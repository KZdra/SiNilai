# Panduan Load & Stress Testing SiNilai dengan k6

Script pengujian performa dan ketahanan server SiNilai telah disiapkan di berkas `load_test_sinilai.js`.

---

## 1. Instalasi k6

### Di Windows:
Gunakan **winget** atau **choco**:
```powershell
winget install k6 --source winget
```
atau unduh installer resmi dari [k6.io/docs/get-started/installation/](https://k6.io/docs/get-started/installation/).

### Di Linux / Server Ubuntu:
```bash
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6
```

---

## 2. Cara Menjalankan Tes

### A. Uji Cepat (Smoke Test - 5 User, 15 Detik)
Untuk memastikan script terhubung ke target server `192.168.0.17:8002` tanpa error:
```bash
k6 run --vus 5 --duration 15s tests/k6/load_test_sinilai.js
```

### B. Uji Beban Penuh (Load Test Bertahap Sesuai Skenario Asli Sekolah)
Menjalankan simulasi berjenjang (10 $\rightarrow$ 30 $\rightarrow$ 60 virtual user):
```bash
k6 run tests/k6/load_test_sinilai.js
```

### C. Menyesuaikan Target IP / Kredensial Akun
Jika ingin menguji server lain atau mengubah user/password akun uji coba:
```bash
k6 run \
  -e BASE_URL=http://192.168.0.17:8002 \
  -e ADMIN_USER=admin \
  -e ADMIN_PASS=password \
  -e STUDENT_USER=1220821 \
  -e STUDENT_PASS=siswa123 \
  tests/k6/load_test_sinilai.js
```

---

## 3. Metrik yang Diukur
* **`http_req_duration`**: Waktu respon rata-rata dan persentil ke-95 (`p(95)`). Target: $< 2$ detik.
* **`http_req_failed`**: Persentase request yang gagal/error (500, 502, 504). Target: $< 2\%$.
* **`login_success_rate`**: Rasio keberhasilan autentikasi form dengan CSRF token.
* **`dashboard_load_time`**: Kecepatan render dashboard dan query analitik Top 5 Siswa.
* **`datatables_ajax_time`**: Kecepatan respon server-side AJAX tabel Master Siswa saat ribuan data difilter.
