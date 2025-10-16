<?php

namespace App\Notifications\Product;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductUploadNotification extends Notification
{
    use Queueable;

    private $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    public function toMail($notifiable)
    {
        $product = $this->product;
        $client = $product->client; 
        
        return (new MailMessage)
            ->from('support@webhunter24.com')
            ->view('email.notification.product.product_upload', compact('product', 'client'))
            ->subject('New Product Upload');
    }

    public function toArray(object $notifiable): array
    {
        $product = $this->product;
        return [
            'data' => 'New Product Upload',
            'product_id' => $product['id'],
        ];
    }
}
