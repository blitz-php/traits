<?php

/**
 * This file is part of Blitz PHP framework.
 *
 * (c) 2022 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Traits;

use BackedEnum;
use BlitzPHP\Contracts\Support\Arrayable;
use BlitzPHP\Contracts\Support\Enumerable;
use BlitzPHP\Contracts\Support\Jsonable;
use BlitzPHP\Traits\Mixins\HigherOrderCollectionProxy;
use BlitzPHP\Utilities\Helpers;
use BlitzPHP\Utilities\Iterable\Arr;
use BlitzPHP\Utilities\Iterable\Collection;
use CachingIterator;
use Closure;
use Exception;
use JsonSerializable;
use Kint\Kint;
use UnexpectedValueException;
use UnitEnum;

/**
 * @template TKey of array-key
 *
 * @template-covariant TValue
 *
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $average
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $avg
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $contains
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $doesntContain
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $each
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $every
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $filter
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $first
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $flatMap
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $groupBy
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $keyBy
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $last
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $map
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $max
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $min
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $partition
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $percentage
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $reject
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $skipUntil
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $skipWhile
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $some
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $sortBy
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $sortByDesc
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $sum
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $takeUntil
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $takeWhile
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $unique
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $unless
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $until
 * @property-read HigherOrderCollectionProxy<TKey, TValue> $when
 */
trait EnumeratesValues
{
    use Conditionable;

    /**
     * Indique que la représentation sous forme de chaîne de l'objet doit être échappée lorsque __toString est invoqué.
     */
    protected bool $escapeWhenCastingToString = false;

    /**
     * Les méthodes qui peuvent être proxy.
     *
     * @var string[]
     */
    protected static array $proxies = [
        'average',
        'avg',
        'contains',
        'doesntContain',
        'each',
        'every',
        'filter',
        'first',
        'flatMap',
        'groupBy',
        'keyBy',
        'last',
        'map',
        'max',
        'min',
        'partition',
        'percentage',
        'reject',
        'skipUntil',
        'skipWhile',
        'some',
        'sortBy',
        'sortByDesc',
        'sum',
        'takeUntil',
        'takeWhile',
        'unique',
        'unless',
        'until',
        'when',
    ];

    /**
     * Créez une nouvelle instance de collection si la valeur n'en est pas déjà une.
     *
     * @template TMakeKey of array-key
     * @template TMakeValue
     *
     * @param Arrayable<TMakeKey, TMakeValue>|iterable<TMakeKey, TMakeValue>|null  $items
	 *
     * @return static<TMakeKey, TMakeValue>
     */
    public static function make($items = []): static
    {
        return new static($items);
    }

    /**
     * Enveloppez la valeur donnée dans une collection, le cas échéant.
     *
     * @template TWrapValue
     *
     * @param  iterable<array-key, TWrapValue>|TWrapValue  $value
	 *
     * @return static<array-key, TWrapValue>
     */
    public static function wrap($value): static
    {
        return $value instanceof Enumerable
            ? new static($value)
            : new static(Arr::wrap($value));
    }

    /**
     * Obtenez les éléments sous-jacents de la collection donnée, le cas échéant.
     *
     * @template TUnwrapKey of array-key
     * @template TUnwrapValue
     *
     * @param  array<TUnwrapKey, TUnwrapValue>|static<TUnwrapKey, TUnwrapValue>  $value
	 *
     * @return array<TUnwrapKey, TUnwrapValue>
     */
    public static function unwrap($value): array
    {
        return $value instanceof Enumerable ? $value->all() : $value;
    }

    /**
     * Créez une nouvelle instance sans éléments.
     */
    public static function empty(): static
    {
        return new static([]);
    }

    /**
     * Créez une nouvelle instance en appelant le callback un certain nombre de fois.
     *
     * @template TTimesValue
     *
     * @param (callable(int): TTimesValue)|null  $callback
	 *
     * @return static<int, TTimesValue>
     */
    public static function times(int $number, ?callable $callback = null): static
    {
        if ($number < 1) {
            return new static();
        }

        return static::range(1, $number)
            ->unless($callback === null)
            ->map($callback);
    }

