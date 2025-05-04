<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCampaignNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $campaign;
    public $admin;

    /**
     * Create a new message instance.
     *
     * @param Campaign $campaign
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->admin = $campaign->admin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pengajuan Kampanye Baru - ' . $this->campaign->title)
                    ->view('emails.new-campaign-notification')
                    ->with([
                        'campaign' => $this->campaign,
                        'admin' => $this->admin
                    ]);
    }
}