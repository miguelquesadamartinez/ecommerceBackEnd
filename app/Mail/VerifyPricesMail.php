<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class VerifyPricesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $description;
    public $orderSummary;
    public $notFoundProducts;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->description = $data['description'];
        $this->orderSummary = $data['orderSummary'] ?? [];
        $this->notFoundProducts = $data['notFoundProducts'] ?? [];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->description,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.verify-prices-mail',
            with: [
                'description' => $this->description,
                'orderSummary' => $this->orderSummary,
                'notFoundProducts' => $this->notFoundProducts,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}