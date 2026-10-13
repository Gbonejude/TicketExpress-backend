<?php

declare(strict_types=1);

use App\Support\LogMask;

it('keeps only the first letter and the domain of an e-mail', function (): void {
    expect(LogMask::email('komi.creppy@example.tg'))->toBe('k***@example.tg');
});

it('keeps only the last four digits of a phone number', function (): void {
    expect(LogMask::phone('+228 90 12 34 56'))->toBe('*******3456');
});

it('passes null and empty values through', function (): void {
    expect(LogMask::email(null))->toBeNull()
        ->and(LogMask::phone(''))->toBe('');
});
