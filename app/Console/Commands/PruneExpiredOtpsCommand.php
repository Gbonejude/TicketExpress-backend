<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\OtpCode;
use Illuminate\Console\Command;

class PruneExpiredOtpsCommand extends Command
{
    protected $signature = 'prune:expired-otps
                            {--hours=24 : Supprimer les OTPs expirés depuis au moins ce nombre d\'heures}';

    protected $description = 'Supprimer les codes OTP expirés de la base de données.';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $deleted = OtpCode::where('expires_at', '<', now()->subHours($hours))->delete();

        $this->components->info("Suppression de {$deleted} code(s) OTP expiré(s).");

        return self::SUCCESS;
    }
}
