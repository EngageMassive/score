<?php

namespace Takt\Score\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades;

use Takt\Score\Score;
use Takt\Score\View\Composers\BlockComposer;
use Takt\Score\Console\Commands\CreateBlockCommand;

class ScoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Score', function () {
            return new Score($this->app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([CreateBlockCommand::class]);
        }

        Facades\View::composer(['*.view'], BlockComposer::class);
    }
}
