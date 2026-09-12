<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Raport Resmi | {{ $school->nama_sekolah ?? 'SiNilai' }}</title>
    <link rel="shortcut icon" href="{{ asset('images/icb.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=fallback" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #102C57;
            --primary-dark: #0B192C;
            --success: #16A34A;
            --bg: #F8FAFC;
            --card-border: #E2E8F0;
            --text-main: #1E293B;
            --text-muted: #64748B;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #EEF2F6 0%, #E2E8F0 100%);
            min-height: 100vh;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
        }

        .container {
            width: 100%;
            max-width: 620px;
            margin: auto;
        }

        .card {
            background: #FFFFFF;
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(16, 44, 87, 0.15);
            border: 1px solid var(--card-border);
            overflow: hidden;
            position: relative;
        }

        .card-header {
            background: linear-gradient(135deg, #102C57 0%, #164863 100%);
            color: #FFFFFF;
            padding: 28px 24px 24px;
            text-align: center;
            position: relative;
        }

        .school-name {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }

        .school-address {
            font-size: 0.8rem;
            opacity: 0.85;
            font-weight: 400;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-top: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .badge-valid {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #86EFAC;
        }

        .badge-invalid {
            background: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FCA5A5;
        }

        .card-body {
            padding: 28px 24px;
        }

        .section-title {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .info-item {
            background: #F8FAFC;
            border: 1px solid #EDF2F7;
            padding: 12px 16px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .info-label {
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .info-val {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-main);
            text-align: right;
        }

        .attendance-pills {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 24px;
        }

        .pill {
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 10px;
            text-align: center;
        }

        .pill-num {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary);
        }

        .pill-title {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
        }

        .notes-box {
            background: #FEF3C7;
            border: 1px solid #FDE68A;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .notes-box p {
            font-size: 0.85rem;
            color: #92400E;
            line-height: 1.5;
            font-style: italic;
        }

        .sign-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            border-top: 1px dashed var(--card-border);
            padding-top: 20px;
        }

        .sign-box {
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .sign-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-main);
            margin-top: 36px;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .watermark {
            position: absolute;
            bottom: 10px;
            right: 15px;
            opacity: 0.04;
            font-size: 6rem;
            pointer-events: none;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="watermark">
                <i class="fa-solid fa-certificate"></i>
            </div>

            <div class="card-header">
                <h1 class="school-name">{{ $school->nama_sekolah ?? 'SiNilai - Kurikulum Merdeka' }}</h1>
                <p class="school-address">{{ $school->alamat_sekolah ?? 'Sistem Informasi Penilaian & Raport Resmi' }}</p>

                @if($valid)
                    <div class="badge-status badge-valid">
                        <i class="fa-solid fa-shield-check"></i>
                        DOKUMEN RAPORT SAH & TERVERIFIKASI
                    </div>
                @else
                    <div class="badge-status badge-invalid">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        DOKUMEN TIDAK TERDAFTAR / INVALID
                    </div>
                @endif
            </div>

            <div class="card-body">
                @if($valid)
                    <div class="section-title">
                        <i class="fa-solid fa-id-card"></i> Identitas Siswa
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Nama Lengkap</span>
                            <span class="info-val">{{ $record->student_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">NIS / NISN</span>
                            <span class="info-val">{{ $record->nis }} / {{ $record->nisn ?: '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Rombongan Belajar (Kelas)</span>
                            <span class="info-val">{{ $record->class_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fase / Semester</span>
                            <span class="info-val">{{ ucwords($record->fase) }} / Semester {{ $record->semester }} ({{ $record->tahun_ajaran }})</span>
                        </div>
                        @if($record->status_kenaikan)
                        <div class="info-item" style="background: #F0FDF4; border-color: #BBF7D0;">
                            <span class="info-label" style="color: #15803D;">Keputusan Kenaikan/Kelulusan</span>
                            <span class="info-val" style="color: #166534;">{{ $record->status_kenaikan }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="section-title">
                        <i class="fa-solid fa-calendar-check"></i> Rekap Presensi Semester
                    </div>
                    <div class="attendance-pills">
                        <div class="pill">
                            <div class="pill-num">{{ $record->sakit }}</div>
                            <div class="pill-title">Sakit (Hari)</div>
                        </div>
                        <div class="pill">
                            <div class="pill-num">{{ $record->izin }}</div>
                            <div class="pill-title">Izin (Hari)</div>
                        </div>
                        <div class="pill">
                            <div class="pill-num">{{ $record->alpa }}</div>
                            <div class="pill-title">Tanpa Ket (Hari)</div>
                        </div>
                    </div>

                    @if($record->catatan)
                    <div class="section-title">
                        <i class="fa-solid fa-comment-dots"></i> Catatan Perkembangan Siswa
                    </div>
                    <div class="notes-box">
                        <p>"{{ $record->catatan }}"</p>
                    </div>
                    @endif

                    <div class="sign-grid">
                        <div class="sign-box">
                            <span>Wali Kelas</span>
                            <div class="sign-name">{{ $waliKelas }}</div>
                        </div>
                        <div class="sign-box">
                            <span>Kepala Sekolah</span>
                            <div class="sign-name">{{ $school->nama_kepala_sekolah ?? 'Kepala Sekolah' }}</div>
                        </div>
                    </div>
                @else
                    <div style="text-align: center; padding: 30px 10px;">
                        <i class="fa-solid fa-file-circle-xmark" style="font-size: 3.5rem; color: #EF4444; margin-bottom: 16px;"></i>
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: #991B1B; margin-bottom: 8px;">Token Raport Tidak Ditemukan</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">
                            Kode verifikasi tidak cocok dengan basis data sekolah resmi kami. Pastikan Anda memindai QR Code asli dari lembar fisik raport resmi.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="footer-note">
            Sistem Informasi Penilaian & Raport (SiNilai) &copy; {{ date('Y') }} &bull; Dokumen sah dan dilindungi secara digital.
        </div>
    </div>
</body>
</html>
