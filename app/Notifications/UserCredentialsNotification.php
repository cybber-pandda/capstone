<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCredentialsNotification extends Notification
{
    use Queueable;

    protected $name;
    protected $username;
    protected $email;
    protected $password;
    protected $role;

    public function __construct($name, $username, $email, $role, $password = null)
    {
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Your TanticoCTC Account Details')
            ->greeting('Hello ' . $this->name . ',')
            ->line("Welcome to TanticoCTC! You have been registered as a **{$this->role}**.")
            ->line('Below are your account credentials:')
            ->line("• **Username:** {$this->username}")
            ->line("• **Email:** {$this->email}");

        if ($this->password) {
            $message->line("• **Temporary Password:** {$this->password}");
        }

        $message->line('⚠️ For your security, please log in and change your password immediately.')
            ->line('If you did not expect this email, please contact support.');

        return $message;
    }
}
