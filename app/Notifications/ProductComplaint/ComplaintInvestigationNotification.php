<?php

namespace App\Notifications\ProductComplaint;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintInvestigationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $complaint;
    private $recipientType;

    public function __construct($complaint,$recipientType = 'customer')
    {
        $this->complaint = $complaint;
        $this->recipientType = $recipientType;
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
        $recipientType = $this->recipientType; 
        return (new MailMessage)
                ->from('support@sparex.com.com')
                ->view('email.notification.product-complaint.product_complaint_investigation', compact('complaint','recipientType'))
                ->subject('Product Complaint Status Update');
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
            'data' => 'Product Complaint Status Update',
            'complaint_id' => $complaint ? $complaint->id : null,
        ];
    }
}

