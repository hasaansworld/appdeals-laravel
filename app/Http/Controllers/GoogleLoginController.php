<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        $user = Socialite::driver('google')->user();

        $existingUser = User::where('google_id', $user->id)->first();

        if ($existingUser) {
            Auth::login($existingUser);
        } else {
            $existingEmailUser = User::where('email', $user->getEmail())->first();
            if ($existingEmailUser) {
                $newUser = $existingEmailUser;
            } else {
                $newUser = new User();
            }
            $newUser->name = $user->getName();
            $newUser->email = $user->getEmail();
            $newUser->email_verified_at = Carbon::now();
            $newUser->google_id = $user->getId();
            $newUser->password = bcrypt(request(Str::random())); // Set some random password
            $newUser->profile_picture = $user->getAvatar();
            $newUser->save();

            if (!$existingEmailUser) {
                event(new Registered($user));
            }
            Auth::login($newUser);
        }

        return redirect()->intended(config('app.frontend_url').RouteServiceProvider::NEW_LISTING);
    }
}