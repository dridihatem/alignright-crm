<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;
use App\Models\User;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/drive.file', 'https://www.googleapis.com/auth/userinfo.email'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::find(auth()->user()->id);

        $user->google_access_token = $googleUser->token;
        $user->google_refresh_token = $googleUser->refreshToken;
        $user->google_token_expires_at = Carbon::now()->addSeconds($googleUser->expiresIn);
        $user->save();
        toastr()->success(__('master.google_drive_connected'));
        return redirect()->route('doctor.dashboard');
    }
}