<?php

namespace App\Services;

use App\Jobs\LogClickJob;
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

    public function logClick(int $url_id, Request $request)
    {
        LogClickJob::dispatch(
            $url_id,
            $request->ip(),
            $request->headers->get('referer') ?? null,
            $request->userAgent()
        );
    }
}
