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

use BlitzPHP\Utilities\Helpers;

/**
 * @credit <a href="https://laravel.com">Laravel - Illuminate\Support\Traits\Tappable</a>
 */
trait Tappable
{
    /**
     * Appelez la Closure donnée avec cette instance puis renvoyez l'instance.
     *
     * @param (callable($this): mixed)|null $callback
     *
     * @return ($callback is null ? \BlitzPHP\Traits\Mixins\HigherOrderTapProxy : $this)
     */
    public function tap(?callable $callback = null)
    {
        return Helpers::tap($this, $callback);
    }
}
