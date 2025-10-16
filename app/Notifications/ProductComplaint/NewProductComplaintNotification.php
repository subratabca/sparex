<?php

namespace App\Notifications\ProductComplaint;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewProductComplaintNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $complaint;

    public function __construct($complaint)
    {
        $this->complaint = $complaint;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */

    public function toMail(object $notifiable): MailMessage
    {
        $complaint = $this->complaint; 
        return (new MailMessage)
                ->from('support@sparex.com')
                ->view('email.notification.product-complaint.new_product_complaint_by_customer', compact('complaint'))
                ->subject('New Product Complaint Received');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $complaint = $this->complaint;
        return [
            'data' => 'New Product Complaint Received',
            'complaint_id' => $complaint ? $complaint->id : null,
        ];
    }
}
