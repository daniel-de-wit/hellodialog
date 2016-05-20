<?php
namespace Czim\HelloDialog\Mail;

use Czim\HelloDialog\Contracts\HelloDialogApiInterface;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_Message;

class HelloDialogTransport extends Transport
{
    /**
     * @var HelloDialogApiInterface
     */
    protected $api;

    /**
     * @param HelloDialogApiInterface $api
     */
    public function __construct(HelloDialogApiInterface $api)
    {
        $this->api = $api;
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
