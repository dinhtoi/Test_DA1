<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Services\SocialAccountService;
use Illuminate\Support\Facades\Log;
use Socialite;
use Auth;
use App\User;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }
 
    /**
     * Obtain the user information from facebook.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->fields(['name','first_name', 'last_name', 'email', 'gender', 'friends','birthday','location','accounts'])->scopes(['user_friends','manage_pages','user_age_range','user_gender'])->user();  

        $id = $user['accounts']['data'][0]['id'];
        $token = $user['accounts']['data'][0]['access_token'];
        $user2 = Socialite::driver($provider)->fields(['$id/feed','$token'])->scopes(['manage_pages'])->user();  
     
        print_r($user2);
        die();
        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);
        return redirect($this->redirectTo);
    }
 
    public function findOrCreateUser($user, $provider)
    {
        $authUser = User::where('provider_id', $user->id)->first();
        if ($authUser) {
            return $authUser;
        }
        return User::create([
            'name'     => $user->name,
            'email'    => $user->email,
            'provider' => $provider,
            'provider_id' => $user->id
        ]);
    }

}