<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    private $data = [];
    public $view = '';
    public $subject = '';

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
        if($this->data['type'] == 'password-recover'){
            $this->view = 'partials.mail.recover-password';
            $this->subject = 'Recover your password!';
        }
        else if($this->data['type'] == 'leave-event'){
            $this->view = 'partials.mail.leave-event';
            $eventName = $this->data['event'];
            $this->subject = "You left $eventName!";
        }
        else if($this->data['type'] == 'join-event'){
            $this->view = 'partials.mail.join-event';
            $eventName = $this->data['event'];
            $this->subject = "Welcome to $eventName!";
        }
        else if($this->data['type'] == 'invite-event'){
            $this->view = 'partials.mail.invite-event';
            $eventName = $this->data['event'];
            $this->subject = "You have been invited to $eventName!";
        }
        else if($this->data['type'] == 'request-to-join-event'){
            $this->view = 'partials.mail.request-to-join-event';
            $eventName = $this->data['event'];
            $this->subject = "You have a request to join $eventName!";
        }
        else if($this->data['type'] == 'accept-request-to-join-event'){
            $this->view = 'partials.mail.accept-request-to-join-event';
            $eventName = $this->data['event'];
            $this->subject = "Your request to join $eventName was accepted!";
        }
        else if($this->data['type'] == 'deny-request-to-join-event'){
            $this->view = 'partials.mail.deny-request-to-join-event';
            $eventName = $this->data['event'];
            $this->subject = "Your request to join $eventName was denied!";
        }
        else if($this->data['type'] == 'event-update'){
            $this->view = 'partials.mail.event-update';
            $eventName = $this->data['whatChanged']['old_name'];
            $this->subject = "$eventName was updated!";
        }
        else if($this->data['type'] == 'cancel-event'){
            $this->view = 'partials.mail.cancel-event';
            $eventName = $this->data['event'];
            $this->subject = "$eventName was cancelled!";
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {   
        return new Envelope(
            subject: $this->subject
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->view,
            with: $this->data
        );
    }
}
