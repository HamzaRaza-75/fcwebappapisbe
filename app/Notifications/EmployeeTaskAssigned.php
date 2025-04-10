<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeTaskAssigned extends Notification
{
    use Queueable;

    public $taskmilestone;
    /**
     * Create a new notification instance.
     */
    public function __construct($taskmilestone)
    {
        $this->taskmilestone = $taskmilestone;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'name' => User::find($this->taskmilestone->assigned_by)->name,
            'task' => $this->taskmilestone->task_milestone_name,
        ];
    }


    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(object $notifiable): string
    {
        return 'Task-Assigned';
    }
}
