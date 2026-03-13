<?php

namespace App\Services;

use App\Models\Click;
use Illuminate\Http\Request;

class ClickService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function logClick(int $urlId, Request $request)
    {
        Click::create([
            'url_id' => $urlId,
            'clicked_at' => now(),
            'ip' => $this->convertIp($request->ip()),
            'referrer' => $request->headers->get('referer') ?? null,
            'user_agent' => $request->userAgent(),
            'country' => null, // will add later with GeoIP
        ]);
    }

    private function convertIp(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        return inet_pton($ip); // converts IPv4/IPv6 to binary
    }
}
