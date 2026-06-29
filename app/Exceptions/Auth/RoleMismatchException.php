<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use RuntimeException;

/**
 * Thrown when a phone number is already registered with a different role
 * than the one requested at authentication time.
 *
 * Typical scenarios:
 *   * A customer phone is reused at the manager onboarding flow.
 *   * A manager phone is reused at the customer registration flow.
 *
 * Materialises the legacy "invalid_role_for_phone" generic exception from
 * AuthenticationRepository::authenticate() so callers can branch on a
 * typed catch (HTTP 422) instead of inspecting message strings.
 */
final class RoleMismatchException extends RuntimeException
{
    public function __construct(
        public readonly string $phone,
        public readonly string $expectedRole,
        public readonly string $actualRole,
    ) {
        parent::__construct(
            sprintf(
                'Phone %s is registered with role "%s" but role "%s" was requested.',
                $phone,
                $actualRole,
                $expectedRole,
            ),
            422,
        );
    }
}
