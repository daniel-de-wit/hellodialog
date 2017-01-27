<?php
namespace Czim\HelloDialog;

use Czim\HelloDialog\Contracts\HelloDialogApiFactoryInterface;
use Czim\HelloDialog\Contracts\HelloDialogHandlerInterface;
use Czim\HelloDialog\Factories\HelloDialogApiFactory;
use Czim\HelloDialog\Mail\HelloDialogTransport;
use Illuminate\Support\ServiceProvider;

class HelloDialogServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/hellodialog.php' => config_path('hellodialog.php'),
        ]);

        $this->registerHelloDialogMailDriver();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/hellodialog.php', 'hellodialog'
        );

        $this->registerHelloDialogInterfaces();
    }


    /**
     * Registers Hello Dialog class interfaces
     */
    protected function registerHelloDialogInterfaces()
    {
        $this->app->bind(HelloDialogApiFactoryInterface::class, HelloDialogApiFactory::class);
        $this->app->bind(HelloDialogHandlerInterface::class, HelloDialogHandler::class);
    }

    /**
     * Registers a custom mail driver with the transport manager
     */
    protected function registerHelloDialogMailDriver()
    {
        $this->app['swift.transport']->extend(
            'hellodialog',
            $this->app->share(function ($app) {
                $handler = $app->make(HelloDialogHandlerInterface::class);
                return new HelloDialogTransport($handler);
            })
        );
    }
}
