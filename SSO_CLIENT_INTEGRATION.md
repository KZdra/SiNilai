# Panduan Integrasi SSO (Single Sign-On) untuk Aplikasi Klien

Dokumen ini menjelaskan langkah demi langkah bagaimana aplikasi klien (khususnya berbasis Laravel) dapat berintegrasi dengan **SSO Server** ini menggunakan protokol **OAuth2 Authorization Code Flow**.

---

## 1. Mendaftarkan Aplikasi Klien di SSO Server

Sebelum aplikasi klien dapat menggunakan SSO, Anda harus mendaftarkannya terlebih dahulu melalui Dashboard SSO Server.

1. Login sebagai **Admin** di SSO Server.
2. Navigasi ke menu **Client Applications**.
3. Klik tombol **Add New Client**.
4. Isi data berikut:
   - **application_name**: Nama aplikasi klien (contoh: `Aplikasi HRD`).
   - **redirect_uri**: URL callback di aplikasi klien Anda yang akan memproses kode otorisasi setelah berhasil login (contoh: `http://localhost:8001/auth/callback`).
   - **status**: Pilih `Active`.
5. Setelah disimpan, Anda akan mendapatkan `client_id` dan `client_secret`.
6. Simpan kedua kredensial tersebut dengan aman, karena akan digunakan di aplikasi klien.

---

## 2. Persiapan di Aplikasi Klien (Client App)

Pada aplikasi klien Laravel Anda, tambahkan environment variable dari kredensial yang didapat pada file `.env`:

```env
SSO_CLIENT_ID=id_dari_sso
SSO_CLIENT_SECRET=secret_dari_sso
SSO_REDIRECT_URI=http://localhost:8001/auth/callback
SSO_SERVER_URL=http://localhost:8000
```

*Catatan: Sesuaikan URL port SSO Server (`SSO_SERVER_URL`) dan port aplikasi klien Anda.*

---

## 3. Implementasi Alur Login SSO di Klien

Proses login melibatkan dua langkah utama:
1. Mengarahkan pengguna (redirect) ke SSO Server untuk otentikasi.
2. Menangani URL Callback untuk menukar kode otorisasi (*authorization code*) dengan token akses (*access token*), lalu mengambil data pengguna.

### Route Klien (`routes/web.php`)

Buat dua route untuk menangani proses SSO:

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SsoController;

Route::get('/auth/redirect', [SsoController::class, 'redirect'])->name('sso.login');
Route::get('/auth/callback', [SsoController::class, 'callback'])->name('sso.callback');
```

### Controller Klien (`SsoController.php`)

Buat controller untuk menangani logika HTTP request (Anda dapat menggunakan `Illuminate\Support\Facades\Http`).

```bash
php artisan make:controller SsoController
```

Isi `SsoController.php` dengan kode berikut:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    /**
     * Langkah 1: Redirect user ke SSO Server.
     */
    public function redirect(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => env('SSO_CLIENT_ID'),
            'redirect_uri' => env('SSO_REDIRECT_URI'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return redirect(env('SSO_SERVER_URL') . '/oauth/authorize?' . $query);
    }

    /**
     * Langkah 2: Tangani callback dari SSO, tukar kode dengan token, lalu login.
     */
    public function callback(Request $request)
    {
        $state = $request->session()->pull('state');

        // Validasi state untuk mencegah serangan CSRF
        if (empty($state) || $state !== $request->state) {
            return redirect('/login')->withErrors(['msg' => 'State mismatch. Please try again.']);
        }

        // 1. Minta Access Token menggunakan Authorization Code
        $response = Http::asForm()->post(env('SSO_SERVER_URL') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('SSO_CLIENT_ID'),
            'client_secret' => env('SSO_CLIENT_SECRET'),
            'redirect_uri' => env('SSO_REDIRECT_URI'),
            'code' => $request->code,
        ]);

        if ($response->failed()) {
            return redirect('/login')->withErrors(['msg' => 'Gagal mendapatkan token dari SSO.']);
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];

        // 2. Ambil data profil user dari SSO Server
        $userResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get(env('SSO_SERVER_URL') . '/api/me');

        if ($userResponse->failed()) {
            return redirect('/login')->withErrors(['msg' => 'Gagal mendapatkan data user dari SSO.']);
        }

        $ssoUser = $userResponse->json();

        // 3. Login otomatis user di database lokal (Klien)
        // Cari user berdasarkan email. Jika tidak ada, buat akun lokal baru.
        $user = User::updateOrCreate(
            ['email' => $ssoUser['email']],
            [
                'name' => $ssoUser['fullname'] ?? $ssoUser['username'], // Asumsi DB lokal memiliki field 'name'
                'password' => bcrypt(Str::random(24)), // User tidak akan pakai password lokal
                // Anda juga bisa sinkronisasi avatar atau role di sini
            ]
        );

        // Login ke aplikasi klien
        Auth::login($user);

        return redirect('/dashboard');
    }
}
```

---

## 4. Menggunakan Laravel Socialite (Alternatif Lebih Rapi)

Jika Anda ingin implementasi klien yang lebih elegan, Anda bisa menginstal **Laravel Socialite** di aplikasi klien Anda dan membuat custom provider (seperti package `socialiteproviders/laravelpassport`). Namun, metode manual menggunakan `Http` facade seperti di atas adalah yang paling transparan dan bebas dari package pihak ketiga.

---

## Selesai!
Sekarang ketika pengguna mengakses `http://localhost:8001/auth/redirect` di aplikasi klien Anda, mereka akan dilempar secara aman ke SSO Server untuk verifikasi identitas, dan kembali masuk (*logged in*) secara otomatis di aplikasi klien.
