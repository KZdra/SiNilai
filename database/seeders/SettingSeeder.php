<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'auth_method', 'value' => 'internal'],
            ['key' => 'sso_client_id', 'value' => ''],
            ['key' => 'sso_client_secret', 'value' => ''],
            ['key' => 'sso_redirect_uri', 'value' => 'http://localhost:8000/auth/callback'],
            ['key' => 'sso_server_url', 'value' => 'http://localhost:8001'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
