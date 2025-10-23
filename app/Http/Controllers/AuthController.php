<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Greetings;
use App\Models\GreetingTypes;

class AuthController extends Controller
{
    public function index()
    {
        $greeting = Greetings::whereHas('type', function ($query) {
            $query->where('name', 'login');
        })->inRandomOrder()->limit(1)->get(['name'])->first();

        return view('auth.login', ['greeting' => $greeting->name ?? 'Selamat datang di Apotek!']);
    }

    public function login(Request $request)
    {

        // $user = User::where('username', $request->username)->first();

        // $debug = [
        //     'input' => [
        //         'username' => $request->username,
        //         'password_length' => strlen($request->password),
        //     ],
        //     'user_found' => (bool) $user,
        //     'hash_match' => $user ? Hash::check($request->password, $user->password) : false,
        // ];

        // dd($debug);

        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt([
                'username' => $request->input('username'),
                'password' => $request->input('password'),
            ], 
                $request->boolean('remember'))) {
            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $currentId = $request->session()->getId();

        if (config('session.driver') === 'database') {
            DB::table('sessions')
                ->where('user_id', $user->getAuthIdentifier())
                ->where('id', '!=', $currentId)
                ->delete();
        }

        $user->forceFill(['current_session_id' => $currentId])->save();

        if ($user->role === 'admin') {
            return redirect()->route('dashboard');
        }

        if ($user->role === 'manager') {
            return redirect()->route('dashboard');
        }

        // Default users
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();

        if ($user) {
            $user->forceFill(['current_session_id' => null])->save();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
