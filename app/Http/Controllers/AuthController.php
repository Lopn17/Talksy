<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('login');
    }

    public function registerPage()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if(Auth::attempt($credentials))
        {
            return redirect('/chat');
        }

        return back()->with('error', 'Login gagal');
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/login');
    }
}