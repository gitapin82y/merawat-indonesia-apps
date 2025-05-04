<?php
// app/Mail/FundraisingStatusMail.php
namespace App\Mail;

use App\Models\FundraisingWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FundraisingStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $withdrawal;

    /**
     * Create a new message instance.
     *
     * @param FundraisingWithdrawal $withdrawal
     */
    public function __construct(FundraisingWithdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
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
            return $this->subject($subject)
                    ->view('emails.fundraising-withdrawal-approved')
                    ->with([
                        'withdrawal' => $this->withdrawal,
                    ]);
        } else if ($this->withdrawal->status == 'ditolak') {
            $subject = 'Pencairan Dana Fundraising Ditolak';
            return $this->subject($subject)
                    ->view('emails.fundraising-withdrawal-rejected')
                    ->with([
                        'withdrawal' => $this->withdrawal,
                    ]);
        }
        
        return $this->subject($subject)
                ->view('emails.fundraising-withdrawal-status')
                ->with([
                    'withdrawal' => $this->withdrawal,
                ]);
    }
}