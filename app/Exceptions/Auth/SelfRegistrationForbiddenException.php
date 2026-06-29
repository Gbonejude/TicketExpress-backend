<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use RuntimeException;

/**
 * Thrown when a role that cannot self-register (admin, super-admin, manager)
 * is attempted through the public registration flow.
 *
 * Only the public mobile profiles (client) may create their own
 * account; everything else is provisioned by an administrator. Carries
 * HTTP 403.
 */
final class SelfRegistrationForbiddenException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 403);
    }
}
