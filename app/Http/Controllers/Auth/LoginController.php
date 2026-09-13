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

    /**
     * Override attemptLogin untuk mendukung:
     * 1. Login standar (username)
     * 2. Login via Email
     * 3. Login Portal Siswa via NISN ataupun NIS (dengan auto-link ke akun user)
     */
    protected function attemptLogin(Request $request)
    {
        $loginValue = trim((string) $request->input($this->username()));
        $password   = (string) $request->input('password');
        $remember   = $request->boolean('remember');

        // 1. Coba login standar berdasarkan field 'username'
        if ($this->guard()->attempt([$this->username() => $loginValue, 'password' => $password], $remember)) {
            return true;
        }

        // 2. Jika input berupa email, coba autentikasi dengan email
        if (filter_var($loginValue, FILTER_VALIDATE_EMAIL)) {
            if ($this->guard()->attempt(['email' => $loginValue, 'password' => $password], $remember)) {
                return true;
            }
        }

        // 3. Pencarian Akun Portal Siswa: Bisa menggunakan NISN ataupun NIS
        $student = \Illuminate\Support\Facades\DB::table('students')
            ->where('nis', $loginValue)
            ->orWhere('nisn', $loginValue)
            ->first();

        if ($student) {
            $user = \App\Models\User::where('student_id', $student->id)
                ->orWhere(function ($q) use ($student) {
                    if (!empty($student->nisn)) {
                        $q->orWhere('username', $student->nisn);
                    }
                    if (!empty($student->nis)) {
                        $q->orWhere('username', $student->nis);
                    }
                })
                ->first();

            // Jika user siswa ditemukan, cek password
            if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                // Pastikan student_id terhubung jika sebelumnya null
                if (!$user->student_id) {
                    $user->student_id = $student->id;
                    $user->save();
                }
                $this->guard()->login($user, $remember);
                return true;
            }

            // Jika user belum pernah di-generate, izinkan login perdana dengan default password 'siswa123'
            if (!$user && $password === 'siswa123') {
                $defaultUsername = !empty($student->nisn) ? $student->nisn : $student->nis;
                $user = \App\Models\User::create([
                    'name'       => $student->nama,
                    'username'   => $defaultUsername,
                    'email'      => strtolower(str_replace(' ', '', $defaultUsername)) . '@siswa.sekolah.id',
                    'password'   => \Illuminate\Support\Facades\Hash::make('siswa123'),
                    'role_id'    => 3, // Role Siswa
                    'class_id'   => $student->class_id,
                    'student_id' => $student->id,
                ]);

                $this->guard()->login($user, $remember);
                return true;
            }
        }

        return false;
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->role_id == 3) {
            return redirect()->route('portal.dashboard');
        }
        return redirect()->intended($this->redirectPath());
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