    /**
     * Create a new collection by decoding a JSON string.
     *
     * @return static<TKey, TValue>
     */
    public static function fromJson(string $json, int $depth = 512, int $flags = 0): static
    {
        return new static(json_decode($json, true, $depth, $flags));
    }

    /**
     * Get the average value of a given key.
     *
     * @param (callable(TValue): float|int)|string|null  $callback
	 *
     * @return float|int|null
     */
    public function avg($callback = null)
    {
        $callback = $this->valueRetriever($callback);

        $reduced = $this->reduce(static function (&$reduce, $value) use ($callback) {
            if (! is_null($resolved = $callback($value))) {
                $reduce[0] += $resolved;
                $reduce[1]++;
            }

            return $reduce;
        }, [0, 0]);

        return $reduced[1] ? $reduced[0] / $reduced[1] : null;
    }

    /**
     * Alias pour la méthode "avg".
     *
     * @param (callable(TValue): float|int)|string|null  $callback
     *
     * @return float|int|null
     */
    public function average($callback = null)
    {
        return $this->avg($callback);
    }

    /**
     * Alias pour la méthode "contains".
     *
     * @param  (callable(TValue, TKey): bool)|TValue|string $key
     */
    public function some($key, mixed $operator = null, mixed $value = null): bool
    {
        return $this->contains(...func_get_args());
    }

    /**
     * Videz la collection et terminez le script.
     */
    public function dd(...$args): never
    {
        $this->dump(...$args);

        exit(1);
    }

    /**
     * Videz la collection.
     */
    public function dump(...$args): self
    {
		Kint::dump($this->all(), ...$args);

        return $this;
    }

