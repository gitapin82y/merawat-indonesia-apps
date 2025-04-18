<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Carbon\Carbon;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check() || Auth::user()->role != $role) {
            return redirect()->back()->with('toast', [
                'type' => 'error', 
                'message' => 'Anda Tidak Memiliki Akses'
            ]);
        }

        if (Auth::check() && Auth::user()->role === 'yayasan') {
            // Find the admin record associated with the user
            $admin = Admin::where('user_id', Auth::id())->first();
            
            if ($admin) {
                // Update the log_activity timestamp
                $admin->update(['log_activity' => Carbon::now()]);
            }
        }

        return $next($request);
    }
}
