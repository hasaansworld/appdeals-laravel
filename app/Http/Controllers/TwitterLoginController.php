<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class TwitterLoginController extends Controller
{
    /**
     * Redirect to Twitter
     *
     * @return RedirectResponse
     */
    public function redirectToTwitter(): RedirectResponse
    {
        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Handle Twitter authentication callback
     *
     * @return RedirectResponse
     */
    public function handleTwitterCallback(): RedirectResponse
    {
        $user = Socialite::driver('twitter')->user();
    
        $existingUser = User::where('twitter_id', $user->id)->first();

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
            $newUser->twitter_id = $user->getId();
            $newUser->password = bcrypt(request(Str::random())); // Set some random password
            $newUser->profile_picture = $user->getAvatar();
            $newUser->save();

            if (!$existingEmailUser) {
                event(new Registered($user));
            }
            Auth::login($newUser, true);
        }

        return redirect()->intended(config('app.frontend_url').RouteServiceProvider::NEW_LISTING);
    }
}