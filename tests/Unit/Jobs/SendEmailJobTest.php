<?php

declare(strict_types=1);

use App\Jobs\SendEmailJob;
use App\Mail\ContactMessageMail;

it('waits for the surrounding transaction to commit before being queued', function (): void {
    $job = new SendEmailJob('client@example.tg', ContactMessageMail::class);

    expect($job->afterCommit)->toBeTrue();
});

it('retries with growing delays', function (): void {
    $job = new SendEmailJob('client@example.tg', ContactMessageMail::class);

    expect($job->backoff())->toBe([30, 120, 600]);
});
