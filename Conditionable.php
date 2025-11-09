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

use BlitzPHP\Traits\Mixins\HigherOrderWhenProxy;
use Closure;

trait Conditionable
{
    /**
     * Appliquez le callback si la "valeur" donnée est (ou se résout) véridique.
     *
     * @template TWhenParameter
     * @template TWhenReturnType
     *
     * @param (Closure($this): TWhenParameter)|TWhenParameter|null    $value
     * @param (callable($this, TWhenParameter): TWhenReturnType)|null $callback
     * @param (callable($this, TWhenParameter): TWhenReturnType)|null $default
     * @param mixed|null                                              $value
     *
     * @return $this|TWhenReturnType
     */
    public function when($value = null, ?callable $callback = null, ?callable $default = null)
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if (func_num_args() === 0) {
            return new HigherOrderWhenProxy($this);
        }

        if (func_num_args() === 1) {
            return (new HigherOrderWhenProxy($this))->condition($value);
        }

        if ($value) {
            return $callback($this, $value) ?? $this;
        }
        if ($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }

    /**
     * Appliquez le callback si la "valeur" donnée est (ou se résout) fausse.
     *
     * @template TUnlessParameter
     * @template TUnlessReturnType
     *
     * @param (Closure($this): TUnlessParameter)|TUnlessParameter|null    $value
     * @param (callable($this, TUnlessParameter): TUnlessReturnType)|null $callback
     * @param (callable($this, TUnlessParameter): TUnlessReturnType)|null $default
     * @param mixed|null                                                  $value
     *
     * @return $this|TUnlessReturnType
     */
    public function unless($value = null, ?callable $callback = null, ?callable $default = null)
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if (func_num_args() === 0) {
            return (new HigherOrderWhenProxy($this))->negateConditionOnCapture();
        }

        if (func_num_args() === 1) {
            return (new HigherOrderWhenProxy($this))->condition(! $value);
        }

        if (! $value) {
            return $callback($this, $value) ?? $this;
        }
        if ($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }
}
