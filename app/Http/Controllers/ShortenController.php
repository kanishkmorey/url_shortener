<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUrlRequest;
use App\Models\Url;
use App\Services\ClickService;
use App\Services\UrlShortnerService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ShortenController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUrlRequest $request, UrlShortnerService $service)
    {
        try {
            $validated = $request->validated();
            $generatedCode = $service->generateCode();

            Url::create([
                'user_id' => $request->attributes->get('user_details')['id'],
                'url' => $validated['url'],
                'short_code' => $generatedCode,
                'is_active' => true,
                'is_blocked' => false,
                'title' => $request->title,
                'description' => $request->description ?? null,
                'meta' => [],
            ]);

            return $this->successResponse(
                ['url' => config('app.url').'/'.$generatedCode],
                'URL shortened successfully'
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                'Failed',
                ['exception' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {}

    public function redirect(string $code, Request $request, UrlShortnerService $service, ClickService $clickService)
    {
        try {
            $record = Cache::remember($code, now()->addMinutes(1440), function () use ($code, $service) {
                return $service->resolveUrl($code);
            });
            $clickService->logClick($record->id, $request);

            return redirect($record->url);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed', ['exception' => $e->getMessage()], 404);
        }
    }
}
