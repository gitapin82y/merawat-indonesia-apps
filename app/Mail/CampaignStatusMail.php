<?php
// app/Mail/CampaignStatusMail.php
namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $campaign;
    public $statusText;
    public $isApproved;

    /**
     * Create a new message instance.
     *
     * @param Campaign $campaign
     * @param string $status
     */
    public function __construct(Campaign $campaign, string $status)
    {
        $this->campaign = $campaign;
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
            'Kampanye Anda Telah Disetujui' : 
            'Kampanye Anda Tidak Disetujui';
            
        return $this->subject($subject)
                    ->view('emails.campaign-status')
                    ->with([
                        'campaign' => $this->campaign,
                        'isApproved' => $this->isApproved,
                        'statusText' => $this->statusText
                    ]);
    }
}