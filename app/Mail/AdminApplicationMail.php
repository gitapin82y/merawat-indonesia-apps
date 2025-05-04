<?php
// app/Mail/AdminApplicationMail.php
namespace App\Mail;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminApplicationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $admin;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param Admin $admin
     * @param User $user
     */
    public function __construct(Admin $admin, User $user)
    {
        $this->admin = $admin;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pendaftaran Admin Baru Memerlukan Persetujuan')
                    ->view('emails.admin-application')
                    ->with([
                        'admin' => $this->admin,
                        'user' => $this->user
                    ]);
    }
}