<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class TripayIpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Daftar IP Tripay yang diizinkan
        $allowedIPs = [
            '95.111.200.230', // IPv4
            '2a04:3543:1000:2310:ac92:4cff:fe87:63f9', // IPv6
        ];
        
        // Dapatkan IP address pengirim
        $requestIP = $request->ip();
        $forwardedIp = $request->header('X-Forwarded-For');
        
        // Log untuk debugging
        Log::info('Tripay request IP: ' . $requestIP);
        Log::info('X-Forwarded-For: ' . $forwardedIp);
        
        // Untuk development/testing, bisa dilewati
        if (env('APP_ENV') === 'local') {
            return $next($request);
        }
        
        // Cek IP address - perhatikan bahwa beberapa server ada di belakang reverse proxy
        $ipToCheck = [$requestIP];
        if ($forwardedIp) {
            $ipToCheck = array_merge($ipToCheck, explode(',', $forwardedIp));
        }
        
        $isAllowed = false;
        foreach ($ipToCheck as $ip) {
            $ip = trim($ip);
            if (in_array($ip, $allowedIPs)) {
                $isAllowed = true;
                break;
            }
        }
        
        if (!$isAllowed) {
            Log::warning('Unauthorized IP trying to access Tripay callback', [
                'requestIP' => $requestIP,
                'forwardedIp' => $forwardedIp,
                'allIps' => $ipToCheck
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized IP address'
            ], 403);
        }
        
        return $next($request);
    }
}