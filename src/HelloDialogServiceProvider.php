<?php
namespace Czim\HelloDialog;

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
    }

}
