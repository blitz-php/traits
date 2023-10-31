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

use BlitzPHP\Translator\Translate;

/**
 * Trait pour les traductions
 * Necessite l'installation du package `blitz-php/translator`
 */
trait Translatable
{
    protected string $_locale      = 'en';
    private ?Translate $translator = null;

    /**
     * Analyse la chaîne de langue d'un fichier, charge le fichier, si nécessaire, et obtient la traduction souhaitée.
     *
     * @return string|string[]
     */
    public function translate(string $line, array $args)
    {
        return $this->translator()->getLine($line, $args);
    }

    /**
     * Instance du traducteur
     */
    protected function translator(): Translate
    {
        if (null !== $this->translator) {
            return $this->translator;
        }

        return $this->translator = new Translate($this->_locale ?? 'en');
    }
}
