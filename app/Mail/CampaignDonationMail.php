<?php
// app/Mail/CampaignDonationMail.php
namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignDonationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $donation;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    public function build()
    {
        return $this->subject('Donasi Baru untuk Kampanye Anda')
                    ->view('emails.campaign-donation');
    }
}