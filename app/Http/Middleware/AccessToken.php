<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AccessToken
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $proxyAuthToken = $request->header('proxy_auth_token');

        if (! $proxyAuthToken) {
            return $this->errorResponse('Unauthorized - No access token found!', null, 401);
        }

        try {
            // Calling the 36Blocks API to get user details from the provided proxy_auth_token
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->withHeaders([
                    'proxy_auth_token' => $proxyAuthToken,
                ])
                ->get(config('services.36Blocks.url').'/c/getDetails')
                ->throw();
        } catch (ConnectionException $exception) {
            return $this->errorResponse('Unable to reach auth service. Please try again later.', null, 503);
        } catch (RequestException $exception) {
            return $this->errorResponse('Unauthorized - Invalid or expired access token.', null, 401);
        } catch (Throwable $exception) {
            return $this->errorResponse('Something went wrong while validating access token.', null, 500);
        }

        $userData = $response->json('data.0') ?? null;

        if (! is_array($userData) || empty($userData['id'])) {
            return $this->errorResponse("Unauthorized - User can't be retrieved.", null, 401);
        }

        $request->attributes->set('user_details', $userData);

        return $next($request);
    }
}
