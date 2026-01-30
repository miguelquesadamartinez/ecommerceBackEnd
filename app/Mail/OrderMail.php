<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable// implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $imagePath;

    public function __construct(private $order, private $pharmacy, private $referece)
    {
        $this->imagePath = storage_path('app/noName/noName-logo-color.jpg');
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'NoName Ventes Directes : confirmation de votre commande n° ' . $this->referece,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'email.orderMail',
            with: ['order' => $this->order, 'imagePath' => $this->imagePath, 'pharmacy' => $this->pharmacy],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
