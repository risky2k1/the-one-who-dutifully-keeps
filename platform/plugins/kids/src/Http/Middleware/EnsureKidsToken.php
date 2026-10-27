<?php

namespace Botble\Kids\Http\Middleware;

use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsParent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKidsToken
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => true, 'message' => 'Unauthenticated.', 'data' => null], 401);
        }

        $token = $user->currentAccessToken();
        if (! $token) {
            return response()->json(['error' => true, 'message' => 'Unauthenticated.', 'data' => null], 401);
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json(['error' => true, 'message' => 'Token expired.', 'data' => null], 401);
        }

        $valid = match ($role) {
            'parent' => ($user instanceof KidsParent) && $token->can('parent'),
            'device' => ($user instanceof KidsParent) && $token->can('device'),
            'kid' => ($user instanceof KidsChild) && $token->can('kid'),
            default => false,
        };

        if (! $valid) {
            return response()->json(['error' => true, 'message' => 'Forbidden: Invalid token role or ability.', 'data' => null], 403);
        }

        return $next($request);
    }
}
