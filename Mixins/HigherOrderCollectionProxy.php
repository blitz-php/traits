<?php

/**
 * This file is part of Blitz PHP framework.
 *
 * (c) 2022 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Traits\Mixins;

use BlitzPHP\Contracts\Support\Enumerable;

/**
 * @template TKey of array-key
 *
 * @template-covariant TValue
 *
 * @mixin \BlitzPHP\Traits\Enumerable<TKey, TValue>
 * @mixin TValue
 *
 * @credit <a href="https://laravel.com">Laravel - Illuminate\Support\HigherOrderCollectionProxy</a>
 */
class HigherOrderCollectionProxy
{
    /**
     * créer une nouvelle instance de proxy.
     *
     * @param Enumerable<TKey, TValue> $collection La collection opérée.
     * @param string                   $method     La méthode faisant l'objet d'un proxy.
     */
    public function __construct(protected Enumerable $collection, protected string $method)
    {
    }

    /**
     * Proxy accédant à un attribut sur les éléments de la collection.
     */
    public function __get(string $key): mixed
    {
        return $this->collection->{$this->method}(static fn ($value) => is_array($value) ? $value[$key] : $value->{$key});
    }

    /**
     * Proxy un appel de méthode sur les éléments de la collection.
     */
    public function __call(string $method, array $parameters = []): mixed
    {
        return $this->collection->{$this->method}(static function ($value) use ($method, $parameters) {
            return is_string($value)
                ? $value::{$method}(...$parameters)
                : $value->{$method}(...$parameters);
        });
    }
}
