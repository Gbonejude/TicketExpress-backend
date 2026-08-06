<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Organizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Acknowledges an organizer application to the person who sent it.
 *
 * Without it, signing up as an organizer ended in silence: the account exists
 * but cannot do anything yet, and nothing said why or for how long. The
 * approval e-mail — the one carrying the back-office URL — only comes later,
 * once an administrator has decided.
 *
 * It deliberately does not link anywhere: there is nothing for a pending
 * organizer to open. The public site is for buying tickets, and the
 * back-office refuses them until they are approved.
 */
final class OrganizerApplicationReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Organizer $organizer)
    {
        $this->queue = 'notifications';
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre demande organisateur a bien été reçue — TicketExpress')
            ->greeting('Bonjour '.$notifiable->first_name.',')
            ->line("Nous avons bien reçu votre demande pour **{$this->organizer->company_name}**.")
            ->line('Notre équipe l’examine. Vous recevrez un e-mail dès qu’une décision sera prise, '
                .'et il contiendra le lien vers votre espace organisateur.')
            ->line('Aucune action n’est nécessaire de votre part pour le moment.')
            ->salutation('L’équipe TicketExpress');
    }
}
