<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Models\KabarTerbaru;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        try {
            Log::info('Building email for campaign: ' . $this->campaign->title . ' to ' . $this->donor['email']);
            return $this->subject('Kabar Terbaru: ' . $this->campaign->title)
                        ->view('emails.campaign_update'); // Changed from markdown to view
        } catch (\Exception $e) {
            Log::error('Error building email: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}