<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProductExportNoDataMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $format
    ) {
    }

    public function build()
    {
        return $this->subject('Product export - No data found')
            ->markdown('emails.export_no_data')
            ->with([
                'user' => $this->user,
                'format' => $this->format,
            ]);
    }
}
