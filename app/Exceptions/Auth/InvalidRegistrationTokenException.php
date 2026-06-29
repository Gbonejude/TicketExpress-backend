<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use RuntimeException;

/**
 * Thrown when the registration token presented at /register is missing,
 * corrupted, expired, already consumed, or does not match a verified phone.
 *
 * Carries HTTP 422 so the client knows the verification step must be redone.
 */
final class InvalidRegistrationTokenException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 422);
    }
}
