<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

class LogClickJob implements ShouldQueue
{
    use Queueable;

    public const REDIS_KEY = 'clicks:buffer';

    public function __construct(
        private int $url_id,
        private ?string $ip,
        private ?string $referrer,
        private ?string $user_agent
    ) {}

    public function handle(): void
    {
        $location = geoip($this->ip);

        $clickData = json_encode([
            'url_id' => $this->url_id,
            'clicked_at' => now()->toDateTimeString(),
            'ip' => $this->ip ?? null,
            'referrer' => $this->referrer,
            'user_agent' => $this->user_agent,
            'country' => $location->iso_code ?? null,
        ]);

        $bufferSize = Redis::rpush(self::REDIS_KEY, $clickData);

        $batchSize = (int) config('clicks.batch_size', 100);

        if ($bufferSize >= $batchSize) {
            Artisan::call('clicks:flush');
        }
    }
}
