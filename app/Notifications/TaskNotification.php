<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskNotification extends Notification
{
    use Queueable;

    protected $taskDetails;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($taskDetails)
    {
        $this->taskDetails = $taskDetails;
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
        $mailMessage = (new MailMessage)
            ->subject('Task and Schedule Notification')
            ->greeting('Hello, ' . $notifiable->firstname)
            ->line('Here are your assigned tasks:')
            ->line('Your schedule for the tasks:')
            ->line('Time In: ' . $this->taskDetails['schedule_time_in'])
            ->line('Time Out: ' . $this->taskDetails['schedule_time_out'])
            ->line('Schedule: ' . $this->taskDetails['schedule_day']);

        foreach ($this->taskDetails['tasks'] as $index => $task) {
            $mailMessage->line(($index + 1) . '. ' . $task['task']);
        }

        // $mailMessage->action('View Task List', url('/tasks'))
        //             ->line('Thank you for your dedication!');

        $mailMessage->line('Thank you for your dedication!');

        return $mailMessage;
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
            //
        ];
    }
}
