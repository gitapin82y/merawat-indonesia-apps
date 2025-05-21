<?php
namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class AdminStatusMail extends Mailable 
{
    use Queueable, SerializesModels;

    public $admin;
    public $statusText;
    public $isApproved;

    /**
     * Create a new message instance.
     *
     * @param Admin $admin
     * @param string $status
     */
    public function __construct(Admin $admin, string $status)
    {
        $this->admin = $admin;
        $this->isApproved = $status === 'disetujui';
        $this->statusText = $this->isApproved ? 'disetujui' : 'ditolak';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->isApproved ? 
            'Pendaftaran Admin Anda Disetujui' : 
            'Pendaftaran Admin Anda Ditolak';
            
        return $this->subject($subject)
                    ->view('emails.admin-status')
                    ->with([
                        'admin' => $this->admin,
                        'isApproved' => $this->isApproved,
                        'statusText' => $this->statusText
                    ]);
    }
}