<?php

namespace App\Notifications\Client;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientAccountActivationNotification extends Notification
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
                ->from('support@sparex.com')
                ->view('email.notification.client.client_account_status', compact('client'))
                ->subject('Client Account Status Update');
    }
    
    public function toArray(object $notifiable): array
    {
        $client = $this->client;
        return [
            'data' => 'Client Account Status Update',
            'client_id' => $client['id'],
        ];
    }
}
