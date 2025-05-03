<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class TripayIpMiddleware
{
    protected $allowedIps = [
        // IP resmi dari Tripay untuk callback
        '95.111.200.230', // IPv4
        '2a04:3543:1000:2310:ac92:4cff:fe87:63f9', // IPv6
    ];
    
    public function handle($request, Closure $next)
    {
        // Matikan IP check untuk testing atau development
        if (env('APP_ENV') === 'local' || !env('TRIPAY_IP_CHECK', true)) {
            Log::info('Tripay IP check disabled');
            return $next($request);
        }
        
        $requestIp = $request->ip();
        
        // Debug logging
        Log::info('Tripay callback IP check:', [
            'request_ip' => $requestIp,
            'allowed_ips' => $this->allowedIps,
            'x_forwarded_for' => $request->header('X-Forwarded-For'),
            'x_real_ip' => $request->header('X-Real-IP'),
        ]);
        
        if (!in_array($requestIp, $this->allowedIps)) {
            Log::warning('Unauthorized Tripay callback IP: ' . $requestIp);
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized IP address'
            ], 403);
        }
        
        return $next($request);
    }
}