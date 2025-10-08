<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Admins;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->token) {
                $user->token()->revoke();
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            session(['token' => $token]);

            return redirect()->route('dashboard')->with('success', 'Berhasil masuk sebagai Kasir');
        }

        return redirect()->back()->withInput($request->only('username', 'password'))->with('error', 'Username atau password salah');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();

        if ($token)
            $token->revoke();

        $request->user()->token()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
