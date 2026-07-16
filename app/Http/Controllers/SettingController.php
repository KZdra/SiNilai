<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function editAuth()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('settings.auth', compact('settings'));
    }

    public function updateAuth(Request $request)
    {
        $request->validate([
            'auth_method' => 'required|in:internal,sso',
            'sso_client_id' => 'nullable|string',
            'sso_client_secret' => 'nullable|string',
            'sso_redirect_uri' => 'nullable|url',
            'sso_server_url' => 'nullable|url',
        ]);

        $keys = ['auth_method', 'sso_client_id', 'sso_client_secret', 'sso_redirect_uri', 'sso_server_url'];

        foreach ($keys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->$key]);
        }

        return redirect()->back()->with('success', 'Pengaturan otentikasi berhasil diperbarui.');
    }
}
