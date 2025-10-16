<?php

namespace App\Notifications\Product;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductPublishNotification extends Notification
{
    use Queueable;

    private $product;
    private $publishDate; 

    public function __construct($product, $publishDate)
    {
        $this->product = $product;
        $this->publishDate = $publishDate;
    }


    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $product = $this->product;
        $publishDate = $this->publishDate; 
        
        return (new MailMessage)
                ->from('support@sparex.com.com')
                ->view('email.notification.product.product_publish', compact('product', 'publishDate'))
                ->subject('Product Publish Confirmation');
    }
    
    public function toArray(object $notifiable): array
    {
        $product = $this->product;
        return [
            'data' => 'Product Publish',
            'product_id' => $product['id'],
        ];
    }
}
