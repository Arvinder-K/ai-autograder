<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const PROVIDER_DRIVER_MAP = [
        'microsoft' => 'azure',
        'google' => 'google',
    ];

    public function redirect(string $provider)
    {
        $driver = self::PROVIDER_DRIVER_MAP[$provider] ?? $provider;
        return Socialite::driver($driver)->redirect();
    }

    public function callback(string $provider, Request $request)
    {
        $driver = self::PROVIDER_DRIVER_MAP[$provider] ?? $provider;

        try {
            $socialUser = Socialite::driver($driver)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Authentication failed. Please try again.');
        }

        // Find by provider + provider_id
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        // Fallback: match by email
        if (!$user) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Link SSO to existing account
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'provider_token' => $socialUser->token,
                    'provider_refresh_token' => $socialUser->refreshToken,
                    'avatar' => $socialUser->getAvatar(),
                ]);
            }
        }

        // Create new user
        if (!$user) {
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken,
                'avatar' => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ]);

            // Assign default role
            $defaultRole = Role::where('slug', 'business_user')->first();
            if ($defaultRole) {
                $user->roles()->attach($defaultRole->id);
            }
        }

        // Block inactive users
        if (!$user->is_active) {
            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Contact an administrator.');
        }

        // Update tokens and login metadata
        $user->update([
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
            'avatar' => $socialUser->getAvatar() ?: $user->avatar,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        Auth::login($user, true);

        AuditService::log('login', 'user', $user->id, "User logged in via {$provider}", request: $request);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        AuditService::log('logout', 'user', Auth::id(), 'User logged out');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
