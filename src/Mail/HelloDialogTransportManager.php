<?php
namespace Czim\HelloDialog\Mail;

use Czim\HelloDialog\Contracts\HelloDialogApiInterface;
use Illuminate\Mail\TransportManager;

class HelloDialogTransportManager extends TransportManager
{

    /**
     * Create an instance of the HelloDialog Swift Transport driver.
     *
     * @return HelloDialogTransport
     */
    protected function createHellodialogDriver()
    {
        $api = $this->app->make(HelloDialogApiInterface::class, [ 'transactional' ]);

        return new HelloDialogTransport($api);
    }

}
