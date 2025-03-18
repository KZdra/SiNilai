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
            height: 75px;
        }

        .absen-table {
            margin-top: 15px;
            width: 50%;
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
            <td>Nis</td>
            <td>: {{ $formattedStudents[0]['student_nis'] }}</td>
            <td>Fase</td>
            <td>: {{ucwords($formattedStudents[0]['fst']->fase ?? "-") }}</td>
        </tr>
        <tr>
            <td>Sekolah</td>
            <td>: {{$formattedStudents[0]['school_data']->nama_sekolah ?? "SMK ICB CINTA TEKNIKA" }}</td>
            <td>Semester</td>
            <td>: {{$formattedStudents[0]['fst']->semester ?? "-" }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: {{$formattedStudents[0]['school_data']->alamat_sekolah ?? "Jalan Atlas Tengah No. 2" }}</td>
            <td>Tahun Pembelajaran</td>
            <td>: {{$formattedStudents[0]['fst']->tahun_ajaran ?? "-" }}</td>
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
                <td style="text-align: center;">{{ round($s) }}</td>
                <td>
                    <p>{{ $formattedStudents[0]['TP'][$subject]['Hasil_Tp_tinggi'] }}</p>
                    <p>{{ $formattedStudents[0]['TP'][$subject]['Hasil_Tp_kurang'] }}</p>
                </td>
            </tr>
        @endforeach


    </table>
    <table class="eskul-table" style="margin-top: 20px">
        <tr>
            <th>No</th>
            <th>Ekstrakulikuler</th>
            <th>Keterangan</th>
        </tr>
        <tr>
            <td>
            1
            </td>
            <td>Band</td>
            <td>Lorem Ipsum dolor sit amet </td>
        </tr>
    </table>

    <table class="absen-table">
        <tr><td colspan="2" style="text-align: center;">Ketidakhadiran</td></tr>
        <tr>
            <td style="width: 145px">Sakit</td>
            <td style="text-align: center">{{ $formattedStudents[0]['sakit'] }} Hari</td>
        </tr>
        <tr>
            <td style="width: 145px">Izin</td>
            <td  style="text-align: center">{{ $formattedStudents[0]['izin'] }} Hari</td>
        </tr>
        <tr>
            <td style="width: 145px">Tanpa Keterangan</td>
            <td style="text-align: center">{{ $formattedStudents[0]['alpa'] }} Hari</td>
        </tr>
    </table>
    {{-- <script type="text/javascript"> try { this.print(true); } catch (e) { window.onload = window.print; } </script> --}}
</body>

</html>
