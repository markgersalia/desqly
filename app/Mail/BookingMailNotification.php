<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class BookingMailNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected $mailSubject;
    protected $template;
    protected $data;
    /**
     * Create a new message instance.
     */
    public function __construct($mailSubject, $template, $data)
    {
        //
        $this->mailSubject = $mailSubject;
        $this->template = $template;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: $this->template,
            with: ['data' => $this->data]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (!isset($this->data['status'])) { 
            $this->data['status'] = 'pending';
        }
 

        if ($this?->data['status'] != 'confirmed') {
            return [];
        }
        return [
            Attachment::fromPath($this->data['invoice']['file_path']),
        ];
    }
}
