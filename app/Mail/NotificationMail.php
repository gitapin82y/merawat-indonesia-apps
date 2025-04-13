<?php
// app/Mail/NotificationMail.php
namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function build()
    {
        $mail = $this->subject($this->notification->title)
                     ->view('emails.notification');

        if ($this->notification->image_path) {
            $mail->attach(storage_path('app/public/' . $this->notification->image_path), [
                'as' => 'notification-image.jpg',
                'mime' => 'image/jpeg',
            ]);
        }

        return $mail;
    }
}