<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cover_Identitas_Kelas</title>
    <style>
        @font-face {
            font-family: 'Ba';
            src: url('{{ public_path('fonts/bookantiqua.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'Ba';
            src: url('{{ public_path('fonts/bookantiqua.ttf') }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        @page {
            size: A4 portrait;
            margin-top: 0.8cm;
            margin-bottom: 0.8cm;
            margin-left: 1.2cm;
            margin-right: 1.2cm;
        }

        body {
            font-family: "Ba", "Book Antiqua", serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    @foreach ($students as $student)
        @include('docs.partials.cover_and_identity', [
            'student' => $student,
            'schoolData' => $schoolData,
            'tgl_print' => $tgl_print ?? now()
        ])
        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>
