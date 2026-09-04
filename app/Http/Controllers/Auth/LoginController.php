<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

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

    public function logout(Request $request)
    {
        // 1. Hapus sesi lokal aplikasi SiNilai
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 2. Arahkan ke SSO Server untuk membersihkan sesi SSO juga (Single Logout / SLO)
        $authMethod = Schema::hasTable('settings')
            ? Setting::where('key', 'auth_method')->value('value')
            : 'internal';

        $ssoServerUrl = Schema::hasTable('settings')
            ? Setting::where('key', 'sso_server_url')->value('value')
            : env('SSO_SERVER_URL', 'http://127.0.0.1:8001');

        $loginUrl = url('/login'); // URL tujuan setelah logout SSO selesai

        // Jika mode SSO aktif dan server URL terdefinisi, arahkan ke endpoint SLO SSO Server
        if ($authMethod === 'sso' && !empty($ssoServerUrl)) {
            return redirect(rtrim($ssoServerUrl, '/') . '/sso/logout?redirect_uri=' . urlencode($loginUrl));
        }

        return redirect()->route('login')->with('info', 'Anda telah berhasil logout.');
    }
}
