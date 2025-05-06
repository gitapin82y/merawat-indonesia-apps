<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use App\Models\DonationLike;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;


class AuthController extends Controller
{
     // Tampilkan halaman login
     public function login()
     {
         return view('auth.login');
     }
     public function forgotPassword()
     {
         return view('auth.forgot-password');
     }
     public function sendResetLink(Request $request)
     {
         $request->validate(['email' => 'required|email']);
 
         // Cek apakah email terdaftar
         $user = User::where('email', $request->email)->first();
         
         if (!$user) {
             return back()->with('error', 'Email tidak ditemukan.');
         }
 
         // Cek jika user adalah social login dan tidak punya password
         if ($user->provider && !$user->password) {
             return back()->with('error', 'Akun ini terdaftar melalui ' . ucfirst($user->provider) . '. Anda tidak bisa reset password.');
         }
 
         $status = Password::sendResetLink(
             $request->only('email')
         );
 
         return $status === Password::RESET_LINK_SENT
                     ? back()->with('success', 'Kami telah mengirimkan link reset password ke email Anda!')
                     : back()->withErrors(['email' => __($status)]);
     }

     public function resetPassword(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Proses update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('success', 'Password berhasil diubah! Silakan login dengan password baru Anda.')
                    : back()->withErrors(['email' => [__($status)]]);
    }
     
 
     public function loginProcess(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);
    
    // Cek apakah user exists dan gunakan social login
    $user = User::where('email', $request->email)->first();
    
    // Jika user ada dan menggunakan social provider tapi tidak punya password
    if ($user && $user->provider && !$user->password) {
        return back()->with('error', 'Akun ini terdaftar melalui ' . ucfirst($user->provider) . '. Silakan login menggunakan ' . ucfirst($user->provider) . '.');
    }
    
    // Coba login normal
    if (Auth::attempt($request->only('email', 'password'))) {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            return redirect('/super-admin')->with('success', 'Login berhasil!');
        }
        
        
        // Get guest identifier from cookie
        $guestIdentifier = $request->cookie('guest_identifier');

        if ($guestIdentifier) {
            // Migrate guest likes
            DonationLike::where('guest_identifier', $guestIdentifier)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id, 'guest_identifier' => null]);
                
            return redirect('/')->with('success', 'Login berhasil!')
                              ->withCookie(Cookie::forget('guest_identifier'));
        }
        
        // Normal redirect if no cookie
        return redirect('/')->with('success', 'Login berhasil!');
    }

    return back()->with('error', 'Email atau password salah.');
}
 
     // Tampilkan halaman register
     public function register()
     {
        
         return view('auth.register');
     }
 
     // Proses registrasi
     public function registerProcess(Request $request)
     {
         $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^08[1-9][0-9]{7,10}$/|min:10|max:13',
            'email' => 'required|email|unique:users,email',
             'password' => 'required|min:6|confirmed',
         ]);
 
         $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => 'donatur',
            'email' => $request->email,
             'password' => Hash::make($request->password),
         ]);
 
         Auth::login($user);
         return redirect()->route('login')->with('success', 'Registrasi berhasil! Selamat datang.');
     }
 
     // Logout
     public function logout()
     {
         Auth::logout();
         return redirect()->route('login')->with('toast', [
            'type' => 'success', 
            'message' => 'Anda telah logout.'
        ]);
     }
}
