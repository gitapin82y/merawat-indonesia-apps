<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\DonationLike;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
{
    try {
        $socialUser = Socialite::driver($provider)->user();
        
        // Cek apakah user sudah ada
        $user = User::where('provider_id', $socialUser->getId())
                ->where('provider', $provider)
                ->first();
        
        // Avatar dari provider (bisa berupa URL atau null)
        $avatarUrl = $socialUser->getAvatar();
        
        // Jika belum ada, buat user baru
        if (!$user) {
            // Cek apakah email sudah digunakan
            $existingUser = User::where('email', $socialUser->getEmail())->first();
            
            if ($existingUser) {
                // Update informasi provider pada user yang sudah ada
                $updateData = [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ];
                
                // Update avatar hanya jika ada avatar dari provider
                if ($avatarUrl) {
                    $updateData['avatar'] = $avatarUrl;
                }
                
                $existingUser->update($updateData);
                
                $user = $existingUser;
            } else {
                // Data untuk user baru
                $userData = [
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'role' => 'donatur',
                    'phone' => null, // Perlu penanganan tambahan
                ];
                
                // Tambahkan avatar jika ada
                if ($avatarUrl) {
                    $userData['avatar'] = $avatarUrl;
                }
                
                // Buat user baru
                $user = User::create($userData);
            }
        } else {
            // Update avatar jika ada perubahan dari provider
            if ($avatarUrl && $user->avatar != $avatarUrl) {
                $user->update(['avatar' => $avatarUrl]);
            }
        }
        
        // Login user
        Auth::login($user);

        if ($user->role === 'super_admin') {
            return redirect('/super-admin')->with('success', 'Login berhasil!');
        }
        
        // Migrasi likes dari guest ke user
        $guestIdentifier = request()->cookie('guest_identifier');
        if ($guestIdentifier) {
            DonationLike::where('guest_identifier', $guestIdentifier)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id, 'guest_identifier' => null]);
                
            // Clear cookie
            Cookie::forget('guest_identifier');
        }
        
        return redirect('/')->with('success', 'Login berhasil!');
        
    } catch (\Exception $e) {
        \Log::error('Social login error: ' . $e->getMessage());
        return redirect('/login')->with('error', 'Login gagal: ' . $e->getMessage());
    }
}
}