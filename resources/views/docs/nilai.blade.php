<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport_{{ $formattedStudents[0]['student_name'] }}</title>
    <style>
        body {
            font-size: 12px;
        }

        @page {
            size: A4;
            margin-top: 1.5cm;
            margin-bottom: 1.5cm;
            margin-left: 2cm;
            margin-right: 2cm;

        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 3px;
            text-align: left;
        }

        .header-table {
            border: none;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            padding: 2px;
        }

        .nilai-table td {
            height: 75px;
        }

        .absen-table {
            margin-top: 15px;
            width: 50%;
        }
    </style>
</head>

<body>
    @if ($formattedStudents[0]['fst']->ta == 'akhir')
        <h2 style="text-align: center;">LAPORAN HASIL BELAJAR<br>AKHIR SEMESTER</h2>
    @elseif ($formattedStudents[0]['fst']->ta == 'tengah')
    <h2 style="text-align: center;">LAPORAN HASIL BELAJAR (RAPOR)</h2>
    @else
        <h2 style="text-align: center;">LAPORAN HASIL BELAJAR (RAPOR)</h2>
    @endif

    <table class="header-table">
        <tr>
            <td>Nama Peserta Didik</td>
            <td>: {{ $formattedStudents[0]['student_name'] }}</td>
            <td>Kelas</td>
            <td>: {{ $formattedStudents[0]['class_name'] }}</td>
        </tr>
        <tr>
            <td>Nis</td>
            <td>: {{ $formattedStudents[0]['student_nis'] }}</td>
            <td>Fase</td>
            <td>: {{ ucwords($formattedStudents[0]['fst']->fase ?? '-') }}</td>
        </tr>
        <tr>
            <td>Sekolah</td>
            <td>: {{ $formattedStudents[0]['school_data']->nama_sekolah ?? 'SMK ICB CINTA TEKNIKA' }}</td>
            <td>Semester</td>
            <td>: {{ $formattedStudents[0]['fst']->semester ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: {{ $formattedStudents[0]['school_data']->alamat_sekolah ?? 'Jalan Atlas Tengah No. 2' }}</td>
            <td>Tahun Pembelajaran</td>
            <td>: {{ $formattedStudents[0]['fst']->tahun_ajaran ?? '-' }}</td>
        </tr>
    </table>

    <table class="nilai-table">
        <tr>
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">Mata Pelajaran</th>
            <th style="text-align: center;">Nilai Akhir</th>
            <th style="text-align: center;">Capaian Kompetensi</th>
        </tr>
        @foreach ($formattedStudents[0]['nilai_per_mapel'] as $subject => $s)
        @if ($s > 0)  <!-- Hanya tampilkan jika nilai lebih dari 0 -->
        <tr>
            <td style="text-align: center;">{{ $loop->iteration }}</td>
            <td>{{ $subject }}</td>
            <td style="text-align: center;">{{ round($s) }}</td>
            <td>
                <p>{{ $formattedStudents[0]['TP'][$subject]['Hasil_Tp_tinggi'] }}</p>
                <p>{{ $formattedStudents[0]['TP'][$subject]['Hasil_Tp_kurang'] }}</p>
            </td>
        </tr>
    @endif
    @endforeach



    </table>
    <table class="eskul-table" style="margin-top: 20px">
        <tr>
            <th>No</th>
            <th>Ekstrakulikuler</th>
            <th>Keterangan</th>
        </tr>
        @foreach ($formattedStudents[0]['eskul'] as $eskul )
        <tr> 
            <td style="width: 10px">
                {{$loop->iteration}}
            </td>
            <td>{{ $eskul->nama_eskul}}</td>
            <td>{{$eskul->nilai_eskul}} </td>
        </tr>
        @endforeach
    </table>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <!-- Tabel kiri -->
            <td style="width: 50%;border:none;padding:0;margin:0;">
                <table class="absen-table" style="width: 100%;">
                    <tr>
                        <td colspan="2" style="text-align: center;font-weight: bold">Ketidakhadiran</td>
                    </tr>
                    <tr>
                        <td style="width: 145px">Sakit</td>
                        <td style="text-align: center;">{{ $formattedStudents[0]['sakit'] }} Hari</td>
                    </tr>
                    <tr>
                        <td style="width: 145px">Izin</td>
                        <td style="text-align: center;">{{ $formattedStudents[0]['izin'] }} Hari</td>
                    </tr>
                    <tr>
                        <td style="width: 145px">Tanpa Keterangan</td>
                        <td style="text-align: center;">{{ $formattedStudents[0]['alpa'] }} Hari</td>
                    </tr>
                </table>
            </td>

            <!-- Tabel kanan -->
            <td style="width: 50%;border:none;padding:0;margin:0;padding-left:20px">
                {{-- @if ($) --}}

                {{-- @endif --}}
                {{-- <table class="absen-table" style="width: 100%;">
                    <tr>
                        <td colspan="2" style="text-align: center;font-weight: bold">Keputusan</td>
                    </tr>
                    <tr>
                        <td style="width: 145px;text-align:center;font-weight: bold" colspan="2">Berdasarkan
                            pencapaian seluruh kompetensi, peserta didik dinyatakan Naik Kelas ke Kelas XII (Dua belas)
                        </td>
                    </tr>
                </table> --}}
            </td>
        </tr>
    </table>


    <table class="ttd-table" style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="text-align: center;width:50%;border:none"></td>
            <td style="text-align: center;width:50%;border:none;font-size:14px">Bandung,
                {{ \Carbon\Carbon::parse(request()->tgl_print == 'null' ? now() : request()->tgl_print)->locale('id')->translatedFormat('d F Y') }}
            </tr>
        <tr>
            <td style="width:50%;text-align:center;border:none;font-size:14px">Orang Tua</td>
            <td style="text-align: center;width:50%;border:none;font-size:14px">WaliKelas</td>
        </tr>
        <tr>
            <td style="height: 50px;width:50%;border:none;font-size:14px"></td>
            <td style="width:50%;border:none;font-size:14px"></td>
        </tr>
        <tr>
            <td style="text-align:center;width:50%;border:none;font-size:14px">…………………………………………</td>
            <td style="text-align: center;width:50%;border:none;font-size:14px">{{ Auth::user()->name }}</td>
        </tr>
    </table>

    <table class="ttd-table"
        style="margin-top:10px;border-collapse: collapse; width: 100%;margin-left:auto;margin-right:auto;">

        <tr>
            <td style="text-align: center;width:50%;border:none;font-size:14px">Mengetahui,<br> Kepala Sekolah</td>
        </tr>
        <tr>
            <td style="height: 50px;width:50%;border:none;font-size:14px"></td>
        </tr>
        <tr>
            <td style="width:50%;border:none;font-size:14px;text-align: center">
                {{ $formattedStudents[0]['school_data']->nama_kepala_sekolah ?? 'Belum Di Atur' }}<br>Nip:
                {{ $formattedStudents[0]['school_data']->nip_kepala_sekolah ?? '-' }}</td>
        </tr>
    </table>
</body>

</html>
