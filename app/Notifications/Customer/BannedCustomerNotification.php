<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BannedCustomerNotification extends Notification
{
    use Queueable;

    private $bannedCustomer;
    private $action; // 'banned' or 'unbanned'

    public function __construct($bannedCustomer, $action = 'banned')
    {
        $this->bannedCustomer = $bannedCustomer;
        $this->action = $action;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->action === 'banned' 
            ? 'Customer Access Restricted' 
            : 'Customer Access Restored';

        return (new MailMessage)
            ->from('support@sparex.com')
            ->view('email.notification.customer.banned_customer', [
                'bannedCustomer' => $this->bannedCustomer,
                'notifiable' => $notifiable,
                'action' => $this->action
            ])
            ->subject($subject);
    }
    
    public function toArray(object $notifiable): array
    {
        $customer = $this->bannedCustomer->customer;
        return [
            'data' => $this->action === 'banned' ? 'Banned Customer' : 'Unbanned Customer',
            'customer_id' => $customer['id'],
            'action' => $this->action,
        ];
    }
}