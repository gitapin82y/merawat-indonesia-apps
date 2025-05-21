<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class DataDeletionRequestMail extends Mailable 
{
    use Queueable, SerializesModels;

    public $requestData;

    /**
     * Create a new message instance.
     *
     * @param array $requestData
     */
    public function __construct(array $requestData)
    {
        $this->requestData = $requestData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Permintaan Penghapusan Data Baru')
                    ->view('emails.data-deletion-request')
                    ->with([
                        'requestData' => $this->requestData,
                        'requestDate' => now()->format('d F Y H:i:s')
                    ]);
    }
}