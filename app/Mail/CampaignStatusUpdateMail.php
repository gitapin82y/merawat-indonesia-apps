<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CampaignStatusUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $campaign;
    public $statusText;
    public $isApproved;
    public $isPending;

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
        $this->isPending = $status === 'validasi';
        $this->statusText = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Status Kampanye: ' . ucfirst($this->campaign->title);
        if ($this->isApproved) {
            $subject = 'Kampanye Anda Telah Disetujui - ' . $this->campaign->title;
        } elseif ($this->isPending) {
            $subject = 'Pengajuan Kampanye Baru - ' . $this->campaign->title;
        } else {
            $subject = 'Kampanye Anda Ditolak - ' . $this->campaign->title;
        }
            
        return $this->subject($subject)
                    ->view('emails.campaign-status-update')
                    ->with([
                        'campaign' => $this->campaign,
                        'isApproved' => $this->isApproved,
                        'isPending' => $this->isPending,
                        'statusText' => $this->statusText
                    ]);
    }
}