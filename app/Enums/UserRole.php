<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case PARTICIPANT = 'participant';
    case ADMIN = 'admin';
    case ORGANIZER_MANAGER = 'organizer-manager';
    case SUPER_ADMIN = 'super-admin';

    /**
     * Les rôles qui travaillent dans le back-office.
     *
     * Sert à distinguer « le public » de « l'exploitation », et pas seulement
     * « connecté » de « anonyme » : un participant est connecté lui aussi, et il
     * ne doit pas voir pour autant ce que voit l'administration. Cette liste est
     * lue par le catalogue (événements passés, organisateurs désactivés) et par
     * le choix de la page de réinitialisation du mot de passe — d'où un seul
     * endroit qui la définit.
     *
     * @return array<int, string>
     */
    public static function staff(): array
    {
        return [
            self::SUPER_ADMIN->value,
            self::ADMIN->value,
            self::ORGANIZER_MANAGER->value,
        ];
    }

    /**
     * Returns the label in French for display
     */
    public function label(): string
    {
        return match ($this) {
            self::PARTICIPANT => 'Participant',
            self::ADMIN => 'Administrateur',
            self::ORGANIZER_MANAGER => 'Organisateur',
            self::SUPER_ADMIN => 'Super Administrateur',
        };
    }
}
