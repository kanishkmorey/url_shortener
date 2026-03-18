<?php

namespace App\Jobs;

use App\Models\Click;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogClickJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $url_id,
        private ?string $ip,
        private ?string $referrer,
        private ?string $user_agent
    ) {}

    public function handle(): void
    {
        $location = geoip($this->ip);

        Click::create([
            'url_id' => $this->url_id,
            'clicked_at' => now(),
            'ip' => $this->ip ? inet_pton($this->ip) : null,
            'referrer' => $this->referrer,
            'user_agent' => $this->user_agent,
            'country' => $location->iso_code ?? null,
        ]);
    }
}
