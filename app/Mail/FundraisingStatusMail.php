<?php
// app/Mail/FundraisingStatusMail.php
namespace App\Mail;

use App\Models\FundraisingWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class FundraisingStatusMail extends Mailable 
{
    use Queueable, SerializesModels;

    public $withdrawal;
    public $emailData;

    /**
     * Create a new message instance.
     *
     * @param FundraisingWithdrawal $withdrawal
     */
    public function __construct(FundraisingWithdrawal $withdrawal, array $emailData = [])
    {
        $this->withdrawal = $withdrawal;
        $this->emailData = $emailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Update Status Pencairan Dana Fundraising';
        
        if ($this->withdrawal->status == 'disetujui') {
            $subject = 'Pencairan Dana Fundraising Disetujui';
            $view = 'emails.fundraising-withdrawal-approved';
        } else if ($this->withdrawal->status == 'ditolak') {
            $subject = 'Pencairan Dana Fundraising Ditolak';
            $view = 'emails.fundraising-withdrawal-rejected';
        } else {
            $view = 'emails.fundraising-withdrawal-status';
        }
        
        $mail = $this->subject($subject)
                     ->view($view)
                     ->with([
                         'withdrawal' => $this->withdrawal,
                     ]);
        
        // Add any additional data to the email
        if (!empty($this->emailData)) {
            $mail->with($this->emailData);
        }
                
        return $mail;
    }
}