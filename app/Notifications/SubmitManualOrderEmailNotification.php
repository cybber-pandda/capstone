<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmitManualOrderEmailNotification extends Notification
{
    use Queueable;

    protected $orderId;
    protected $email;

    public function __construct($orderId, $email)
    {
        $this->orderId = $orderId;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Create the URL with route parameters
        $url = route('manual-order.process', [
            'id' => $this->orderId,
            'email' => $this->email
        ]);

        return (new MailMessage)
            ->subject('Tantuco Hardware - Manual Order Processing Request')
            ->greeting('Hello from Tantuco Hardware!')
            ->line('We have received a request to process a manual order for your account at Tantuco Hardware.')
            ->line('To proceed, please click the button below to review and complete your manual order.')
            ->action('Process Manual Order', $url)
            ->line('Thank you for choosing Tantuco Hardware. We value your business!');
    }

    public function toArray($notifiable)
    {
        return [];
    }
}

