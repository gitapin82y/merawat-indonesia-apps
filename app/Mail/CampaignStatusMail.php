<?php
// app/Mail/CampaignStatusMail.php
namespace App\Mail;

use App\Models\CampaignWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class CampaignStatusMail extends Mailable 
{
    use Queueable, SerializesModels;

    public $withdrawal;
    public $emailData;

    /**
     * Create a new message instance.
     *
     * @param CampaignWithdrawal $withdrawal
     * @param array $emailData Optional additional data for email
     */
    public function __construct(CampaignWithdrawal $withdrawal, array $emailData = [])
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
        $subject = 'Update Status Pencairan Dana Kampanye';
        
        if ($this->withdrawal->status == 'disetujui') {
            $subject = 'Pencairan Dana Kampanye Disetujui';
            $view = 'emails.campaign-withdrawal-approved';
        } else if ($this->withdrawal->status == 'ditolak') {
            $subject = 'Pencairan Dana Kampanye Ditolak';
            $view = 'emails.campaign-withdrawal-rejected';
        } else {
            $view = 'emails.campaign-withdrawal-status';
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