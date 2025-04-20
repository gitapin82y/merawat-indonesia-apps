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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login')->with('toast', [
                'type' => 'error', 
                'message' => 'Silahkan login terlebih dahulu'
            ]);
        }

        // Check if user has any of the required roles
        $userRole = Auth::user()->role;
        $hasRole = false;

        foreach ($roles as $role) {
            if ($userRole === $role) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            return redirect()->back()->with('toast', [
                'type' => 'error', 
                'message' => 'Anda Tidak Memiliki Akses'
            ]);
        }

        // If user is a yayasan, update their log_activity
        if (Auth::user()->role === 'yayasan') {
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