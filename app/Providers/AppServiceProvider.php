<?php

namespace App\Providers;

use App\Services\SnowflakeService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SnowflakeService::class, function () {

            // Using worker PID as machine ID, as currently intended for single machine.
            $machineId = getmypid() % 1024;

            return new SnowflakeService($machineId);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
