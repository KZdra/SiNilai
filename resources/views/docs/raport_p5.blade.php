<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor_P5_{{ str_replace(' ', '_', $student->nama) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm 1cm 1.2cm 1cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            color: #111;
            line-height: 1.35;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .title-box {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
        }

        .title-box h2 {
            margin: 0;
            font-size: 13pt;
            letter-spacing: 0.5px;
        }

        .title-box h3 {
            margin: 4px 0 0 0;
            font-size: 11pt;
            font-weight: normal;
        }

        .identity-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9pt;
        }

        .identity-table td {
            border: none;
            padding: 2px 4px;
            vertical-align: top;
        }

        .legend-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background-color: #f7f9fa;
            font-size: 8.5pt;
        }

        .legend-table td {
            border: 1px solid #ccc;
            padding: 4px 6px;
        }

        .project-block {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .project-header {
            background-color: #2b4c7e;
            color: white;
            padding: 5px 8px;
            font-size: 10pt;
            font-weight: bold;
            border-radius: 3px 3px 0 0;
        }

        .project-desc {
            background-color: #f4f6f9;
            border: 1px solid #d1d5db;
            border-top: none;
            padding: 6px 8px;
            font-size: 8.5pt;
            color: #444;
            margin-bottom: 8px;
        }

        .table-matrix {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 8px;
        }

        .table-matrix th, .table-matrix td {
            border: 1px solid #333;
            padding: 4px 5px;
        }

        .table-matrix th {
            background-color: #eaeef3;
            text-align: center;
            font-weight: bold;
        }

        .check-cell {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            width: 38px;
        }

        .catatan-box {
            border: 1px solid #333;
            padding: 6px 8px;
            margin-bottom: 15px;
            background-color: #fff;
            font-size: 8.5pt;
        }

        .catatan-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 3px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 9pt;
            page-break-inside: avoid;
        }

        .signature-table td {
            border: none;
            text-align: center;
            vertical-align: top;
        }

        .qr-wrapper {
            float: left;
            text-align: left;
            font-size: 7.5pt;
            color: #555;
        }

        .qr-wrapper img {
            width: 65px;
            height: 65px;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    <!-- Header Judul -->
    <div class="title-box">
        <h2 class="uppercase">RAPOR PROJEK PENGUATAN PROFIL PELAJAR PANCASILA (P5)</h2>
        <h3>{{ $schoolData ? $schoolData->nama_sekolah : 'SEKOLAH PENGGERAK KURIKULUM MERDEKA' }}</h3>
        @if($schoolData && $schoolData->alamat_sekolah)
            <div style="font-size: 8pt; color: #555;">{{ $schoolData->alamat_sekolah }}</div>
        @endif
    </div>

    <!-- Identitas Siswa -->
    <table class="identity-table">
        <tr>
            <td style="width: 18%;" class="font-bold">Nama Peserta Didik</td>
            <td style="width: 3%;">:</td>
            <td style="width: 39%;" class="font-bold uppercase">{{ $student->nama }}</td>

            <td style="width: 16%;" class="font-bold">Kelas</td>
            <td style="width: 3%;">:</td>
            <td style="width: 21%;">{{ $student->class_name }}</td>
        </tr>
        <tr>
            <td class="font-bold">NIS / NISN</td>
            <td>:</td>
            <td>{{ $student->nis }} / {{ $student->nisn ?: '-' }}</td>

            <td class="font-bold">Fase</td>
            <td>:</td>
            <td>{{ $fst ? $fst->fase : '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">Nama Sekolah</td>
            <td>:</td>
            <td>{{ $schoolData ? $schoolData->nama_sekolah : 'SMK / SMA' }}</td>

            <td class="font-bold">Semester / T.A</td>
            <td>:</td>
            <td>{{ $fst ? $fst->semester : '-' }} / {{ $fst ? $fst->tahun_ajaran : '-' }}</td>
        </tr>
    </table>

    <!-- Keterangan Skala Nilai -->
    <table class="legend-table">
        <tr>
            <td style="width: 25%;"><strong>MB</strong> : Mulai Berkembang</td>
            <td style="width: 25%;"><strong>SB</strong> : Sedang Berkembang</td>
            <td style="width: 25%;"><strong>BSH</strong> : Berkembang Sesuai Harapan</td>
            <td style="width: 25%;"><strong>SAB</strong> : Sangat Berkembang</td>
        </tr>
    </table>

    <!-- Daftar Projek P5 -->
    @foreach ($projekDetails as $pIndex => $pItem)
        <div class="project-block">
            <div class="project-header">
                Projek {{ $pIndex + 1 }} : {{ $pItem['projek']->nama_projek }} (Tema: {{ $pItem['projek']->tema }})
            </div>
            @if($pItem['projek']->deskripsi)
                <div class="project-desc">
                    <strong>Deskripsi Projek:</strong> {{ $pItem['projek']->deskripsi }}
                </div>
            @endif

            <table class="table-matrix">
                <thead>
                    <tr>
                        <th style="width: 25%;">Dimensi & Elemen</th>
                        <th style="width: 47%;">Subelemen / Capaian Akhir Fase</th>
                        <th style="width: 7%;">MB</th>
                        <th style="width: 7%;">SB</th>
                        <th style="width: 7%;">BSH</th>
                        <th style="width: 7%;">SAB</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pItem['targets'] as $target)
                        @php
                            $rating = isset($pItem['ratings'][$target->subelemen_id]) ? $pItem['ratings'][$target->subelemen_id]->predikat : '-';
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $target->nama_dimensi }}</strong><br>
                                <span style="color: #444; font-size: 8pt;">&bull; {{ $target->nama_elemen }}</span>
                            </td>
                            <td>
                                <strong>{{ $target->nama_subelemen }}</strong><br>
                                <span style="color: #444; font-size: 8pt;">{{ $target->capaian_fase }}</span>
                            </td>
                            <td class="check-cell">{!! $rating == 'MB' ? '&#10003;' : '' !!}</td>
                            <td class="check-cell">{!! $rating == 'SB' ? '&#10003;' : '' !!}</td>
                            <td class="check-cell">{!! $rating == 'BSH' ? '&#10003;' : '' !!}</td>
                            <td class="check-cell">{!! $rating == 'SAB' ? '&#10003;' : '' !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="color: #888;">Belum ada target subelemen pada projek ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Catatan Proses Projek -->
            <div class="catatan-box">
                <div class="catatan-title">Catatan Proses Fasilitator:</div>
                <div>
                    {{ $pItem['catatan_proses'] ?: 'Peserta didik aktif berpartisipasi dalam setiap tahapan projek dan mampu mengembangkan profil Pelajar Pancasila dengan baik.' }}
                </div>
            </div>
        </div>
    @endforeach

    <!-- Tanda Tangan & QR Code Verifikasi -->
    <table class="signature-table">
        <tr>
            <td style="width: 33%;">
                @if($qrCodeDataUri)
                    <div class="qr-wrapper">
                        <img src="{{ $qrCodeDataUri }}" alt="QR Verifikasi"><br>
                        <span>Scan untuk verifikasi resmi</span>
                    </div>
                @endif
            </td>
            <td style="width: 33%;"></td>
            <td style="width: 34%;">
                Tempat, {{ $tgl_print }}<br>
                Fasilitator / Wali Kelas
                <div style="height: 50px;"></div>
                <strong>{{ $projekDetails[0]['projek']->fasilitator_name ?? 'Wali Kelas' }}</strong>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 15px;">
                Mengetahui,<br>
                Orang Tua / Wali Murid
                <div style="height: 45px;"></div>
                ( .................................................. )
            </td>
            <td style="padding-top: 15px;" colspan="2">
                Mengetahui,<br>
                Kepala Sekolah
                <div style="height: 45px;"></div>
                <strong>{{ $schoolData ? $schoolData->nama_kepala_sekolah : 'Kepala Sekolah' }}</strong><br>
                <span>NIP. {{ $schoolData ? $schoolData->nip_kepala_sekolah : '-' }}</span>
            </td>
        </tr>
    </table>

</body>
</html>
