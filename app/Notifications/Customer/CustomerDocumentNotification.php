<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerDocumentNotification extends Notification
{
    use Queueable;

    private $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $customer = $this->customer;
        return (new MailMessage)
                ->from('support@sparex.com')->view('email.notification.customer.customer-document',compact('customer'))
                ->subject('New Customer Document');
    }

    public function toArray(object $notifiable): array
    {
        $customer = $this->customer;
        return [
            'data' => 'New Customer Document',
            'customer_id' => $customer['id'], 
        ];
    }
}
