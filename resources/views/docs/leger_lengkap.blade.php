<table>
    <!-- Kop Leger -->
    <tr>
        <th colspan="{{ count($mapelNames) + 11 }}" style="text-align: center; font-weight: bold; font-size: 14pt;">
            LEGER NILAI HASIL BELAJAR PESERTA DIDIK
        </th>
    </tr>
    <tr>
        <th colspan="{{ count($mapelNames) + 11 }}" style="text-align: center; font-weight: bold; font-size: 11pt;">
            {{ $schoolData ? strtoupper($schoolData->nama_sekolah) : 'SATUAN PENDIDIKAN KURIKULUM MERDEKA' }}
        </th>
    </tr>
    <tr>
        <th colspan="{{ count($mapelNames) + 11 }}" style="text-align: center; font-size: 10pt;">
            Kelas: {{ $className }} | Fase: {{ $fst ? $fst->fase : '-' }} | Semester: {{ $fst ? $fst->semester : '-' }} | Tahun Ajaran: {{ $fst ? $fst->tahun_ajaran : '-' }}
        </th>
    </tr>
    <tr>
        <th colspan="{{ count($mapelNames) + 11 }}" style="text-align: center; font-size: 9.5pt;">
            Wali Kelas: {{ $walasName ?: '-' }}
        </th>
    </tr>
    <tr></tr>

    <!-- Header Tabel Leger -->
    <thead>
        <tr>
            <th rowspan="2" style="background-color: #1f4e78; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000; vertical-align: middle;">No</th>
            <th rowspan="2" style="background-color: #1f4e78; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000; vertical-align: middle;">NIS</th>
            <th rowspan="2" style="background-color: #1f4e78; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000; vertical-align: middle;">NISN</th>
            <th rowspan="2" style="background-color: #1f4e78; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000; vertical-align: middle;">Nama Peserta Didik</th>
            <th rowspan="2" style="background-color: #1f4e78; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000; vertical-align: middle;">L/P</th>
            <th colspan="{{ count($mapelNames) }}" style="background-color: #2f5597; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000;">Nilai Mata Pelajaran</th>
            <th rowspan="2" style="background-color: #1f4e78; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000; vertical-align: middle;">Total</th>
            <th rowspan="2" style="background-color: #1f4e78; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000; vertical-align: middle;">Rata-Rata</th>
            <th rowspan="2" style="background-color: #1f4e78; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000; vertical-align: middle;">Rank</th>
            <th colspan="3" style="background-color: #2f5597; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000;">Ketidakhadiran</th>
            <th rowspan="2" style="background-color: #1f4e78; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000; vertical-align: middle;">Keputusan / Catatan</th>
        </tr>
        <tr>
            @foreach ($mapelNames as $mpName)
                <th style="background-color: #4472c4; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $mpName }}</th>
            @endforeach
            <th style="background-color: #4472c4; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000;">S</th>
            <th style="background-color: #4472c4; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000;">I</th>
            <th style="background-color: #4472c4; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000;">A</th>
        </tr>
    </thead>

    <!-- Body Siswa -->
    <tbody>
        @foreach ($students as $idx => $s)
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $idx + 1 }}</td>
                <td style="text-align: center; border: 1px solid #000000;">'{{ $s['nis'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">'{{ $s['nisn'] ?: '-' }}</td>
                <td style="border: 1px solid #000000;">{{ ucwords($s['nama']) }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $s['jenis_kelamin'] ?: '-' }}</td>

                <!-- Nilai per Mapel -->
                @foreach ($mapelNames as $mpName)
                    <td style="text-align: center; border: 1px solid #000000;">
                        {{ isset($s['scores'][$mpName]) && $s['scores'][$mpName] > 0 ? $s['scores'][$mpName] : '-' }}
                    </td>
                @endforeach

                <!-- Total & Rata-rata -->
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $s['total_nilai'] }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ number_format($s['avg_nilai'], 2) }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $s['rank'] }}</td>

                <!-- Absensi -->
                <td style="text-align: center; border: 1px solid #000000;">{{ $s['sakit'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $s['izin'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $s['alpa'] }}</td>

                <!-- Keputusan -->
                <td style="border: 1px solid #000000;">{{ $s['keputusan'] ?: 'Memenuhi Kriteria' }}</td>
            </tr>
        @endforeach

        <!-- Baris Statistik Kelas -->
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right; background-color: #d9e1f2; border: 1px solid #000000;">Rata-Rata Kelas</td>
            @foreach ($mapelNames as $mpName)
                <td style="font-weight: bold; text-align: center; background-color: #d9e1f2; border: 1px solid #000000;">
                    {{ isset($stats['avg'][$mpName]) ? number_format($stats['avg'][$mpName], 2) : '-' }}
                </td>
            @endforeach
            <td colspan="5" style="background-color: #d9e1f2; border: 1px solid #000000;"></td>
        </tr>
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right; background-color: #e2efda; border: 1px solid #000000;">Nilai Tertinggi</td>
            @foreach ($mapelNames as $mpName)
                <td style="font-weight: bold; text-align: center; background-color: #e2efda; border: 1px solid #000000;">
                    {{ isset($stats['max'][$mpName]) ? $stats['max'][$mpName] : '-' }}
                </td>
            @endforeach
            <td colspan="5" style="background-color: #e2efda; border: 1px solid #000000;"></td>
        </tr>
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right; background-color: #fce4d6; border: 1px solid #000000;">Nilai Terendah</td>
            @foreach ($mapelNames as $mpName)
                <td style="font-weight: bold; text-align: center; background-color: #fce4d6; border: 1px solid #000000;">
                    {{ isset($stats['min'][$mpName]) ? $stats['min'][$mpName] : '-' }}
                </td>
            @endforeach
            <td colspan="5" style="background-color: #fce4d6; border: 1px solid #000000;"></td>
        </tr>
    </tbody>
</table>

<!-- Footer Tanda Tangan -->
<table>
    <tr></tr>
    <tr>
        <td colspan="3"></td>
        <td colspan="{{ intval(count($mapelNames) / 2) }}" style="text-align: center;">
            Mengetahui,<br>
            Kepala Sekolah
            <br><br><br><br>
            <strong>{{ $schoolData ? $schoolData->nama_kepala_sekolah : 'Kepala Sekolah' }}</strong><br>
            NIP. {{ $schoolData ? $schoolData->nip_kepala_sekolah : '-' }}
        </td>
        <td colspan="3"></td>
        <td colspan="{{ intval(count($mapelNames) / 2) + 2 }}" style="text-align: center;">
            Tanggal: {{ date('d F Y') }}<br>
            Wali Kelas {{ $className }}
            <br><br><br><br>
            <strong>{{ $walasName ?: '( .................................................. )' }}</strong>
        </td>
    </tr>
</table>
