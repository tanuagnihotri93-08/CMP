<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email:rfc',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $credentials['email'] = mb_strtolower(trim($credentials['email']));

        if (Auth::attempt($credentials + ['is_active' => true])) {
            $request->session()->regenerate();
            $user = $request->user();

            AuditLog::record(
                'login',
                'authentication',
                User::class,
                (int) $user->getKey(),
                'User signed in.'
            );

            return redirect()
                ->route('dashboard')
                ->with('success', 'Signed in successfully.');
        }

        $attemptedEmail = $credentials['email'];
        AuditLog::record(
            'failed_login',
            'authentication',
            User::class,
            User::where('email', $attemptedEmail)->value('id'),
            'Failed login attempt.',
            null,
            ['email' => $attemptedEmail]
        );

        return back()
            ->withErrors(['email' => 'Invalid email or password.'])
            ->with('error', 'Invalid email or password.');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        AuditLog::record(
            'logout',
            'authentication',
            User::class,
            (int) $user->getKey(),
            'User signed out.'
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Signed out successfully.');
    }
}
