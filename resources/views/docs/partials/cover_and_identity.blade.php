@php
    $tutwuriPath = public_path('images/tutwuri.png');
    $tutwuriBase64 = file_exists($tutwuriPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tutwuriPath)) : '';

    if (!function_exists('formatIndoDateRaport')) {
        function formatIndoDateRaport($date) {
            if (!$date) return '-';
            try {
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $carbon = \Carbon\Carbon::parse($date);
                return $carbon->day . ' ' . ($months[$carbon->month] ?? $carbon->format('F')) . ' ' . $carbon->year;
            } catch (\Exception $e) {
                return $date;
            }
        }
    }

    $kotaPrint = 'Bandung';
    if (!empty($schoolData->kabupaten_kota)) {
        $kotaPrint = preg_replace('/^(KOTA|KABUPATEN|Kota|Kabupaten)\s+/i', '', trim($schoolData->kabupaten_kota));
    }
@endphp

<style>
    .page-wrapper {
        width: 100%;
        page-break-inside: avoid;
    }
    .page-break {
        page-break-after: always;
        clear: both;
    }
    .cover-box {
        text-align: center;
        padding-top: 10px;
    }
    .cover-logo {
        margin-bottom: 45px;
    }
    .cover-logo img {
        width: 110px;
        height: auto;
    }
    .cover-titles {
        margin-bottom: 70px;
        line-height: 1.4;
    }
    .cover-student {
        margin-bottom: 80px;
    }
    .name-box-rect {
        border: 2px solid #000;
        padding: 6px 12px;
        width: 78%;
        margin: 5px auto 20px auto;
        text-align: center;
        font-size: 12.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .nis-box-rect {
        border: 2px solid #000;
        padding: 5px 12px;
        width: 78%;
        margin: 5px auto 0 auto;
        text-align: center;
        font-size: 11.5pt;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .cover-foot {
        line-height: 1.4;
    }

    .school-wrapper {
        padding-top: 15px;
    }
    .school-titles {
        text-align: center;
        line-height: 1.35;
        margin-bottom: 35px;
    }

    .student-wrapper {
        padding-top: 0px;
    }
    .student-title-heading {
        text-align: center;
        font-size: 11.5pt;
        font-weight: bold;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .compact-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
    }
    .compact-table td {
        border: none;
        padding: 1.5px 0;
        vertical-align: top;
    }

    .photo-frame {
        width: 2.7cm;
        height: 3.6cm;
        border: 1px solid #000;
        margin: 0 auto;
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        line-height: 3.6cm;
        font-size: 7.5pt;
        color: #999;
    }
</style>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- HALAMAN 1: COVER DEPAN RAPOR                                              -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="page-wrapper cover-box">
    <div class="cover-logo">
        @if (!empty($tutwuriBase64))
            <img src="{{ $tutwuriBase64 }}" alt="Logo Tut Wuri Handayani">
        @endif
    </div>

    <div class="cover-titles">
        <div style="letter-spacing: 6px; font-size: 15pt; font-weight: bold; margin-bottom: 6px;">R A P O R</div>
        <div style="font-size: 14pt; font-weight: bold; margin-bottom: 5px; letter-spacing: 0.5px;">PESERTA DIDIK</div>
        <div style="font-size: 13pt; font-weight: bold; margin-bottom: 5px; letter-spacing: 0.5px;">SEKOLAH MENENGAH KEJURUAN</div>
        <div style="font-size: 13pt; font-weight: bold;">(SMK)</div>
    </div>

    <div class="cover-student">
        <div style="font-size: 10.5pt; margin-bottom: 2px;">Nama Peserta Didik :</div>
        <div class="name-box-rect">
            {{ strtoupper($student->nama ?? '-') }}
        </div>

        <div style="font-size: 10.5pt; margin-bottom: 2px;">NIS / NISN</div>
        <div class="nis-box-rect">
            {{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}
        </div>
    </div>

    <div class="cover-foot">
        <div style="font-weight: bold; font-size: 10.5pt; letter-spacing: 0.5px;">KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN</div>
        <div style="font-weight: bold; font-size: 10.5pt; letter-spacing: 0.5px; margin-top: 2px;">REPUBLIK INDONESIA</div>
    </div>
</div>

<div class="page-break"></div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- HALAMAN 2: IDENTITAS SEKOLAH                                              -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="page-wrapper school-wrapper">
    <div class="school-titles">
        <div style="letter-spacing: 6px; font-size: 14.5pt; font-weight: bold; margin-bottom: 6px;">R A P O R</div>
        <div style="font-size: 13.5pt; font-weight: bold; margin-bottom: 5px; letter-spacing: 0.5px;">PESERTA DIDIK</div>
        <div style="font-size: 12.5pt; font-weight: bold;">SEKOLAH MENENGAH KEJURUAN(SMK)</div>
    </div>

    <div style="margin-left: 20px; margin-right: 20px;">
        <table class="compact-table" style="font-size: 10.5pt; line-height: 1.85;">
            <tr>
                <td style="width: 200px;">Nama Sekolah</td>
                <td style="width: 20px;">:</td>
                <td style="font-weight: bold;">{{ $schoolData->nama_sekolah ?? 'SMK ICB CINTA TEKNIKA' }}</td>
            </tr>
            <tr>
                <td>NPSN</td>
                <td>:</td>
                <td>{{ $schoolData->npsn ?? '-' }}</td>
            </tr>
            <tr>
                <td>Alamat Sekolah</td>
                <td>:</td>
                <td>{{ $schoolData->alamat_sekolah ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kode Pos</td>
                <td>:</td>
                <td>{{ $schoolData->kode_pos ?? '-' }}</td>
            </tr>
            <tr>
                <td>Desa / Kelurahan</td>
                <td>:</td>
                <td>{{ $schoolData->desa_kelurahan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kecamatan</td>
                <td>:</td>
                <td>{{ $schoolData->kecamatan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kabupaten / Kota</td>
                <td>:</td>
                <td>{{ $schoolData->kabupaten_kota ?? '-' }}</td>
            </tr>
            <tr>
                <td>Provinsi</td>
                <td>:</td>
                <td>{{ $schoolData->provinsi ?? '-' }}</td>
            </tr>
            <tr>
                <td>Website</td>
                <td>:</td>
                <td>{{ $schoolData->website ?? '-' }}</td>
            </tr>
            <tr>
                <td>E-mail</td>
                <td>:</td>
                <td>{{ $schoolData->email ?? '-' }}</td>
            </tr>
        </table>
    </div>
</div>

<div class="page-break"></div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- HALAMAN 3: IDENTITAS PESERTA DIDIK                                        -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="page-wrapper student-wrapper">
    <div class="student-title-heading">
        IDENTITAS PESERTA DIDIK
    </div>

    <table class="compact-table" style="font-size: 9.5pt; line-height: 1.3;">
        <tr>
            <td style="width: 200px;">Nama Peserta Didik</td>
            <td style="width: 15px;">:</td>
            <td style="font-weight: bold; text-transform: uppercase;">{{ $student->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>NIS / NISN</td>
            <td>:</td>
            <td>{{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tempat, Tanggal Lahir</td>
            <td>:</td>
            <td>{{ $student->tempat_lahir ?? '-' }} , &nbsp; {{ formatIndoDateRaport($student->tanggal_lahir) }}</td>
        </tr>
        <tr>
            <td>Jenis Kelamin</td>
            <td>:</td>
            <td>
                @php
                    $jk = strtoupper(trim($student->jenis_kelamin ?? ''));
                @endphp
                @if (in_array($jk, ['L', 'LAKI-LAKI', 'LAKI LAKI']))
                    Laki-Laki
                @elseif (in_array($jk, ['P', 'PEREMPUAN']))
                    Perempuan
                @else
                    {{ $student->jenis_kelamin ?? '-' }}
                @endif
            </td>
        </tr>
        <tr>
            <td>Agama</td>
            <td>:</td>
            <td>{{ $student->agama ?? 'Islam' }}</td>
        </tr>
        <tr>
            <td>Pendidikan sebelumnya</td>
            <td>:</td>
            <td>{{ $student->pendidikan_sebelumnya ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat Peserta Didik</td>
            <td>:</td>
            <td>{{ $student->alamat ?? '-' }}</td>
        </tr>

        <!-- Orang Tua -->
        <tr>
            <td colspan="3" style="padding-top: 5px; font-weight: normal;">Nama Orang Tua</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Ayah</td>
            <td>:</td>
            <td>{{ $student->nama_ayah ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Ibu</td>
            <td>:</td>
            <td>{{ $student->nama_ibu ?? '-' }}</td>
        </tr>

        <!-- Pekerjaan Orang Tua -->
        <tr>
            <td colspan="3" style="padding-top: 5px; font-weight: normal;">Pekerjaan Orang Tua</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Ayah</td>
            <td>:</td>
            <td>{{ $student->pekerjaan_ayah ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Ibu</td>
            <td>:</td>
            <td>{{ $student->pekerjaan_ibu ?? '-' }}</td>
        </tr>

        <!-- Alamat Orang Tua -->
        <tr>
            <td colspan="3" style="padding-top: 5px; font-weight: normal;">Alamat Orang Tua</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Jalan</td>
            <td>:</td>
            <td>{{ $student->alamat_orang_tua ?? $student->alamat ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Kelurahan / Desa</td>
            <td>:</td>
            <td>{{ $schoolData->desa_kelurahan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Kecamatan</td>
            <td>:</td>
            <td>{{ $schoolData->kecamatan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Kabupaten / Kota</td>
            <td>:</td>
            <td>{{ $schoolData->kabupaten_kota ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Provinsi</td>
            <td>:</td>
            <td>{{ $schoolData->provinsi ?? '-' }}</td>
        </tr>

        <!-- Wali -->
        <tr>
            <td colspan="3" style="padding-top: 5px; font-weight: normal;">Wali Peserta Didik</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Nama</td>
            <td>:</td>
            <td>{{ $student->nama_wali ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Pekerjaan</td>
            <td>:</td>
            <td>{{ $student->pekerjaan_wali ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding-left: 15px;">Alamat</td>
            <td>:</td>
            <td>{{ $student->alamat_wali ?? '-' }}</td>
        </tr>
    </table>

    <!-- Tanda Tangan & Pasfoto dalam 1 Tabel Kokoh -->
    <div style="margin-top: 20px; width: 100%;">
        <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td style="width: 48%; border: none; vertical-align: bottom; text-align: center;">
                    <div class="photo-frame">
                        @if (!empty($student->foto_siswa_path) && file_exists(storage_path('app/public/' . $student->foto_siswa_path)))
                            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(storage_path('app/public/' . $student->foto_siswa_path))) }}" style="width: 2.7cm; height: 3.6cm; object-fit: cover;">
                        @endif
                    </div>
                </td>
                <td style="width: 52%; border: none; vertical-align: top; text-align: left; padding-left: 15px; font-size: 9.5pt; line-height: 1.4;">
                    {{ $kotaPrint }}, {{ formatIndoDateRaport($tgl_print ?? now()) }}<br>
                    Kepala Sekolah,<br><br><br><br><br>
                    <u style="font-weight: bold;">{{ $schoolData->nama_kepala_sekolah ?? 'Kepala Sekolah' }}</u><br>
                    NIP. {{ $schoolData->nip_kepala_sekolah ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
</div>
