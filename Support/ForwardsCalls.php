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

use BadMethodCallException;
use Error;

/**
 * @credit <a href="https://laravel.com">Laravel - Illuminate\Support\Traits\ForwardsCalls</a>
 */
trait ForwardsCalls
{
    /**
     * Transférer un appel de méthode à l'objet donné.
	 *
	 * @return mixed
     *
     * @throws BadMethodCallException
     */
    protected function forwardCallTo(object $object, string $method, array $parameters = [])
    {
        try {
            return $object->{$method}(...$parameters);
        } catch (BadMethodCallException|Error $e) {
            $pattern = '~^Call to undefined method (?P<class>[^:]+)::(?P<method>[^\(]+)\(\)$~';

            if (! preg_match($pattern, $e->getMessage(), $matches)) {
                throw $e;
            }

            if (
                $matches['class'] !== get_class($object)
                || $matches['method'] !== $method
            ) {
                throw $e;
            }

            static::throwBadMethodCallException($method);
        }
    }

    /**
     * Transférer un appel de méthode vers l'objet donné, en renvoyant $this si l'appel transféré s'est renvoyé lui-même.
     *
     * @throws BadMethodCallException
     */
    protected function forwardDecoratedCallTo(object $object, string $method, array $parameters): mixed
    {
        $result = $this->forwardCallTo($object, $method, $parameters);

        return $result === $object ? $this : $result;
    }

    /**
     * Lance une exception d'appel de méthode incorrecte pour la méthode donnée.
     *
     * @throws BadMethodCallException
     */
    protected static function throwBadMethodCallException(string $method): never
    {
        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()',
            static::class,
            $method
        ));
    }
}
