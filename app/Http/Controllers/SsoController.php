<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    private function getSettings()
    {
        $dbSettings = Setting::pluck('value', 'key')->toArray();

        return [
            'sso_server_url'    => rtrim($dbSettings['sso_server_url'] ?? env('SSO_SERVER_URL', 'http://192.168.0.18:8001'), '/'),
            'sso_client_id'     => $dbSettings['sso_client_id'] ?? env('SSO_CLIENT_ID', ''),
            'sso_client_secret' => $dbSettings['sso_client_secret'] ?? env('SSO_CLIENT_SECRET', ''),
            'sso_redirect_uri'  => $dbSettings['sso_redirect_uri'] ?? env('SSO_REDIRECT_URI', 'http://192.168.0.18:8002/auth/callback'),
        ];
    }

    public function redirect(Request $request)
    {
        $settings = $this->getSettings();
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id'     => $settings['sso_client_id'],
            'redirect_uri'  => $settings['sso_redirect_uri'],
            'response_type' => 'code',
            'scope'         => '',
            'state'         => $state,
        ]);

        return redirect($settings['sso_server_url'] . '/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $settings = $this->getSettings();
        $state = $request->session()->pull('state');

        // Bypass check jika state di session hilang karena cross-port cookie
        if (!empty($state) && $state !== $request->state) {
            return redirect('/login')->withErrors(['username' => 'State mismatch. Please try again.']);
        }

        $serverUrl = rtrim($settings['sso_server_url'], '/');

        // Tukar Code dengan Access Token
        $response = Http::asForm()->post($serverUrl . '/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => $settings['sso_client_id'],
            'client_secret' => $settings['sso_client_secret'],
            'redirect_uri'  => $settings['sso_redirect_uri'],
            'code'          => $request->code,
        ]);

        if ($response->failed()) {
            return redirect('/login')->withErrors(['username' => 'Gagal mendapatkan token dari SSO: ' . ($response->json('message') ?? $response->body())]);
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];

        // Ambil Data Profil User dari SSO
        $userResponse = Http::withHeaders([
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($serverUrl . '/api/me');

        if ($userResponse->failed()) {
            return redirect('/login')->withErrors(['username' => 'Gagal mendapatkan data user dari SSO.']);
        }

        $ssoUser = $userResponse->json();

        // Sinkronisasi User ke Database SiNilai
        $user = User::where('username', $ssoUser['username'])
            ->orWhere('email', $ssoUser['email'] ?? 'undefined')
            ->first();

        $role_id = ($ssoUser['role'] === 'admin') ? 1 : 2;

        if (!$user) {
            $user = User::create([
                'name'     => $ssoUser['fullname'] ?? $ssoUser['username'],
                'username' => $ssoUser['username'],
                'email'    => $ssoUser['email'] ?? null,
                'password' => bcrypt(Str::random(24)),
                'role_id'  => $role_id,
            ]);
        } else {
            $user->update([
                'name'  => $ssoUser['fullname'] ?? $user->name,
                'email' => $ssoUser['email'] ?? $user->email,
            ]);
        }

        // Login ke SiNilai & Regenerasi Session agar cookie valid
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function slo(Request $request)
    {
        $username = $request->input('username');
        \Illuminate\Support\Facades\Log::info('SLO Hit for username: ' . $username);
        if (!$username) {
            return response()->json(['message' => 'Username required'], 400);
        }

        $user = User::where('username', $username)->first();
        if ($user) {
            $deleted = \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $user->id)->delete();
            
            // Invalidate remember me token so it doesn't auto-login
            $user->update(['remember_token' => null]);
            
            \Illuminate\Support\Facades\Log::info('SLO Processed. Sessions deleted: ' . $deleted);
        } else {
            \Illuminate\Support\Facades\Log::warning('SLO Failed: User not found for username: ' . $username);
        }

        return response()->json(['message' => 'Single Logout processed successfully']);
    }
}
