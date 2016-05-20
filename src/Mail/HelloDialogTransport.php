<?php
namespace Czim\HelloDialog\Mail;

use Czim\HelloDialog\Contracts\HelloDialogApiInterface;
use Czim\HelloDialog\Contracts\HelloDialogHandlerInterface;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_Message;

class HelloDialogTransport extends Transport
{
    /**
     * @var HelloDialogHandlerInterface
     */
    protected $handler;

    /**
     * @param HelloDialogHandlerInterface $handler
     */
    public function __construct(HelloDialogHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        // the message is a string made from a view
        // we can pass that on automatically as the typical replacement
        // for the content -- as set in the config

        // before send() is called, a config should be set specifically for the
        // mail implementation, so this class may know how to pass on the message
        // as data to be sent to HD throught the API instance
    }

}
