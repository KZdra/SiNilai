<table>
    <thead>
        <tr>
            <th colspan="17" style="font-size: 14pt; font-weight: bold; text-align: center; background-color: #f1f5f9; color: #0f172a; height: 35px;">
                FORMAT PENILAIAN SISWA - KURIKULUM MERDEKA
            </th>
        </tr>
        <tr style="background-color: #f8fafc; height: 26px;">
            <th colspan="5" style="font-weight: bold; text-align: left;">Mata Pelajaran: {{ $mapel->nama_mapel ?? '-' }}</th>
            <th colspan="5" style="font-weight: bold; text-align: left;">Kelas: {{ $class->class_name ?? '-' }}</th>
            <th colspan="7" style="font-weight: bold; text-align: right;">
                Periode: {{ $fst->tahun_ajaran ?? '-' }} (Sem: {{ $fst->semester ?? '-' }} / Fase: {{ $fst->fase ?? '-' }})
            </th>
        </tr>
        <tr>
            <th colspan="17" style="font-size: 9pt; color: #475569; background-color: #fef3c7; font-style: italic; height: 24px; text-align: left;">
                Petunjuk: Isikan nilai skala 0 - 100. Kolom yang tidak dinilai biarkan KOSONG (otomatis disimpan sebagai NULL). Jangan mengubah NIS, NISN, Nama, atau Kelas.
            </th>
        </tr>
        <tr style="height: 30px;">
            <!-- Kolom Identitas Siswa -->
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 50px;">No</th>
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 110px;">NIS</th>
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 120px;">NISN</th>
            <th style="font-weight: bold; text-align: left; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 250px;">Nama Siswa</th>
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 90px;">Kelas</th>

            <!-- Kolom Asesmen Sumatif Harian 1 s.d 10 (Sesuai Urutan Web Input) -->
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 1</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 2</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 3</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 4</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 5</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 6</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 7</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 8</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 9</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 85px;">Sumatif 10</th>

            <!-- Kolom Asesmen Semester (STS & SAS) -->
            <th style="font-weight: bold; text-align: center; background-color: #0f766e; color: #ffffff; border: 1px solid #94a3b8; width: 90px;">Nilai STS</th>
            <th style="font-weight: bold; text-align: center; background-color: #0f766e; color: #ffffff; border: 1px solid #94a3b8; width: 90px;">Nilai SAS</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $index => $student)
            @php
                $val = $existingValues[$student->id] ?? null;
            @endphp
            <tr style="height: 22px;">
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $index + 1 }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1; mso-number-format:'\@';">{{ $student->nis }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1; mso-number-format:'\@';">{{ $student->nisn }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $student->nama }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $class->class_name }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily) ? $val->value_daily : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily_2) ? $val->value_daily_2 : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily_3) ? $val->value_daily_3 : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily_4) ? $val->value_daily_4 : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily_5) ? $val->value_daily_5 : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily_6) ? $val->value_daily_6 : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily_7) ? $val->value_daily_7 : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily_8) ? $val->value_daily_8 : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily_9) ? $val->value_daily_9 : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_daily_10) ? $val->value_daily_10 : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_sts) ? $val->value_sts : '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $val && !is_null($val->value_sas) ? $val->value_sas : '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="17" style="text-align: center; border: 1px solid #cbd5e1; color: #94a3b8; height: 30px;">
                    Belum ada siswa terdaftar di kelas ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
