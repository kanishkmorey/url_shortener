<?php

namespace App\Providers;

use App\Services\SnowflakeService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('api', function (Request $request) {
            $userId = $request->attributes->get('user_details')['id'] ?? $request->ip();

            return Limit::perMinute(60)->by($userId);
        });

        RateLimiter::for('redirect', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        RateLimiter::for('health', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

    }
}
