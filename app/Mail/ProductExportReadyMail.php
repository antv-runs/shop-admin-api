<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProductExportReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public User $user,
        public string $downloadUrl,
        public string $filename,
        public string $format,
        public int $productCount
    ) {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your product export is ready')
            ->markdown('emails.export_ready')
            ->with([
                'user' => $this->user,
                'downloadUrl' => $this->downloadUrl,
                'filename' => $this->filename,
                'format' => $this->format,
                'productCount' => $this->productCount,
            ]);
    }
}
