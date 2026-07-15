<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport_{{ $formattedStudents[0]['student_name'] }}</title>
    <style>
        @font-face {
            font-family: 'Ba';
            src: url('{{ public_path('fonts/bookantiqua.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        body {
            font-family: "Ba";
            font-size: 10pt;
            margin-top: 0.5cm;
            margin-bottom: 0.5cm;
            margin-left: 0.3cm;
            margin-right: 0.3cm;
        }

        .f-9 {
            font-size: 9pt;
        }

        .hjdul {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            font-weight: bold;
        }

        /* @page {
            size: A4;
        } */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 2px;
            /* padding-left: 2px; */
            text-align: left;
        }

        .header-table {
            border: none;
            width: 600px;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            padding: 0px;
        }

        .nilai-table td {
            height: 75px;
        }

        .absen-table {
            margin-top: 15px;
            width: 50%;
        }

        .wleutik {
            width: 150px;
        }
    </style>
</head>

<body>
    @if ($formattedStudents[0]['fst']->ta == 'akhir')
        <h2 style="text-align: center;" class="hjdul">LAPORAN HASIL BELAJAR<br>AKHIR SEMESTER</h2>
    @elseif ($formattedStudents[0]['fst']->ta == 'tengah')
        <h2 style="text-align: center;" class="hjdul">LAPORAN HASIL BELAJAR <br> (RAPOR)</h2>
    @else
        <h2 style="text-align: center;" class="hjdul">LAPORAN HASIL BELAJAR <br>(RAPOR)</h2>
    @endif

    <table class="header-table">
        <tr>
            <td class="wleutik">Nama Peserta Didik</td>
            <td>: {{ $formattedStudents[0]['student_name'] }}</td>
            <td class="wleutik">Kelas</td>
            <td>: {{ $formattedStudents[0]['class_name'] }}</td>
        </tr>
        <tr>
            <td class="wleutik">Nis</td>
            <td>: {{ $formattedStudents[0]['student_nis'] }}</td>
            <td class="wleutik">Fase</td>
            <td>: {{ ucwords($formattedStudents[0]['fst']->fase ?? '-') }}</td>
        </tr>
        <tr>
            <td class="wleutik">Sekolah</td>
            <td>: {{ $formattedStudents[0]['school_data']->nama_sekolah ?? 'SMK ICB CINTA TEKNIKA' }}</td>
            <td class="wleutik">Semester</td>
            <td>: {{ $formattedStudents[0]['fst']->semester ?? '-' }}</td>
        </tr>
        <tr>
            <td class="wleutik">Alamat</td>
            <td>: {{ $formattedStudents[0]['school_data']->alamat_sekolah ?? 'Jalan Atlas Tengah No. 2' }}</td>
            <td class="wleutik">Tahun Pembelajaran</td>
            <td>: {{ $formattedStudents[0]['fst']->tahun_ajaran ?? '-' }}</td>
        </tr>
    </table>

    <table class="nilai-table">
        <tr>
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">Mata Pelajaran</th>
            <th style="text-align: center;width:50px">Nilai Akhir</th>
            <th style="text-align: center;">Capaian Kompetensi</th>
        </tr>
        @foreach ($formattedStudents[0]['nilai_per_mapel'] as $subject => $s)
            {{-- @if ($s > 0)  <!-- Hanya tampilkan jika nilai lebih dari 0 --> --}}
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td>{{ $subject }}</td>
                <td style="text-align: center;">{{ round($s) }}</td>
                <td>
                    <p class="f-9">{{ $formattedStudents[0]['TP'][$subject]['Hasil_Tp_tinggi'] }}</p>
                    <p class="f-9">{{ $formattedStudents[0]['TP'][$subject]['Hasil_Tp_kurang'] }}</p>
                </td>
            </tr>
            {{-- @endif --}}
        @endforeach



    </table>
    <div style="page-break-inside: avoid;">
        <table class="eskul-table" style="margin-top: 20px; width: 100%;">
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Ekstrakulikuler</th>
                <th style="width: 55%;">Keterangan</th>
            </tr>
            @forelse ($formattedStudents[0]['eskul'] as $eskul)
                <tr>
                    <td style="text-align: center;">
                        {{ $loop->iteration }}
                    </td>
                    <td>{{ $eskul->nama_eskul }}</td>
                    <td>{{ $eskul->nilai_eskul }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">-</td>
                </tr>
            @endforelse
        </table>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <!-- Tabel kiri -->
                <td style="width: 50%;border:none;padding:0;margin:0;vertical-align: top;">
                    <table class="absen-table" style="width: 100%; margin-top:0;">
                        <tr>
                            <td colspan="3" style="text-align: center;font-weight: bold">Ketidakhadiran</td>
                        </tr>
                        <tr>
                            <td style="width: 145px">Sakit</td>
                            <td style="text-align: center; width: 50px;">{{ $formattedStudents[0]['sakit'] }}</td>
                            <td style="text-align: center; width: 50px;">hari</td>
                        </tr>
                        <tr>
                            <td style="width: 145px">Izin</td>
                            <td style="text-align: center; width: 50px;">{{ $formattedStudents[0]['izin'] }}</td>
                            <td style="text-align: center; width: 50px;">hari</td>
                        </tr>
                        <tr>
                            <td style="width: 145px">Tanpa Keterangan</td>
                            <td style="text-align: center; width: 50px;">{{ $formattedStudents[0]['alpa'] }}</td>
                            <td style="text-align: center; width: 50px;">hari</td>
                        </tr>
                    </table>
                </td>

                <!-- Tabel kanan -->
                <td style="width: 50%;border:none;padding:0;margin:0;padding-left:20px;vertical-align: top;">
                    @if ($keputusan)
                        <table class="absen-table" style="width: 100%; margin-top:0;">
                            <tr>
                                <td colspan="2" style="text-align: center;font-weight: bold">Keputusan:</td>
                            </tr>
                            <tr>
                                <td style="text-align:center;" colspan="2">
                                    {{ $keputusan }}
                                </td>
                            </tr>
                        </table>
                    @endif
                </td>
            </tr>
        </table>

        <table class="ttd-table" style="border-collapse: collapse; width: 100%; margin-top: 20px;">
            <tr>
                <td style="text-align: center;width:50%;border:none"></td>
                <td style="text-align: center;width:50%;font-size:10.5pt;border:none">Bandung,
                    {{ \Carbon\Carbon::parse($tgl_print == 'null' ? now() : $tgl_print)->locale('id')->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td style="width:50%;text-align:center;font-size:10.5pt;border:none">Orang Tua,</td>
                <td style="text-align: center;width:50%;font-size:10.5pt;border:none">Wali Kelas</td>
            </tr>
            <tr>
                <td style="height: 60px;width:50%;border:none"></td>
                <td style="height: 60px;width:50%;border:none"></td>
            </tr>
            <tr>
                <td style="text-align:center;width:50%;font-size:10.5pt;border:none">……………………………</td>
                <td style="text-align:center;width:50%;font-size:10.5pt;border:none">{{ Auth::user()->name }}</td>
            </tr>
        </table>

        <table class="ttd-table" style="border-collapse: collapse; width: 100%;margin-left:auto;margin-right:auto; margin-top: 10px;">
            <tr>
                <td style="text-align: center;width:50%;border:none;font-size:10.5pt;">Mengetahui,<br> Kepala Sekolah</td>
            </tr>
            <tr>
                <td style="height: 60px;width:50%;border:none;font-size:10.5pt;"></td>
            </tr>
            <tr>
                <td style="width:50%;border:none;font-size:10.5pt;text-align: center">
                    {{ $formattedStudents[0]['school_data']->nama_kepala_sekolah ?? 'Belum Di Atur' }}<br>NIP.
                    {{ $formattedStudents[0]['school_data']->nip_kepala_sekolah ?? '-' }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
