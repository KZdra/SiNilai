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

    /**
     * Tampilkan halaman pengelolaan aktif/tidaknya modul sistem.
     */
    public function editModules()
    {
        $modules = [
            'p5' => [
                'name' => 'Projek P5 (Profil Pelajar Pancasila)',
                'description' => 'Mengaktifkan modul pembuatan projek profil pelajar pancasila, penilaian dimensi/subelemen, dan cetak rapor P5.',
                'icon' => 'fas fa-shapes text-danger',
                'badge' => 'Kurikulum Merdeka',
                'enabled' => Setting::isModuleEnabled('p5', true),
            ],
            'formatif' => [
                'name' => 'Asesmen Formatif (KKTP)',
                'description' => 'Mengaktifkan modul pencatatan capaian formatif per tujuan pembelajaran dan deskripsi kompetensi otomatis.',
                'icon' => 'fas fa-tasks text-info',
                'badge' => 'Akademik',
                'enabled' => Setting::isModuleEnabled('formatif', true),
            ],
            'eskul' => [
                'name' => 'Penilaian Ekstrakurikuler',
                'description' => 'Mengaktifkan modul rekapitulasi keikutsertaan dan penilaian predikat ekstrakurikuler siswa.',
                'icon' => 'fas fa-award text-warning',
                'badge' => 'Non-Akademik',
                'enabled' => Setting::isModuleEnabled('eskul', true),
            ],
            'cbt_sync' => [
                'name' => 'Integrasi CBT Sync Engine',
                'description' => 'Mengaktifkan tombol tarik nilai CBT di form input nilai serta API push penerima nilai ujian CBT.',
                'icon' => 'fas fa-sync-alt text-primary',
                'badge' => 'Integrasi API',
                'enabled' => Setting::isModuleEnabled('cbt_sync', true),
            ],
            'portal_siswa' => [
                'name' => 'Portal Mandiri Siswa & Orang Tua',
                'description' => 'Mengizinkan siswa dan wali murid login mandiri untuk melihat rekapitulasi nilai dan catatan kenaikan kelas.',
                'icon' => 'fas fa-user-graduate text-success',
                'badge' => 'Akses Publik',
                'enabled' => Setting::isModuleEnabled('portal_siswa', true),
            ],
        ];

        return view('settings.modules', compact('modules'));
    }

    /**
     * Simpan status aktif/tidaknya modul sistem.
     */
    public function updateModules(Request $request)
    {
        $moduleKeys = ['p5', 'formatif', 'eskul', 'cbt_sync', 'portal_siswa'];

        foreach ($moduleKeys as $key) {
            $isEnabled = $request->has("module_{$key}") ? '1' : '0';
            Setting::updateOrCreate(
                ['key' => "module_{$key}"],
                ['value' => $isEnabled]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan status modul sistem berhasil disimpan.');
    }
}
