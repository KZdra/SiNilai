<table border="1">
    <tr>
        <th bgcolor="#66a1ff">
            No
        </th>
        <th bgcolor="#66a1ff">
            Nama Siswa
        </th>
        @foreach ($formattedStudents[0]['nilai_per_mapel'] as $subject => $s)
            <th bgcolor="#66a1ff">{{ $subject }}</th>
        @endforeach
    </tr>
    @foreach ($formattedStudents as $fs)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ ucwords($fs['student_name']) }}</td>
            @foreach ($fs['nilai_per_mapel'] as $subject => $s)
                <td>{{ $s }}</td>
            @endforeach
        </tr>
    @endforeach
    {{-- <tr>
        <td colspan="2">Nilai Rata-Rata</td>
    </tr>
    <tr>
        <td colspan="2">Nilai Tertinggi</td>

    </tr>
    <tr>
        <td colspan="2">Nilai Terendah</td>
    </tr> --}}
</table>
