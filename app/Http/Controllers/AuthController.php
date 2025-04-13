<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AuthController extends Controller
{
     // Tampilkan halaman login
     public function login()
     {
         return view('auth.login');
     }
 
     // Proses login
     public function loginProcess(Request $request)
     {
         $request->validate([
             'email' => 'required|email',
             'password' => 'required'
         ]);
 
         if (Auth::attempt($request->only('email', 'password'))) {
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
