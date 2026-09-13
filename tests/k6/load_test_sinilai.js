import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';

/**
 * ============================================================================
 * SINILAI LOAD & STRESS TESTING SUITE (k6)
 * ============================================================================
 * Target Default: http://192.168.0.17:8002
 *
 * Cara Menjalankan:
 * 1. Default (192.168.0.17:8002):
 *    k6 run tests/k6/load_test_sinilai.js
 *
 * 2. Override URL / Akun via Environment Variable:
 *    k6 run -e BASE_URL=http://192.168.0.17:8002 -e ADMIN_USER=admin -e ADMIN_PASS=password tests/k6/load_test_sinilai.js
 *
 * 3. Quick Smoke Test (5 user selama 10 detik):
 *    k6 run --vus 5 --duration 10s tests/k6/load_test_sinilai.js
 * ============================================================================
 */

// Custom Metrics
const loginSuccessRate = new Rate('login_success_rate');
const dashboardDuration = new Trend('dashboard_load_time');
const portalDuration = new Trend('portal_load_time');
const dataTablesDuration = new Trend('datatables_ajax_time');

// Konfigurasi Target & Akun
const BASE_URL = __ENV.BASE_URL || 'http://192.168.0.17:8002';
const ADMIN_USER = __ENV.ADMIN_USER || 'admin';
const ADMIN_PASS = __ENV.ADMIN_PASS || 'password';
const STUDENT_USER = __ENV.STUDENT_USER || '1220821';
const STUDENT_PASS = __ENV.STUDENT_PASS || 'siswa123';

// Konfigurasi Beban Pengujian (Ramping Stages)
export const options = {
    stages: [
        { duration: '30s', target: 10 },  // Ramp-up ke 10 Virtual Users (pemanasan)
        { duration: '1m',  target: 30 },  // Beban normal: 30 pengguna aktif bersamaan
        { duration: '30s', target: 60 },  // Uji lonjakan (Spike / Stress): 60 pengguna aktif
        { duration: '30s', target: 0 },   // Cooldown kembali ke 0
    ],
    thresholds: {
        // 95% request harus direspon di bawah 2 detik
        http_req_duration: ['p(95)<2000'],
        // Tingkat kegagalan HTTP request harus kurang dari 2%
        http_req_failed: ['rate<0.02'],
        // Tingkat keberhasilan login minimal 95%
        login_success_rate: ['rate>0.95'],
    },
};

/**
 * Helper untuk mengambil CSRF Token dari form HTML Laravel
 */
function extractCsrfToken(htmlBody) {
    if (!htmlBody) return null;
    const metaMatch = htmlBody.match(/<meta\s+name="csrf-token"\s+content="([^"]+)"/);
    if (metaMatch && metaMatch[1]) {
        return metaMatch[1];
    }
    const match = htmlBody.match(/name="_token"\s+value="([^"]+)"/);
    if (match && match[1]) {
        return match[1];
    }
    return null;
}

export default function () {
    // ------------------------------------------------------------------------
    // SCENARIO 1: Guest / Halaman Login & Validasi Publik
    // ------------------------------------------------------------------------
    group('01. Public & Login Page', function () {
        const resLogin = http.get(`${BASE_URL}/login`);
        check(resLogin, {
            'Halaman login HTTP 200': (r) => r.status === 200,
            'Halaman login berisi form': (r) => r.body.includes('name="username"'),
        });
        sleep(1);
    });

    // ------------------------------------------------------------------------
    // SCENARIO 2: Alur Siswa (Portal Transkrip Nilai Mandiri)
    // ------------------------------------------------------------------------
    group('02. Student Flow (Portal Siswa)', function () {
        // 1. Ambil halaman login dan ekstrak token CSRF
        const resLogin = http.get(`${BASE_URL}/login`);
        const token = extractCsrfToken(resLogin.body);

        if (token) {
            // 2. Kirim POST login akun siswa
            const payload = {
                _token: token,
                username: STUDENT_USER,
                password: STUDENT_PASS,
            };
            const postLogin = http.post(`${BASE_URL}/login`, payload, {
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                redirects: 2,
            });

            const isStudentLoggedIn = check(postLogin, {
                'Login Siswa berhasil (200 / Redirect)': (r) => r.status === 200 || r.status === 302,
            });
            loginSuccessRate.add(isStudentLoggedIn);

            // 3. Akses Halaman Transkrip Nilai Siswa
            const startPortal = Date.now();
            const resPortal = http.get(`${BASE_URL}/portal/nilai`);
            portalDuration.add(Date.now() - startPortal);

            check(resPortal, {
                'Portal Nilai HTTP 200': (r) => r.status === 200,
            });

            // 4. Logout dengan token sesi terbaru
            const logoutToken = extractCsrfToken(resPortal.body) || token;
            http.post(`${BASE_URL}/logout`, { _token: logoutToken }, {
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                redirects: 1,
            });
            http.cookieJar().clear(BASE_URL);
        }
        sleep(1.5);
    });

    // ------------------------------------------------------------------------
    // SCENARIO 3: Alur Guru / Admin (Dashboard, Master Siswa, & Nilai Akhir)
    // ------------------------------------------------------------------------
    group('03. Admin/Guru Flow (Dashboard & Akademik)', function () {
        // 1. Ambil halaman login dan ekstrak token CSRF
        const resLogin = http.get(`${BASE_URL}/login`);
        const token = extractCsrfToken(resLogin.body);

        if (token) {
            // 2. Login sebagai Admin
            const payload = {
                _token: token,
                username: ADMIN_USER,
                password: ADMIN_PASS,
            };
            const postLogin = http.post(`${BASE_URL}/login`, payload, {
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                redirects: 2,
            });

            const isAdminLoggedIn = check(postLogin, {
                'Login Admin berhasil (200 / Redirect)': (r) => r.status === 200 || r.status === 302,
            });
            loginSuccessRate.add(isAdminLoggedIn);

            // 3. Akses Halaman Home / Dashboard
            const startDash = Date.now();
            const resHome = http.get(`${BASE_URL}/`);
            dashboardDuration.add(Date.now() - startDash);

            check(resHome, {
                'Dashboard HTTP 200': (r) => r.status === 200,
                'Dashboard memuat statistik': (r) => r.body.includes('Top 5 Siswa') || r.body.includes('Dashboard') || r.body.includes('SiNilai'),
            });

            // 4. Test AJAX Server-Side Datatables Master Siswa (Simulasi pagination & filter kelas)
            const startDt = Date.now();
            const resDtSiswa = http.get(`${BASE_URL}/siswa/data?draw=1&start=0&length=10&class_name=X%20RPL`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            dataTablesDuration.add(Date.now() - startDt);

            check(resDtSiswa, {
                'DataTables Siswa HTTP 200': (r) => r.status === 200,
                'DataTables respon JSON valid': (r) => {
                    try {
                        const json = JSON.parse(r.body);
                        return json && Array.isArray(json.data);
                    } catch (e) {
                        return false;
                    }
                },
            });

            // 5. Test Akses Nilai Akhir AJAX
            const resNilaiAkhir = http.get(`${BASE_URL}/akhir/getAVG?class_id=1&fst_id=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            check(resNilaiAkhir, {
                'Nilai Akhir Rerata HTTP 200': (r) => r.status === 200,
            });

            // 6. Logout dengan token sesi terbaru
            const logoutToken = extractCsrfToken(resHome.body) || token;
            http.post(`${BASE_URL}/logout`, { _token: logoutToken }, {
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                redirects: 1,
            });
            http.cookieJar().clear(BASE_URL);
        }
        sleep(2);
    });
}
