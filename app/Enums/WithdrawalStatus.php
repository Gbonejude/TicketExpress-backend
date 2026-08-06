<?php

declare(strict_types=1);

namespace App\Enums;

enum WithdrawalStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            self::PAID => 'Payé',
        };
    }

    /**
     * Les statuts qu'un retrait peut prendre ensuite.
     *
     * Le circuit est en deux temps — approuver, puis payer — parce que ce sont
     * deux gestes distincts : le premier engage la plateforme, le second
     * constate un virement réellement parti. Passer de « en attente » à « payé »
     * d'un seul clic effacerait cette distinction, et avec elle la trace de qui
     * a validé la dépense.
     *
     * Un rejet reste possible tant que rien n'est payé.
     *
     * @return array<int, self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::PENDING => [self::APPROVED, self::REJECTED],
            self::APPROVED => [self::PAID, self::REJECTED],
            self::PAID, self::REJECTED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->nextStatuses(), true);
    }

    /** Un état final ne bouge plus : le retrait est payé ou refusé. */
    public function isFinal(): bool
    {
        return $this->nextStatuses() === [];
    }

    /**
     * Statuts qui immobilisent de l'argent : ils sont déduits du solde
     * disponible d'un organisateur (voir `Organizer::availableBalance()`). Une
     * demande encore en attente en fait partie — sans quoi le même solde
     * pourrait être demandé deux fois.
     *
     * @return array<int, string>
     */
    public static function committedValues(): array
    {
        return [self::PENDING->value, self::APPROVED->value, self::PAID->value];
    }
}
