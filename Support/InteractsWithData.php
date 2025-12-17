<?php

/**
 * This file is part of Blitz PHP framework.
 *
 * (c) 2022 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Traits\Support;

use BlitzPHP\Utilities\DateTime\Date;
use BlitzPHP\Utilities\Helpers;
use BlitzPHP\Utilities\Iterable\Arr;
use BlitzPHP\Utilities\Iterable\Collection;
use BlitzPHP\Utilities\String\Stringable;
use BlitzPHP\Utilities\String\Text;
use stdClass;
use UnitEnum;

/**
 * @credit <a href="https://laravel.com">Laravel - Illuminate\Support\Traits\InteractsWithData</a>
 */
trait InteractsWithData
{
    /**
     * Récupère toutes les données de l'instance.
     */
    abstract public function all(mixed $keys = null): array;

    /**
     * Récupère les données de l'instance.
     */
    abstract protected function data(?string $key = null, mixed $default = null): mixed;

    /**
     * Détermine si les données contiennent une clé donnée.
     */
    public function exists(array|string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Détermine si les données contiennent une clé donnée.
     */
    public function has(array|string $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        $data = $this->all();

        foreach ($keys as $value) {
            if (! Arr::has($data, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Détermine si l'instance contient l'une des clés données.
     */
    public function hasAny(array|string $keys): bool
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $data = $this->all();

        return Arr::hasAny($data, $keys);
    }

    /**
     * Applique le callback si l'instance contient la clé donnée.
     *
     * @return $this|mixed
     */
    public function whenHas(string $key, callable $callback, ?callable $default = null)
    {
        if ($this->has($key)) {
            return $callback(Helpers::dataGet($this->all(), $key)) ?: $this;
        }

        if ($default) {
            return $default();
        }

        return $this;
    }

    /**
     * Détermine si l'instance contient une valeur non vide pour un élément d'entrée.
     */
    public function filled(array|string $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Détermine si l'instance contient une valeur vide pour un élément d'entrée.
     */
    public function isNotFilled(array|string $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if (! $this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Détermine si l'instance contient une valeur non vide pour l'une des entrées données.
     */
    public function anyFilled(array|string $keys): bool
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        foreach ($keys as $key) {
            if ($this->filled($key)) {
                return true;
            }
        }

        return false;
    }

	/**
     * Applique le callback si l'instance contient une valeur non vide pour la clé d'élément d'entrée donnée.
     *
     * @return mixed|self
     */
    public function whenFilled(string $key, callable $callback, ?callable $default = null)
    {
        if ($this->filled($key)) {
            return $callback(Helpers::dataGet($this->all(), $key)) ?: $this;
        }

        if ($default) {
            return $default();
        }

        return $this;
    }

    /**
     * Détermine si l'instance manque une clé donnée.
     */
    public function missing(array|string $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        return ! $this->has($keys);
    }

    /**
     * Applique le callback si la clé donnée est manquante dans l'instance.
     *
     * @return $this|mixed
     */
    public function whenMissing(string $key, callable $callback, ?callable $default = null)
    {
        if ($this->missing($key)) {
            return $callback(Helpers::dataGet($this->all(), $key)) ?: $this;
        }

        if ($default) {
            return $default();
        }

        return $this;
    }

    /**
     * Détermine si la clé donnée est une chaîne vide pour "filled".
     */
    protected function isEmptyString(string $key): bool
    {
        $value = $this->data($key);

        return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
    }

    /**
     * Récupère les données de l'instance sous forme d'instance Stringable.
     */
    public function str(string $key, mixed $default = null): ?Stringable
    {
        if (null === $value = $this->string($key, $default)) {
            return null;
        }

        return Text::of($value);
    }

    /**
     * Récupère les données de l'instance en tant que chaine de caractere.
     */
    public function string(string $key, mixed $default = null): ?string
    {
        if (null === $value = $this->data($key, $default)) {
            return null;
        }

        return (string) $value;
    }

    /**
     * Récupère les données sous forme de valeur booléenne.
     *
     * Renvoie true lorsque la valeur est "1", "true", "on" et "yes". Sinon, renvoie faux.
     */
    public function boolean(?string $key = null, bool $default = false): bool
    {
        return filter_var($this->data($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Récupère les données sous forme de valeur entière.
     */
    public function integer(string $key, int $default = 0): int
    {
        return (int) ($this->data($key, $default));
    }

    /**
     * Récupère les données sous forme de valeur flottante.
     */
    public function float(string $key, float $default = 0.0): float
    {
        return (float) ($this->data($key, $default));
    }

    /**
     * Récupère les données de l'instance en tant qu'instance Date.
     *
     * @param string|UnitEnum|null $tz
     */
    public function date(string $key, ?string $format = null, $tz = null): ?Date
    {
        $tz = Helpers::enumValue($tz);

        if ($this->isNotFilled($key)) {
            return null;
        }

        if (null === $format) {
            return Date::parse($this->data($key), $tz);
        }

        return Date::createFromFormat($format, $this->data($key), $tz);
    }

    /**
     * Récupère les données de l'instance sous forme d'énumération.
     *
     * @template TEnum of \BackedEnum
     *
     * @param class-string<TEnum> $enumClass
     * @param TEnum|null          $default
     *
     * @return TEnum|null
     */
    public function enum(string $key, string $enumClass, $default = null)
    {
        if ($this->isNotFilled($key) || ! $this->isBackedEnum($enumClass)) {
            return Helpers::value($default);
        }

        return $enumClass::tryFrom($this->data($key)) ?: Helpers::value($default);
    }

    /**
     * Récupère les données de l'instance sous forme de tableau d'énumération.
     *
     * @template TEnum of \BackedEnum
     *
     * @param class-string<TEnum> $enumClass
     *
     * @return list<TEnum>
     */
    public function enums(string $key, $enumClass): array
    {
        if ($this->isNotFilled($key) || ! $this->isBackedEnum($enumClass)) {
            return [];
        }

        return $this->collect($key)
            ->map(static fn ($value) => $enumClass::tryFrom($value))
            ->filter()
            ->all();
    }

    /**
     * Détermine si la classe énumérée donnée est prise en charge.
     *
     * @param class-string $enumClass
     */
    protected function isBackedEnum(string $enumClass): bool
    {
        return enum_exists($enumClass) && method_exists($enumClass, 'tryFrom');
    }

    /**
     * Récupère les données de l'instance sous forme de tableau.
     *
     * @param array|string|null $key
     */
    public function array($key = null): array
    {
        return (array) (is_array($key) ? $this->only($key) : $this->data($key));
    }

    /**
     * Récupère les données de l'instance sous forme de collection.
     */
    public function collect(array|string|null $key = null): Collection
    {
		return new Collection(is_array($key) ? $this->only($key) : $this->data($key));
    }

    /**
     * Obtient un sous-ensemble contenant les clés fournies avec les valeurs provenant des données d'instance.
     *
     * @param array|mixed $keys
     */
    public function only($keys): array
    {
        $results = [];

        $data = $this->all();

        $placeholder = new stdClass();

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            $value = Helpers::dataGet($data, $key, $placeholder);

            if ($value !== $placeholder) {
                Arr::set($results, $key, $value);
            }
        }

        return $results;
    }

    /**
     * Récupérer toutes les données à l'exception d'un tableau d'éléments spécifié.
     *
     * @param array|mixed $keys
     */
    public function except($keys): array
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->all();

        Arr::forget($results, $keys);

        return $results;
    }
}
