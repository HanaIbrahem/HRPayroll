<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class AuthController extends Controller
{
    //
    public function index()
    {
        return view('login');
    }

 
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required','string'],
            'password' => ['required','string'],
        ]);

        // Find by username
        $user = User::where('username', $credentials['username'])->first();

        // If username/password valid but user is inactive → show specific message
        if ($user && Hash::check($credentials['password'], $user->password) && ! $user->is_active) {
            return back()
                ->withErrors(['username' => 'Your account is inactive. Please contact the administrator.'])
                ->onlyInput('username');
        }

        // Normal login (username + password)
        if (Auth::attempt(
            ['username' => $credentials['username'], 'password' => $credentials['password']],
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Generic error for wrong username/password
        return back()
            ->withErrors(['username' => 'Invalid username or password.'])
            ->onlyInput('username');
    }

    public function destroy(Request $request)
    {
    Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');


    }

}