    /**
     * Exécutez un callback sur chaque élément.
     *
     * @param callable(TValue, TKey): mixed $callback
     */
    public function each(callable $callback): self
    {
        foreach ($this as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Exécutez un rappel sur chaque bloc d'éléments imbriqué.
     */
    public function eachSpread(callable $callback): self
    {
        return $this->each(function ($chunk, $key) use ($callback) {
            $chunk[] = $key;

            return $callback(...$chunk);
        });
    }

    /**
     * Déterminez si tous les éléments réussissent le test de vérité donné.
     *
     * @param (callable(TValue, TKey): bool)|TValue|string $key
     */
    public function every($key, mixed $operator = null, mixed $value = null): bool
    {
        if (func_num_args() === 1) {
            $callback = $this->valueRetriever($key);

            foreach ($this as $k => $v) {
                if (! $callback($v, $k)) {
                    return false;
                }
            }

            return true;
        }

        return $this->every($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Obtenez le premier élément par la paire clé-valeur donnée.
     *
     * @return TValue|null
     */
    public function firstWhere(callable|string $key, mixed $operator = null, mixed $value = null): mixed
    {
        return $this->first($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Obtenez la valeur d'une clé unique à partir du premier élément correspondant de la collection.
     *
     * @template TValueDefault
     *
     * @param TValueDefault|(\Closure(): TValueDefault) $default
	 *
     * @return TValue|TValueDefault
     */
    public function value(string $key, mixed $default = null): mixed
    {
		$value = $this->first(fn($target) => Helpers::dataHas($target, $key));

		return Helpers::dataGet($value, $key, $default);
    }

    /**
     * Ensure that every item in the collection is of the expected type.
     *
     * @template TEnsureOfType
     *
     * @param class-string<TEnsureOfType>|array<array-key, class-string<TEnsureOfType>>|'string'|'int'|'float'|'bool'|'array'|'null' $type
	 *
     * @return static<TKey, TEnsureOfType>
     *
     * @throws UnexpectedValueException
     */
    public function ensure($type)
    {
        $allowedTypes = is_array($type) ? $type : [$type];

        return $this->each(function ($item, $index) use ($allowedTypes) {
            $itemType = get_debug_type($item);

            foreach ($allowedTypes as $allowedType) {
                if ($itemType === $allowedType || $item instanceof $allowedType) {
                    return true;
                }
            }

            throw new UnexpectedValueException(
                sprintf("Collection should only include [%s] items, but '%s' found at position %d.", implode(', ', $allowedTypes), $itemType, $index)
            );
        });
    }

    /**
     * Déterminez si la collection n'est pas vide.
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Exécutez une carte sur chaque bloc d'éléments imbriqué.
     *
     * @template TMapSpreadValue
     *
     * @param callable(mixed...): TMapSpreadValue  $callback
	 *
     * @return static<TKey, TMapSpreadValue>
     */
    public function mapSpread(callable $callback)
    {
        return $this->map(function ($chunk, $key) use ($callback) {
            $chunk[] = $key;

            return $callback(...$chunk);
        });
    }

    /**
     * Exécutez une carte de regroupement sur les éléments.
     *
     * Le callback doit renvoyer un tableau associatif avec une seule paire clé/valeur.
     *
     * @template TMapToGroupsKey of array-key
     * @template TMapToGroupsValue
     *
     * @param callable(TValue, TKey): array<TMapToGroupsKey, TMapToGroupsValue>  $callback
	 *
     * @return static<TMapToGroupsKey, static<int, TMapToGroupsValue>>
     */
    public function mapToGroups(callable $callback)
    {
        $groups = $this->mapToDictionary($callback);

        return $groups->map($this->make(...));
    }

    /**
     * Mappez une collection et aplatissez le résultat d'un seul niveau.
     *
     * @template TFlatMapKey of array-key
     * @template TFlatMapValue
     *
     * @param  callable(TValue, TKey): (\BlitzPHP\Utilities\Iterable\Collection<TFlatMapKey, TFlatMapValue>|array<TFlatMapKey, TFlatMapValue>) $callback
	 *
     * @return static<TFlatMapKey, TFlatMapValue>
     */
    public function flatMap(callable $callback)
    {
        return $this->map($callback)->collapse();
    }

    /**
     * Mappez les valeurs dans une nouvelle classe.
     *
     * @template TMapIntoValue
     *
     * @param class-string<TMapIntoValue> $class
	 *
     * @return static<TKey, TMapIntoValue>
     */
    public function mapInto(string $class)
    {
        if (is_subclass_of($class, BackedEnum::class)) {
            return $this->map(fn ($value, $key) => $class::from($value));
        }

        return $this->map(fn ($value, $key) => new $class($value, $key));
    }

    /**
     * Obtenir la valeur minimale d'une clé donnée.
     *
     * @param (callable(TValue):mixed)|string|null $callback
     */
    public function min($callback = null): mixed
    {
        $callback = $this->valueRetriever($callback);

        return $this->map(fn ($value) => $callback($value))
            ->reject(fn ($value) => null === $value)
            ->reduce(fn ($result, $value) => null === $result || $value < $result ? $value : $result);
    }

    /**
     * Obtenir la valeur maximale d'une clé donnée.
     *
     * @param (callable(TValue):mixed)|string|null $callback
     */
    public function max($callback = null): mixed
    {
        $callback = $this->valueRetriever($callback);

        return $this->reject(fn ($value) => null === $value)->reduce(function ($result, $item) use ($callback) {
            $value = $callback($item);

            return null === $result || $value > $result ? $value : $result;
        });
    }

    /**
     * "Pagine" la collection en la découpant en une plus petite collection.
     */
    public function forPage(int $page, int $perPage): static
    {
        $offset = max(0, ($page - 1) * $perPage);

        return $this->slice($offset, $perPage);
    }

    /**
     * Partitionnez la collection en deux tableaux à l'aide du callback ou de la clé donnés.
     *
     * @param (callable(TValue, TKey): bool)|TValue|string $key
     *
     * @return static<int<0, 1>, static<TKey, TValue>>
     */
    public function partition($key, mixed $operator = null, mixed $value = null): static
    {
        $callback = func_num_args() === 1
			? $this->valueRetriever($key)
			: $this->operatorForWhere(...func_get_args());

		[$passed, $failed] = Arr::partition($this->getIterator(), $callback);

        return new static([new static($passed), new static($failed)]);
    }

    /**
     * Calculate the percentage of items that pass a given truth test.
     *
     * @param (callable(TValue, TKey): bool) $callback
     */
    public function percentage(callable $callback, int $precision = 2): ?float
    {
        if ($this->isEmpty()) {
            return null;
        }

        return round(
            $this->filter($callback)->count() / $this->count() * 100,
            $precision
        );
    }

    /**
     * Obtenir la somme des valeurs données.
     *
     * @template TReturnType
     *
     * @param (callable(TValue): TReturnType)|string|null $callback
	 *
     * @return ($callback is callable ? TReturnType : mixed)
     */
    public function sum($callback = null): mixed
    {
        $callback = null === $callback
            ? $this->identity()
            : $this->valueRetriever($callback);

        return $this->reduce(fn ($result, $item) => $result + $callback($item), 0);
    }

    /**
     * Appliquez le callback si la collection est vide.
     *
     * @template TWhenEmptyReturnType
     *
     * @param (callable($this): TWhenEmptyReturnType) $callback
     * @param (callable($this): TWhenEmptyReturnType)|null $default
	 *
     * @return $this|TWhenEmptyReturnType
     */
    public function whenEmpty(callable $callback, ?callable $default = null)
    {
        return $this->when($this->isEmpty(), $callback, $default);
    }

    /**
     * Appliquez le callback si la collection n'est pas vide.
     *
     * @template TWhenNotEmptyReturnType
     *
     * @param callable($this): TWhenNotEmptyReturnType $callback
     * @param (callable($this): TWhenNotEmptyReturnType)|null $default
	 *
     * @return $this|TWhenNotEmptyReturnType
     */
    public function whenNotEmpty(callable $callback, ?callable $default = null)
    {
        return $this->when($this->isNotEmpty(), $callback, $default);
    }

    /**
     * Appliquez le callback seulement si la collection est vide.
     *
     * @template TUnlessEmptyReturnType
     *
     * @param callable($this): TUnlessEmptyReturnType $callback
     * @param (callable($this): TUnlessEmptyReturnType)|null $default
	 *
     * @return $this|TUnlessEmptyReturnType
     */
    public function unlessEmpty(callable $callback, ?callable $default = null)
    {
        return $this->whenNotEmpty($callback, $default);
    }

    /**
     * Appliquez le callback seulement si la collection n'est pas vide.
     *
     * @template TUnlessNotEmptyReturnType
     *
     * @param callable($this): TUnlessNotEmptyReturnType $callback
     * @param (callable($this): TUnlessNotEmptyReturnType)|null $default
	 *
     * @return $this|TUnlessNotEmptyReturnType
     */
    public function unlessNotEmpty(callable $callback, ?callable $default = null)
    {
        return $this->whenEmpty($callback, $default);
    }

    /**
     * Filtrez les éléments par la paire clé-valeur donnée.
	 */
    public function where(callable|string $key, mixed $operator = null, mixed $value = null): static
    {
        return $this->filter($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Filtrer les éléments où la valeur de la clé donnée est nulle.
     */
    public function whereNull(?string $key = null): static
    {
        return $this->whereStrict($key, null);
    }

    /**
     * Filtre les éléments où la valeur de la clé donnée n'est pas nulle.
     */
    public function whereNotNull(?string $key = null): static
    {
        return $this->where($key, '!==', null);
    }

    /**
     * Filtrez les éléments en fonction de la paire clé-valeur donnée à l'aide d'une comparaison stricte.
     */
    public function whereStrict(string $key, mixed $value): static
    {
        return $this->where($key, '===', $value);
    }

    /**
     * Filtrez les éléments par la paire clé-valeur donnée.
     *
     * @param Arrayable|iterable $values
     */
    public function whereIn(string $key, $values, bool $strict = false): static
    {
        $values = $this->getArrayableItems($values);

        return $this->filter(fn ($item) => in_array(Helpers::dataGet($item, $key), $values, $strict));
    }

    /**
     * Filtrez les éléments en fonction de la paire clé-valeur donnée à l'aide d'une comparaison stricte.
     *
     * @param Arrayable|iterable $values
     */
    public function whereInStrict(string $key, $values): static
    {
        return $this->whereIn($key, $values, true);
    }

    /**
     * Filtrez les éléments de sorte que la valeur de la clé donnée se situe entre les valeurs données.
     *
     * @param Arrayable|iterable $values
     */
    public function whereBetween(string $key, $values): static
    {
        return $this->where($key, '>=', reset($values))->where($key, '<=', end($values));
    }

    /**
     * Filtrez les éléments de sorte que la valeur de la clé donnée ne soit pas comprise entre les valeurs données.
     *
     * @param Arrayable|iterable $values
     */
    public function whereNotBetween(string $key, $values): static
    {
        return $this->filter(
            fn ($item) => Helpers::dataGet($item, $key) < reset($values) || Helpers::dataGet($item, $key) > end($values)
        );
    }

    /**
     * Filtrez les éléments par la paire clé-valeur donnée.
     *
     * @param Arrayable|iterable $values
     */
    public function whereNotIn(string $key, $values, bool $strict = false): static
    {
        $values = $this->getArrayableItems($values);

        return $this->reject(fn ($item) => in_array(Helpers::dataGet($item, $key), $values, $strict));
    }

    /**
     * Filtrez les éléments en fonction de la paire clé-valeur donnée à l'aide d'une comparaison stricte.
     *
     * @param Arrayable|iterable $values
     */
    public function whereNotInStrict(string $key, $values): static
    {
        return $this->whereNotIn($key, $values, true);
    }

    /**
     * Filtrez les éléments, en supprimant tous les éléments qui ne correspondent pas au(x) type(s) donné(s).
     *
     * @template TWhereInstanceOf
     *
     * @param class-string<TWhereInstanceOf>|array<array-key, class-string<TWhereInstanceOf>> $type
	 *
     * @return static<TKey, TWhereInstanceOf>
     */
    public function whereInstanceOf($type): static
    {
        return $this->filter(function ($value) use ($type) {
            if (is_array($type)) {
                foreach ($type as $classType) {
                    if ($value instanceof $classType) {
                        return true;
                    }
                }

                return false;
            }

            return $value instanceof $type;
        });
    }

    /**
     * Passez la collection au callback donné et renvoyez le résultat.
     *
     * template TPipeReturnType
     *
     * @param callable($this): TPipeReturnType $callback
	 *
     * @return TPipeReturnType
     */
    public function pipe(callable $callback): mixed
    {
        return $callback($this);
    }

    /**
     * Passez la collection dans une nouvelle classe.
     *
     * @template TPipeIntoValue
     *
     * @param class-string<TPipeIntoValue> $class
	 *
     * @return TPipeIntoValue
     */
    public function pipeInto(string $class): mixed
    {
        return new $class($this);
    }

    /**
     * Passez la collection à travers une série de canaux appelables et renvoyez le résultat.
     *
     * @param callable[] $callbacks
     */
    public function pipeThrough(array $callbacks): mixed
    {
        return Collection::make($callbacks)->reduce(
            static fn ($carry, $callback) => $callback($carry),
            $this,
        );
    }

    /**
     * Réduisez la collection à une seule valeur.
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     * @param callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType $callback
     * @param TReduceInitial $initial
	 *
     * @return TReduceReturnType
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $result = $initial;

        foreach ($this as $key => $value) {
            $result = $callback($result, $value, $key);
        }

        return $result;
    }

    /**
     * Réduisez la collection à plusieurs valeurs agrégées.
     *
     * @throws UnexpectedValueException
     */
    public function reduceSpread(callable $callback, ...$initial): array
    {
		$result = $initial;

        foreach ($this as $key => $value) {
            $result = call_user_func_array($callback, array_merge($result, [$value, $key]));

            if (! is_array($result)) {
                throw new UnexpectedValueException(sprintf(
                    "%s::reduceSpread s'attend à ce que le réducteur renvoie un tableau, mais a obtenu un '%s' à la place.",
					Helpers::classBasename(static::class), gettype($result)
                ));
            }
        }

        return $result;
    }

    /**
     * Reduce an associative collection to a single value.
     *
     * @template TReduceWithKeysInitial
     * @template TReduceWithKeysReturnType
     *
     * @param callable(TReduceWithKeysInitial|TReduceWithKeysReturnType, TValue, TKey): TReduceWithKeysReturnType $callback
     * @param TReduceWithKeysInitial $initial
	 *
     * @return TReduceWithKeysReturnType
     */
    public function reduceWithKeys(callable $callback, $initial = null)
    {
        return $this->reduce($callback, $initial);
    }

    /**
     * Créez une collection de tous les éléments qui ne réussissent pas un test de vérité donné.
     *
     * @param (callable(TValue, TKey): bool)|bool|TValue $callback
     */
    public function reject($callback = true): static
    {
        $useAsCallable = $this->useAsCallable($callback);

        return $this->filter(function ($value, $key) use ($callback, $useAsCallable) {
            return $useAsCallable
                ? ! $callback($value, $key)
                : $value !== $callback;
        });
    }

    /**
     * Passez la collection au rappel donné, puis renvoyez-la.
     *
     * @param callable($this): mixed $callback
     */
    public function tap(callable $callback): self
    {
        $callback($this);

        return $this;
    }

    /**
     * Renvoie uniquement les éléments uniques du tableau de collection.
     *
     * @param (callable(TValue, TKey): mixed)|string|null $key
     */
    public function unique($key = null, bool $strict = false): static
    {
        $callback = $this->valueRetriever($key);

        $exists = [];

        return $this->reject(function ($item, $key) use ($callback, $strict, &$exists) {
            if (in_array($id = $callback($item, $key), $exists, $strict)) {
                return true;
            }

            $exists[] = $id;
        });
    }

    /**
     * Renvoie uniquement les éléments uniques du tableau de collection en utilisant une comparaison stricte.
     *
     * @param (callable(TValue, TKey): mixed)|string|null $key
     */
    public function uniqueStrict($key = null): static
    {
        return $this->unique($key, true);
    }

    /**
     * Rassemblez les valeurs dans une collection.
     */
    public function collect(): Collection
    {
        return Collection::make($this->all());
    }

    /**
     * Obtenez la collection d'éléments sous la forme d'un tableau simple.
     *
     * @return array<TKey, mixed>
     */
    public function toArray(): array
    {
        return $this->map(fn ($value) => $value instanceof Arrayable ? $value->toArray() : $value)->all();
    }

    /**
     * Convertissez l'objet en quelque chose de JSON sérialisable.
     *
     * @return array<TKey, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            return match (true) {
                $value instanceof JsonSerializable => $value->jsonSerialize(),
                $value instanceof Jsonable => json_decode($value->toJson(), true),
                $value instanceof Arrayable => $value->toArray(),
                default => $value,
            };
        }, $this->all());
    }

    /**
     * Obtenez la collection d'éléments au format JSON.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get the collection of items as pretty print formatted JSON.
     */
    public function toPrettyJson(int $options = 0): string
    {
        return $this->toJson(JSON_PRETTY_PRINT | $options);
    }

    /**
     * Obtenez une instance de CachingIterator.
     */
    public function getCachingIterator(int $flags = CachingIterator::CALL_TOSTRING): CachingIterator
    {
        return new CachingIterator($this->getIterator(), $flags);
    }

    /**
     * Convertissez la collection en sa représentation sous forme de chaîne.
     */
    public function __toString(): string
    {
        if (! $this->escapeWhenCastingToString) {
            return $this->toJson();
        }

		return match(true) {
			function_exists('e')   => e($this->toJson()),
			function_exists('esc') => esc($this->toJson()),
			default                => $this->toJson(),
		};
    }

    /**
     * Indique que la représentation sous forme de chaîne du modèle doit être échappée lorsque __toString est invoqué.
     */
    public function escapeWhenCastingToString(bool $escape = true): self
    {
        $this->escapeWhenCastingToString = $escape;

        return $this;
    }

    /**
     * Ajoutez une méthode à la liste des méthodes proxy.
     */
    public static function proxy(string $method): void
    {
        static::$proxies[] = $method;
    }

    /**
     * Accédez dynamiquement aux proxys de collecte.
     *
     * @throws Exception
     */
    public function __get(string $key): mixed
    {
        if (! in_array($key, static::$proxies, true)) {
            throw new Exception("La propriété [{$key}] n'existe pas sur cette instance de collection.");
        }

        return new HigherOrderCollectionProxy($this, $key);
    }

    /**
     * Tableau de résultats des éléments de Collection ou Arrayable.
	 *
     * @return array<TKey, TValue>
     */
    protected function getArrayableItems(mixed $items): array
    {
        return null === $items || is_scalar($items) || $items instanceof UnitEnum
            ? Arr::wrap($items)
            : Arr::from($items);
    }

    /**
     * Obtenez un callback du vérificateur de l'opérateur.
     *
     * @param callable|string $key
     * @param string|null $operateur
     *
     * @return Closure
     */
    protected function operatorForWhere($key, mixed $operator = null, mixed $value = null)
    {
        if ($this->useAsCallable($key)) {
            return $key;
        }

        if (func_num_args() === 1) {
            $value = true;

            $operator = '=';
        }

        if (func_num_args() === 2) {
            $value = $operator;

            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = Helpers::enumValue(Helpers::dataGet($item, $key));
			$value = Helpers::enumValue($value);

            $strings = array_filter([$retrieved, $value], function ($value) {
                return match (true) {
                    is_string($value) => true,
                    $value instanceof \Stringable => true,
                    default => false,
                };
            });

            if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) == 1) {
                return in_array($operator, ['!=', '<>', '!==']);
            }

            switch ($operator) {
                default:
                case '=':
                case '==':  return $retrieved == $value;

                case '!=':
                case '<>':  return $retrieved != $value;
                case '<':   return $retrieved < $value;
                case '>':   return $retrieved > $value;
                case '<=':  return $retrieved <= $value;
                case '>=':  return $retrieved >= $value;
                case '===': return $retrieved === $value;
                case '!==': return $retrieved !== $value;
                case '<=>': return $retrieved <=> $value;
            }
        };
    }

    /**
     * Détermine si la valeur donnée est appelable, mais pas une chaîne.
     */
    protected function useAsCallable(mixed $value): bool
    {
        return ! is_string($value) && is_callable($value);
    }

    /**
     * Obtenez un rappel de récupération de valeur.
     *
     * @param callable|string|null $value
     */
    protected function valueRetriever($value): callable
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return fn ($item) => Helpers::dataGet($item, $value);
    }

    /**
     * Créer une fonction pour vérifier l'égalité d'un élément.
     *
     * @return Closure(mixed): bool
     */
    protected function equality(mixed $value): Closure
    {
        return fn ($item) => $item === $value;
    }

    /**
     * Faire une fonction en utilisant une autre fonction, en annulant son résultat.
     */
    protected function negate(Closure $callback): Closure
    {
        return fn (...$params) => ! $callback(...$params);
    }

    /**
     * Créez une fonction qui renvoie ce qui lui est transmis.
     *
     * @return Closure(TValue): TValue
     */
    protected function identity(): Closure
    {
        return fn ($value) => $value;
    }
}
