<?php

namespace Nyufeng\MakeDatabase;

use Illuminate\Support\ServiceProvider;
use Nyufeng\MakeDatabase\Console\Commands\BuildSeeder;

class MakeDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if($this->app->runningInConsole()){
            $this->commands([
                BuildSeeder::class
            ]);
        }
    }
}
