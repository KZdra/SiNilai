<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Nilai</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 20mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #ff9966, #ff5e62);
            color: white;
            text-align: center;
        }
        .container {
            width: 90%;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            color: black;
        }
        h1, h2, h3 {
            margin: 10px 0;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: gray;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>SERTIFIKAT NILAI</h1>
        <h2>Nama: {{$data[0]->student_name}}</h2>
        <h2>NIS: {{$data[0]->student_nis}}</h2>
        <h2>Kelas: {{$data[0]->class_name}}</h2>

        <table>
            <thead>
                <tr>
                    <th>Nilai Harian</th>
                    <th>Nilai STS</th>
                    <th>Nilai SAS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{number_format($data[0]->value_daily)}}</td>
                    <td>{{number_format($data[0]->value_sts)}}</td>
                    <td>{{number_format($data[0]->value_sas)}}</td>
                </tr>
            </tbody>
        </table>

        <h3>Rata-Rata Nilai Siswa: {{number_format($data[0]->average_value)}}</h3>

        <div class="footer">Exported by SiNilai</div>
    </div>
</body>
</html>
