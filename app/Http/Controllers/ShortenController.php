<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteUrlRequest;
use App\Http\Requests\GetUrlStatsRequest;
use App\Http\Requests\StoreUrlRequest;
use App\Http\Requests\UpdateUrlRequest;
use App\Models\Click;
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
    public function index(Request $request)
    {
        try {
            $userId = $request->attributes->get('user_details')['id'];
            $records = Url::where('user_id', $userId)
                ->Paginate(10);

            return $this->successResponse($records);
        } catch (Throwable $e) {
            return $this->errorResponse(
                'Failed',
                ['exception' => $e->getMessage()],
                500
            );
        }

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
                'is_active' => $validated['is_active'],
                'is_blocked' => false,
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
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
    public function show(Request $request, string $id)
    {
        try {
            $userId = $request->attributes->get('user_details')['id'];

            $record = Url::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (! $record) {
                return $this->errorResponse('URL not found', null, 404);
            }

            return $this->successResponse($record);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed', ['exception' => $e->getMessage()], 401);
        }
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
    public function update(UpdateUrlRequest $request, string $id)
    {
        try {
            $validated = $request->validated();
            $record = Url::find($id);
            $record->update($validated);

            Cache::forget($record->short_code);
            Cache::put($record->short_code, $record, now()->addMinute(1440));

            return $this->successResponse($record, 'URL updated successfully');
        } catch (Throwable $e) {
            return $this->errorResponse('Failed', ['exception' => $e->getMessage()], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteUrlRequest $request, string $id)
    {
        try {
            $record = Url::find($id);
            $record->delete();
            Cache::forget($record->short_code);

            return $this->successResponse(null, 'URL deleted successfully', 204);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed', ['exception' => $e->getMessage()], 401);
        }
    }

    /**
     * Redirects from shortened URL to the original URL.
     *
     * Checks if URL record is present in cache, if not fetches from db and puts in cache too.
     * Checks in the record if it is blocked or isn't active.
     * Queues a job for logging the click.
     * Redirects the user to the original URL.
     */
    public function redirect(string $code, Request $request, UrlShortnerService $service, ClickService $clickService)
    {
        try {
            $record = Cache::remember($code, now()->addMinutes(1440), function () use ($code, $service) {
                return $service->resolveUrl($code);
            });

            // Check if the resource is blocked or inactive
            if ($record->is_blocked) {
                return $this->errorResponse('Failed - The resource is blocked.', null, 403);
            }
            if (! $record->is_active) {
                return $this->errorResponse('Failed - The resource is set inactive by the owner.', null, 403);
            }

            $clickService->logClick($record->id, $request);

            return redirect($record->url);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed', ['exception' => $e->getMessage()], 404);
        }
    }

    /**
     * Return stats related to a url.
     */
    public function getUrlStats(GetUrlStatsRequest $request, Url $url)
    {
        try {
            $cacheKey = "click_stats_{$url->id}_{$request->from}_{$request->to}_{$request->group_by}";

            // cache for 10 minutes
            $stats = Cache::remember($cacheKey, 60 * 10, function () use ($url, $request) {
                // unlimited counts for accurate totals
                $totalQuery = Click::where('url_id', $url->id)
                    ->when($request->from, fn ($q) => $q->whereDate('clicked_at', '>=', $request->from))
                    ->when($request->to, fn ($q) => $q->whereDate('clicked_at', '<=', $request->to));

                // limited dataset for heavy aggregations
                $baseIds = (clone $totalQuery)
                    ->orderBy('clicked_at', 'desc')
                    ->limit(10000)
                    ->pluck('id');

                $clicks = Click::whereIn('id', $baseIds);

                // group by option
                $groupBy = $request->group_by ?? 'day';
                $byTime = match ($groupBy) {
                    'week' => (clone $clicks)->selectRaw('YEARWEEK(clicked_at) as date, COUNT(*) as count')
                        ->groupBy('date')->orderBy('date')->get(),
                    'month' => (clone $clicks)->selectRaw('DATE_FORMAT(clicked_at, "%Y-%m") as date, COUNT(*) as count')
                        ->groupBy('date')->orderBy('date')->get(),
                    default => (clone $clicks)->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
                        ->groupBy('date')->orderBy('date')->get(),
                };

                return [
                    'message' => 'Except from total and unique, only last ten thousand clicks will be considered for analytics.',
                    'total' => $totalQuery->count(),
                    'unique' => $totalQuery->distinct('ip')->count('ip'),
                    'byTime' => $byTime,
                    'byCountry' => (clone $clicks)->selectRaw('country, COUNT(*) as count')
                        ->groupBy('country')->orderByDesc('count')->limit(10)->get(),
                    'byReferrer' => (clone $clicks)->selectRaw('referrer, COUNT(*) as count')
                        ->groupBy('referrer')->orderByDesc('count')->limit(10)->get(),
                ];
            });

            return $this->successResponse($stats);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed', ['exception' => $e->getMessage()], 401);
        }
    }
}
