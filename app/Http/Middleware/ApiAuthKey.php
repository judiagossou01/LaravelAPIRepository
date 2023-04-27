<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (empty($request->header('apikey')) || $request->header('apikey') != config('digit.data.apikey')) {
            $data['status'] = Response::HTTP_INTERNAL_SERVER_ERROR;
            $data['message'] = 'Unauthorized';

            return response([
                'time' => microtime(true) - LARAVEL_START,
                'data' => $data
            ], Response::HTTP_INTERNAL_SERVER_ERROR)->header('Content-Type', "application/json");
        }
        return $next($request);
    }
}
