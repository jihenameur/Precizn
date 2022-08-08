<?php

namespace App\Notifications;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupplierNotification extends Notification
{
    use Queueable;
    private $channel;
    private $supplier;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Supplier $supplier, $channel = 'mail')
    {
        $this->supplier = $supplier;
        $this->channel = $channel;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Nouvelle inscription fournisseur')
                    ->line("un nouveau fournisseur s'est inscrit")
                    ->line('Nom: '. $this->supplier->firstName.' '.$this->supplier->lastName)
                    ->line('Email: '. $this->supplier->firstName)
                    ->line('Tel: '. $this->supplier->user->tel)
                    ->line('Numero: '. $this->supplier->id)
                    ->line('')
                    ->line('Merci de verifier ce fournisseur');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
