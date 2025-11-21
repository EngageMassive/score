<?php

namespace Takt\Score;

use Illuminate\Support\ServiceProvider;

class ScoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([Console\Commands\CreateBlockCommand::class]);
        }
    }
}
