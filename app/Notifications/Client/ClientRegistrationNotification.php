<?php

namespace App\Notifications\Client;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientRegistrationNotification extends Notification
{
    use Queueable;

    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $client = $this->client;
        return (new MailMessage)
                ->from('support@sparex.com')->view('email.notification.client.client-registration',compact('client'))
                ->subject('New Client Registration');
    }

    public function toArray(object $notifiable): array
    {
        $client = $this->client;
        return [
            'data' => 'New Client Registration',
            'client_id' => $client['id'], 
        ];
    }
}
