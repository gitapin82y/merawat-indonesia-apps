<?php
// app/Mail/FundraisingWithdrawalMail.php
namespace App\Mail;

use App\Models\FundraisingWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FundraisingWithdrawalMail extends Mailable implements ShouldQueue
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
        return $this->subject('Permintaan Pencairan Dana Fundraising Baru')
                    ->view('emails.fundraising-withdrawal')
                    ->with([
                        'withdrawal' => $this->withdrawal,
                    ]);
    }
}