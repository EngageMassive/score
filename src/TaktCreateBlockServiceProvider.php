<?php

namespace Takt\Score;

use Illuminate\Support\ServiceProvider;

class TaktCreateBlockServiceProvider extends ServiceProvider
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
