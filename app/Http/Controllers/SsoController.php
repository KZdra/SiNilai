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
        return Setting::pluck('value', 'key')->toArray();
    }

    public function redirect(Request $request)
    {
        $settings = $this->getSettings();
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => $settings['sso_client_id'] ?? '',
            'redirect_uri' => $settings['sso_redirect_uri'] ?? '',
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        $serverUrl = $settings['sso_server_url'] ?? '';
        return redirect($serverUrl . '/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $settings = $this->getSettings();
        $state = $request->session()->pull('state');

        if (empty($state) || $state !== $request->state) {
            return redirect('/login')->withErrors(['username' => 'State mismatch. Please try again.']);
        }

        $serverUrl = $settings['sso_server_url'] ?? '';

        $response = Http::asForm()->post($serverUrl . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $settings['sso_client_id'] ?? '',
            'client_secret' => $settings['sso_client_secret'] ?? '',
            'redirect_uri' => $settings['sso_redirect_uri'] ?? '',
            'code' => $request->code,
        ]);

        if ($response->failed()) {
            return redirect('/login')->withErrors(['username' => 'Gagal mendapatkan token dari SSO.']);
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];

        $userResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($serverUrl . '/api/me');

        if ($userResponse->failed()) {
            return redirect('/login')->withErrors(['username' => 'Gagal mendapatkan data user dari SSO.']);
        }

        $ssoUser = $userResponse->json();
  
        // Cari user berdasarkan username. Karena SiNilai login pake username, kita set email ke DB lokal (jika perlu) atau pakai username
        // Pada aplikasi ini login menggunakan field `username`. Kita periksa struktur tabel User nanti.
        // Asumsi kita mencocokkan 'username' atau 'email' di lokal dengan data SSO.
        $user = User::where('username', $ssoUser['username'])->orWhere('email', $ssoUser['email'] ?? 'undefined')->first();
        
        $role_id = 1;
        if ($ssoUser['role'] == 'user') {
            $role_id = 2;
        }
        
        if (!$user) {
            $user = User::create([
                'name' => $ssoUser['fullname'] ?? $ssoUser['username'],
                'username' => $ssoUser['username'],
                'email' => $ssoUser['email'] ?? null,
                'password' => bcrypt(Str::random(24)),
                'role_id' => $role_id, // Default to admin? Atur sesuai kebutuhan
            ]);
        }

        Auth::login($user);

        return redirect('/');
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
