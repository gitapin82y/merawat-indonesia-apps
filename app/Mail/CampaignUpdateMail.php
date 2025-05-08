<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Models\KabarTerbaru;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $donor;
    public $campaign;
    public $kabarTerbaru;

    /**
     * Create a new message instance.
     *
     * @param array $donor
     * @param Campaign $campaign
     * @param KabarTerbaru $kabarTerbaru
     * @return void
     */
    public function __construct($donor, Campaign $campaign, KabarTerbaru $kabarTerbaru)
    {
        $this->donor = $donor;
        $this->campaign = $campaign;
        $this->kabarTerbaru = $kabarTerbaru;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Kabar Terbaru: ' . $this->campaign->title)
                    ->markdown('emails.campaign_update');
    }
}