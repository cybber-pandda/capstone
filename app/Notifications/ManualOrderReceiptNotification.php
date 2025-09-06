<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManualOrderReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $type;

    public function __construct($order, $type)
    {
        $this->order = $order;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {    
        if ($this->type === 'approve') {
            return (new MailMessage)
                ->subject('Your Order Receipt')
                ->view('emails.manual_order_receipt', [
                    'order' => $this->order
                ]);
        }

        if ($this->type === 'reject') {
            return (new MailMessage)
                ->subject('Order Rejected')
                ->line('We regret to inform you that your manual order has been rejected.')
                ->line('Order ID: ' . $this->order->id)
                ->line('If you believe this is a mistake, please contact our support team.');
        }

        return (new MailMessage)->subject('Order Update')->line('Your order status has been updated.');
    }
}
