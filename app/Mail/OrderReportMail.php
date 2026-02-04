<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Log;

class OrderReportMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $filePath;
    protected $fileName;
    protected $ordersCount;
    protected $subject_2;
    protected $text2;

    /**
     * Create a new message instance.
     */
    public function __construct($filePath, $fileName, $ordersCount, $subject_2, $text2)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->ordersCount = $ordersCount;
        $this->subject_2 = $subject_2;
        $this->text2 = $text2;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject_2 . ' - ' . date('d/m/Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-report',
            with: [
                'text2' => $this->text2,
                'subject_2' => $this->subject_2,
                'ordersCount' => $this->ordersCount,
                'date' => date('d/m/Y')
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
        return [
            Attachment::fromPath($this->filePath)
                ->as($this->fileName)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}