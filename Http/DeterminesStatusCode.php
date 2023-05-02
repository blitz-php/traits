<?php

/**
 * This file is part of Blitz PHP framework.
 *
 * (c) 2022 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Traits\Http;

use BlitzPHP\Contracts\Http\StatusCode;

/**
 * @credit <a href="http://laravel.com/">Laravel</a>
 */
trait DeterminesStatusCode
{
    /**
     * Obtenez le code d'état de la réponse.
     */
    abstract protected function status(): int;

	/**
     * Déterminez si le code de réponse était la réponse 200 "OK".
     */
    public function ok(): bool
    {
        return $this->status() === StatusCode::OK;
    }

    /**
     * Déterminez si le code de réponse était la réponse 201 "Created".
     */
    public function created(): bool
    {
        return $this->status() === StatusCode::CREATED;
    }

    /**
     * Déterminez si le code de réponse était la réponse 202 "Accepted".
     */
    public function accepted(): bool
    {
        return $this->status() === StatusCode::ACCEPTED;
    }

    /**
     * Déterminez si le code de réponse était le code d'état donné et si le corps n'a pas de contenu.
     */
    public function noContent(int $status = StatusCode::NO_CONTENT): bool
    {
        return $this->status() === $status && $this->body() === '';
    }

    /**
     * Déterminez si le code de réponse était un 301 "Moved Permanently".
     */
    public function movedPermanently(): bool
    {
        return $this->status() === StatusCode::MOVED_PERMANENTLY;
    }

    /**
     * Déterminez si le code de réponse était une réponse 302 "Found".
     */
    public function found(): bool
    {
        return $this->status() === StatusCode::FOUND;
    }

    /**
     * Déterminez si la réponse était une réponse 400 "Bad Request".
     */
    public function badRequest(): bool
    {
        return $this->status() === StatusCode::BAD_REQUEST;
    }

    /**
     * Déterminez si la réponse était une réponse 401 "Unauthorized".
     */
    public function unauthorized(): bool
    {
        return $this->status() === StatusCode::UNAUTHORIZED;
    }

    /**
     * Déterminez si la réponse était une réponse 402 "Payment Required".
     */
    public function paymentRequired(): bool
    {
        return $this->status() === StatusCode::PAYMENT_REQUIRED;
    }

    /**
     * Déterminez si la réponse était une réponse 403 "Forbidden".
     */
    public function forbidden(): bool
    {
        return $this->status() === StatusCode::FORBIDDEN;
    }

    /**
     * Déterminez si la réponse était une réponse 404 "Not Found".
     */
    public function notFound(): bool
    {
        return $this->status() === StatusCode::NOT_FOUND;
    }

    /**
     * Déterminez si la réponse était une réponse 408 "Request Timeout".
     */
    public function requestTimeout(): bool
    {
        return $this->status() === StatusCode::REQUEST_TIMEOUT;
    }

    /**
     * Déterminez si la réponse était une réponse 409 "Conflict".
     */
    public function conflict(): bool
    {
        return $this->status() === StatusCode::CONFLICT;
    }

    /**
     * Déterminez si la réponse était une réponse 422 "Unprocessable Entity".
     */
    public function unprocessableEntity(): bool
    {
        return $this->status() === StatusCode::UNPROCESSABLE_ENTITY;
    }

    /**
     * Déterminez si la réponse était une réponse 429 "Too Many Requests".
     */
    public function tooManyRequests(): bool
    {
        return $this->status() === StatusCode::TOO_MANY_REQUESTS;
    }
}
