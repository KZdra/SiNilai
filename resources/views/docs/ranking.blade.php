<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid black;">Peringkat</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid black;">Nama Siswa</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid black;">Kelas</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid black;">Rata-Rata Nilai Akhir</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($students as $index => $student)
            <tr>
                <td style="text-align: center; border: 1px solid black;">{{ $index + 1 }}</td>
                <td style="border: 1px solid black;">{{ $student->student_name }}</td>
                <td style="text-align: center; border: 1px solid black;">{{ $student->class_name }}</td>
                <td style="text-align: center; border: 1px solid black;">{{ $student->avg_nilai_semua_mapel }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
