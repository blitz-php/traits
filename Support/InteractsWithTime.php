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

use BlitzPHP\Utilities\Date;
use DateInterval;
use DateTimeInterface;

trait InteractsWithTime
{
    /**
     * Obtenez le nombre de secondes jusqu'au DateTime donné.
     */
    protected function secondsUntil(DateInterval|DateTimeInterface|int $delay): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
                            ? max(0, $delay->getTimestamp() - $this->currentTime())
                            : (int) $delay;
    }

    /**
     * Obtenez l'horodatage UNIX "disponible à".
     */
    protected function availableAt(DateInterval|DateTimeInterface|int $delay = 0): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
                            ? $delay->getTimestamp()
                            : Date::now()->addSeconds($delay)->getTimestamp();
    }

    /**
     * Si la valeur donnée est un intervalle, convertissez-la en instance DateTime.
     *
     * @return DateTimeInterface|int
     */
    protected function parseDateInterval(DateInterval|DateTimeInterface|int $delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Date::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Obtenez l’heure actuelle du système sous forme d’horodatage UNIX.
     */
    protected function currentTime(): int
    {
        return Date::now()->getTimestamp();
    }
}
