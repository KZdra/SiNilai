<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';
    //->with('success', 'Login berhasil! Selamat datang.')

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
    public function username()
    {
        return 'username';
    }

    protected function loggedOut(\Illuminate\Http\Request $request)
    {
        $authMethod = \Illuminate\Support\Facades\Schema::hasTable('settings')
            ? \App\Models\Setting::where('key', 'auth_method')->value('value')
            : 'internal';

        if ($authMethod === 'sso') {
            $ssoServerUrl = \App\Models\Setting::where('key', 'sso_server_url')->value('value');
            $redirectUri = route('login', ['logged_out' => 1]);

            $ssoLogoutUrl = rtrim($ssoServerUrl, '/') . '/sso/logout?redirect_uri=' . urlencode($redirectUri);
            return redirect($ssoLogoutUrl);
        }

        return redirect()->route('login');
    }
}
