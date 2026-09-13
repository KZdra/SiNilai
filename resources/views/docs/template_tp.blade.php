<table>
    <thead>
        <tr>
            <th colspan="3" style="font-size: 14pt; font-weight: bold; text-align: center; background-color: #f1f5f9; color: #0f172a; height: 35px;">
                FORMAT TUJUAN PEMBELAJARAN (TP) - KURIKULUM MERDEKA
            </th>
        </tr>
        <tr style="background-color: #f8fafc; height: 26px;">
            <th style="font-weight: bold; text-align: left;">Mata Pelajaran: {{ $mapel->nama_mapel ?? '-' }}</th>
            <th style="font-weight: bold; text-align: left;">Kelas: {{ $class ? $class->class_name : 'Semua Kelas' }}</th>
            <th style="font-weight: bold; text-align: right;">
                Periode: {{ $fst->tahun_ajaran ?? '-' }} (Sem: {{ $fst->semester ?? '-' }} / Fase: {{ $fst->fase ?? '-' }})
            </th>
        </tr>
        <tr>
            <th colspan="3" style="font-size: 9pt; color: #475569; background-color: #fef3c7; font-style: italic; height: 24px; text-align: left;">
                Petunjuk: Isikan deskripsi Tujuan Pembelajaran pada kolom C (Deskripsi Tujuan Pembelajaran). TP ini berlaku sebagai acuan untuk seluruh kelas pada mata pelajaran ini.
            </th>
        </tr>
        <tr style="height: 30px;">
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 60px;">No</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #94a3b8; width: 120px;">Kode TP</th>
            <th style="font-weight: bold; text-align: left; background-color: #0f766e; color: #ffffff; border: 1px solid #94a3b8; width: 550px;">Deskripsi Tujuan Pembelajaran (Kompetensi)</th>
        </tr>
    </thead>
    <tbody>
        @if (count($existingTps) > 0)
            @foreach ($existingTps as $index => $tp)
                <tr style="height: 28px;">
                    <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $index + 1 }}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1;">TP {{ $index + 1 }}</td>
                    <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $tp->tp_deskripsi }}</td>
                </tr>
            @endforeach
        @else
            <tr style="height: 28px;">
                <td style="text-align: center; border: 1px solid #cbd5e1;">1</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">TP 1</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Memahami konsep dasar dan struktur materi pembelajaran</td>
            </tr>
            <tr style="height: 28px;">
                <td style="text-align: center; border: 1px solid #cbd5e1;">2</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">TP 2</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Menganalisis dan mengevaluasi penerapan kaidah dalam studi kasus</td>
            </tr>
            <tr style="height: 28px;">
                <td style="text-align: center; border: 1px solid #cbd5e1;">3</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">TP 3</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Merancang dan mempresentasikan solusi berbasis proyek kreatif</td>
            </tr>
            <tr style="height: 28px;">
                <td style="text-align: center; border: 1px solid #cbd5e1;">4</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">TP 4</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Menerapkan prinsip etika dan kerja sama tim dalam penyelesaian tugas</td>
            </tr>
        @endif
    </tbody>
</table>
