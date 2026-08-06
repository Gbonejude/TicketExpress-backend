<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class OrganizerRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Organizer $organizer,
        public readonly User $user,
    ) {
        $this->queue = 'notifications';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Mail as well as the bell: an application waits on a human, and an
        // administrator who does not open the back-office that day would
        // otherwise leave a would-be organizer waiting indefinitely.
        return ['database', 'mail'];
    }

    /** The e-mail an administrator receives when someone applies. */
    public function toMail(object $notifiable): MailMessage
    {
        $userName = trim("{$this->user->first_name} {$this->user->last_name}");

        return (new MailMessage)
            ->subject('Nouvelle demande organisateur — '.$this->organizer->company_name)
            ->greeting('Nouvelle demande organisateur')
            ->line("**{$this->organizer->company_name}** souhaite publier des événements sur TicketExpress.")
            ->line("Demandeur : {$userName}")
            ->line("E-mail : {$this->user->email}")
            ->line("Téléphone : {$this->user->phone}")
            ->when(
                filled($this->organizer->description),
                fn (MailMessage $mail) => $mail->line("Activité : {$this->organizer->description}"),
            )
            ->action('Examiner la demande', config('app.dashboard_url').'/app/organizers')
            ->line('Le compte reste en attente tant qu’il n’a pas été approuvé.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $userName = trim("{$this->user->first_name} {$this->user->last_name}");

        return [
            'title' => 'Nouvel organisateur inscrit',
            'message' => "Nouvel organisateur inscrit : {$this->organizer->company_name} ({$userName})",
            'action' => 'view_organizer',
            'url' => "/admin/organizers/{$this->organizer->id}",
            'organizer_id' => $this->organizer->id,
            'company_name' => $this->organizer->company_name,
            'user_name' => $userName,
            'user_email' => $this->user->email,
            'user_phone' => $this->user->phone,
            'created_at' => now()->toISOString(),
        ];
    }
}
