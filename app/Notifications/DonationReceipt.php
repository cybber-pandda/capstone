<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DonationReceipt extends Notification
{
    use Queueable;

    protected $donation;

    /**
     * Create a new notification instance.
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Donation Receipt')
                    ->greeting('Hello ' . $notifiable->name)
                    ->line('Thank you for your generous donation!')
                    ->line('Donation Amount: PHP ' . number_format($this->donation->donation_amount, 2))
                    ->line('Shelter: ' . $this->donation->shelter->shelter_name)
                    ->line('Donation Date: ' . $this->donation->created_at->toFormattedDateString())
                    ->line('We appreciate your support!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'donation_amount' => $this->donation->donation_amount,
            'shelter' => $this->donation->shelter->shelter_name,
            'donation_date' => $this->donation->created_at->toFormattedDateString(),
        ];
    }
}
