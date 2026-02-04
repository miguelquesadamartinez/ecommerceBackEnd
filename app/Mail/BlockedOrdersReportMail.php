<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class BlockedOrdersReportMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $filePath;
    protected $fileName;
    protected $ordersCount;
    protected $orderSentReport;
    protected $filename_2;
    protected $ordersSentCount;

    /**
     * Create a new message instance.
     */
    public function __construct($filePath, $fileName, $ordersCount, $orderSentReport, $filename_2, $ordersSentCount)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->ordersCount = $ordersCount;
        $this->orderSentReport = $orderSentReport;
        $this->filename_2 = $filename_2;
        $this->ordersSentCount = $ordersSentCount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rapport des commandes bloquées/expédiées - ' . date('d/m/Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.blocked-orders-report',
            with: [
                'ordersCount' => $this->ordersCount,
                'ordersSentCount' => $this->ordersSentCount,
                'date' => date('d/m/Y'),
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
            Attachment::fromPath($this->orderSentReport)
                ->as($this->filename_2)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
