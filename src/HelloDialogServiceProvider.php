<?php
namespace Czim\HelloDialog;

use Czim\HelloDialog\Contracts\HelloDialogApiInterface;
use Czim\HelloDialog\Contracts\HelloDialogHandlerInterface;
use Illuminate\Support\ServiceProvider;

class HelloDialogServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/hellodialog.php' => config_path('hellodialog.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/hellodialog.php', 'hellodialog'
        );

        $this->registerHelloDialogInterfaces();
    }


    /**
     * Register Hello Dialog class interfaces
     */
    protected function registerHelloDialogInterfaces()
    {
        $this->app->bind(HelloDialogApiInterface::class, HelloDialogApi::class);
        $this->app->bind(HelloDialogHandlerInterface::class, HelloDialogHandler::class);
    }

}
