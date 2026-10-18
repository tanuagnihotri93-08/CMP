<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $isActive = $user
            ? (bool) User::query()->whereKey($user->getKey())->value('is_active')
            : true;

        if ($user && ! $isActive) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Your account is inactive. Contact an administrator.');
        }

        return $next($request);
    }
}
