<?php
// app/Mail/DonationSuccessMail.php
namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DonationSuccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $donation;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    public function build()
    {
        return $this->subject('Terima Kasih untuk Donasi Anda')
                    ->view('emails.donation-success');
    }
}