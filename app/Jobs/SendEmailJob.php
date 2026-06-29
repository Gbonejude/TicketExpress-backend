<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    /**
     * @param  string  $to  Email recipient
     * @param  class-string<Mailable>  $mailableClass  Mailable class name
     * @param  array<int, mixed>  $mailableData  Constructor arguments for mailable
     * @param  string|null  $queueName  Queue name (default: 'emails')
     */
    public function __construct(
        private readonly string $to,
        private readonly string $mailableClass,
        private readonly array $mailableData = [],
        private readonly ?string $queueName = 'emails',
    ) {
        $this->onQueue($queueName);
    }

    public function handle(): void
    {
        try {
            /** @var Mailable $mailable */
            $mailable = new $this->mailableClass(...$this->mailableData);
            Mail::to($this->to)->send($mailable);

            Log::info('Email envoyé avec succès via Job', [
                'to' => $this->to,
                'mailable' => $this->mailableClass,
                'queue' => $this->queue,
            ]);
        } catch (\Throwable $e) {
            Log::error('Échec d\'envoi d\'email via Job', [
                'to' => $this->to,
                'mailable' => $this->mailableClass,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job d\'envoi d\'email a échoué après toutes les tentatives', [
            'to' => $this->to,
            'mailable' => $this->mailableClass,
            'error' => $exception->getMessage(),
        ]);
    }
}
