<?php
// app/Mail/CampaignWithdrawalMail.php
namespace App\Mail;

use App\Models\CampaignWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CampaignWithdrawalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $withdrawal;

    /**
     * Create a new message instance.
     *
     * @param CampaignWithdrawal $withdrawal
     */
    public function __construct(CampaignWithdrawal $withdrawal)
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
        return $this->subject('Permintaan Pencairan Dana Kampanye Baru')
                    ->view('emails.campaign-withdrawal')
                    ->with([
                        'withdrawal' => $this->withdrawal,
                    ]);
    }
}