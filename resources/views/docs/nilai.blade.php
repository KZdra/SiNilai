<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport_{{ $formattedStudents[0]['student_name']}}</title>
    <style>
        body {
            font-size: 12px;
            margin: 1cm;
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
            padding: 5px;
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
            height: 80px;
        }

        .rata-rata-table {
            border: solid;
            margin-top: 2px;
        }

        .rata-rata-table td {
            height: 70px;
        }
    </style>
</head>

<body>

    <h2 style="text-align: center;">LAPORAN HASIL BELAJAR (RAPOR)</h2>

    <table class="header-table">
        <tr>
            <td>Nama Peserta Didik</td>
            <td>: {{ $formattedStudents[0]['student_name'] }}</td>
            <td>Kelas</td>
            <td>: {{ $formattedStudents[0]['class_name'] }}</td>
        </tr>

        <tr>
            <td>Sekolah</td>
            <td>: SMK ICB CINTA TEKNIKA</td>
            <td>Semester</td>
            <td>: -</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: Jalan Atlas Tengah No. 2</td>

        </tr>
    </table>

    <table class="nilai-table">
        <tr>
            <th>No</th>
            <th>Mata Pelajaran</th>
            <th>Nilai Akhir</th>
            <th>Capaian Kompetensi</th>
        </tr>

        @foreach ($formattedStudents[0]['nilai_per_mapel'] as $subject => $s)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $subject }}</td>
                <td>{{ round($s) }}</td>
                {{-- <td>
                    <p>- Menunjukkan pemahaman dalam menganalisis ayat Al-Qur'an dan hadis.</p>
                    <p>- Menunjukkan pemahaman dalam menganalisis ayat Al-Qur'an dan hadis.</p>
                </td> --}}
                <td>-</td>
            </tr>
        @endforeach


    </table>
    <table class="rata-rata-table">
        <tr>
            <td style="font-weight: bold;">Nilai Rata Rata</td>
            <td style="font-weight: bold;">{{ round($formattedStudents[0]['avg_nilai_semua_mapel']) }}</td>
        </tr>
    </table>

</body>

</html>
