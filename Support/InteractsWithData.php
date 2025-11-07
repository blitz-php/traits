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
     * Retrieve all data from the instance.
     */
    abstract public function all(mixed $keys = null): array;

    /**
     * Retrieve data from the instance.
     */
    abstract protected function data(?string $key = null, mixed $default = null): mixed;

    /**
     * Determine if the data contains a given key.
     */
    public function exists(array|string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Determine if the data contains a given key.
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
     * Determine if the instance contains any of the given keys.
     */
    public function hasAny(array|string $keys): bool
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $data = $this->all();

        return Arr::hasAny($data, $keys);
    }

    /**
     * Apply the callback if the instance contains the given key.
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
     * Determine if the instance contains a non-empty value for the given key.
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
     * Determine if the instance contains an empty value for the given key.
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
     * Determine if the instance contains a non-empty value for any of the given keys.
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
     * Apply the callback if the instance contains a non-empty value for the given key.
     *
     * @return $this|mixed
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
     * Determine if the instance is missing a given key.
     */
    public function missing(array|string $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        return ! $this->has($keys);
    }

    /**
     * Apply the callback if the instance is missing the given key.
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
     * Determine if the given key is an empty string for "filled".
     */
    protected function isEmptyString(string $key): bool
    {
        $value = $this->data($key);

        return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
    }

    /**
     * Retrieve data from the instance as a Stringable instance.
     */
    public function str(string $key, mixed $default = null): ?Stringable
    {
		if (null === $value = $this->data($key, $default)) {
			return null;
		}

		return Text::of($value);
    }

    /**
     * Retrieve data from the instance as a string
     */
    public function string(string $key, mixed $default = null): ?string
    {
		return $this->str($key, $default)?->toString() ?? null;
    }

    /**
     * Retrieve data as a boolean value.
     *
     * Returns true when value is "1", "true", "on", and "yes". Otherwise, returns false.
     */
    public function boolean(?string $key = null, bool $default = false): bool
    {
        return filter_var($this->data($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Retrieve data as an integer value.
     */
    public function integer(string $key, int $default = 0): int
    {
        return intval($this->data($key, $default));
    }

    /**
     * Retrieve data as a float value.
     */
    public function float(string $key, float $default = 0.0): float
    {
        return floatval($this->data($key, $default));
    }

    /**
     * Retrieve data from the instance as a Carbon instance.
     *
     * @param UnitEnum|string|null $tz
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
     * Retrieve data from the instance as an enum.
     *
     * @template TEnum of \BackedEnum
     *
     * @param  class-string<TEnum>  $enumClass
     * @param  TEnum|null  $default
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
     * Retrieve data from the instance as an array of enums.
     *
     * @template TEnum of \BackedEnum
     *
     * @param  class-string<TEnum>  $enumClass
     * @return TEnum[]
     */
    public function enums(string $key, $enumClass): array
    {
        if ($this->isNotFilled($key) || ! $this->isBackedEnum($enumClass)) {
            return [];
        }

        return $this->collect($key)
            ->map(fn ($value) => $enumClass::tryFrom($value))
            ->filter()
            ->all();
    }

    /**
     * Determine if the given enum class is backed.
     *
     * @param  class-string  $enumClass
     */
    protected function isBackedEnum(string $enumClass): bool
    {
        return enum_exists($enumClass) && method_exists($enumClass, 'tryFrom');
    }

    /**
     * Retrieve data from the instance as an array.
     *
     * @param  array|string|null  $key
     */
    public function array($key = null): array
    {
        return (array) (is_array($key) ? $this->only($key) : $this->data($key));
    }

    /**
     * Retrieve data from the instance as a collection.
     *
     * @param  array|string|null  $key
     */
    public function collect($key = null): Collection
    {
        return new Collection(is_array($key) ? $this->only($key) : $this->data($key));
    }

    /**
     * Get a subset containing the provided keys with values from the instance data.
     */
    public function only(mixed $keys): array
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
     * Get all of the data except for a specified array of items.
     */
    public function except(mixed $keys): array
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->all();

        Arr::forget($results, $keys);

        return $results;
    }
}
