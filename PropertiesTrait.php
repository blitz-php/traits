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

use ReflectionClass;
use ReflectionProperty;

/**
 * Fourni des utilitaires pour lire et ecrire des proprietés de classes.
 * Limite l'accès principalement à des proprietés publiques.
 */
trait PropertiesTrait
{
    /**
     * Tente de modifier les valeurs des proprités publiques de la classe.
     */
    final public function fill(array $params): self
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * Recupere les proprietés publiques de la classe et les retourne dans un tableau.
     */
    final public function getPublicProperties(): array
    {
        $worker = new class () {
            public function getProperties(object $obj): array
            {
                return get_object_vars($obj);
            }
        };

        return $worker->getProperties($this);
    }

    /**
     * Recupere les proprietés protégées et privées de la classe et les retourne dans un tableau.
     */
    final public function getNonPublicProperties(array $exclude = []): array
    {
        $properties = [];

        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED) as $property) {
            if ($property->isStatic() || in_array($property->getName(), $exclude, true)) {
                continue;
            }

            $property->setAccessible(true);
            $properties[] = $property;
        }

        return $properties;
    }
}
