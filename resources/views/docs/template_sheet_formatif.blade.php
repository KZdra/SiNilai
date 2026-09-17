<table>
    <thead>
        <tr>
            <th colspan="{{ 4 + (count($tps) > 0 ? count($tps) * 2 : 16) }}" style="font-size: 13pt; font-weight: bold; text-align: center; background-color: #047857; color: #ffffff; height: 32px;">
                FORMAT PENILAIAN CAPAIAN TUJUAN PEMBELAJARAN (FORMATIF) - KELAS {{ strtoupper($class->class_name ?? '') }}
            </th>
        </tr>
        <tr style="background-color: #f8fafc; height: 24px;">
            <th colspan="2" style="font-weight: bold; text-align: left;">Mata Pelajaran: {{ $mapel->nama_mapel ?? '-' }}</th>
            <th colspan="2" style="font-weight: bold; text-align: left;">Kelas: {{ $class->class_name ?? '-' }}</th>
            <th colspan="{{ count($tps) > 0 ? count($tps) * 2 : 16 }}" style="font-weight: bold; text-align: right;">
                Periode: {{ $fst->tahun_ajaran ?? '-' }} (Sem: {{ $fst->semester ?? '-' }} / Fase: {{ $fst->fase ?? '-' }})
            </th>
        </tr>
        <tr>
            <th colspan="{{ 4 + (count($tps) > 0 ? count($tps) * 2 : 16) }}" style="font-size: 9pt; color: #92400e; background-color: #fef3c7; font-style: italic; height: 24px; text-align: left;">
                Petunjuk: Kolom Capaian diisi: 1 = Tercapai (Pemahaman Baik), 0 = Perlu Bimbingan. Kolom Tampil Rapor diisi: 1 = Tampilkan di Rapor, 0 = Tidak Tampil.
            </th>
        </tr>
        <tr style="height: 28px;">
            <!-- Kolom Identitas Siswa -->
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 45px;">No</th>
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 110px;">NIS</th>
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 120px;">NISN</th>
            <th style="font-weight: bold; text-align: left; background-color: #1e293b; color: #ffffff; border: 1px solid #94a3b8; width: 250px;">Nama Siswa</th>

            <!-- Kolom Tiap TP (Capaian 1/0 & Tampil Rapor 1/0) -->
            @if(count($tps) > 0)
                @foreach($tps as $index => $tp)
                    <th style="font-weight: bold; text-align: center; background-color: #0f766e; color: #ffffff; border: 1px solid #94a3b8; width: 130px;" title="{{ $tp->tp_deskripsi }}">
                        TP {{ $index + 1 }} Capaian (1/0)
                    </th>
                    <th style="font-weight: bold; text-align: center; background-color: #0369a1; color: #ffffff; border: 1px solid #94a3b8; width: 130px;" title="{{ $tp->tp_deskripsi }}">
                        TP {{ $index + 1 }} Tampil Rapor (1/0)
                    </th>
                @endforeach
            @else
                @for($i = 1; $i <= 8; $i++)
                    <th style="font-weight: bold; text-align: center; background-color: #0f766e; color: #ffffff; border: 1px solid #94a3b8; width: 130px;">
                        TP {{ $i }} Capaian (1/0)
                    </th>
                    <th style="font-weight: bold; text-align: center; background-color: #0369a1; color: #ffffff; border: 1px solid #94a3b8; width: 130px;">
                        TP {{ $i }} Tampil Rapor (1/0)
                    </th>
                @endfor
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse($students as $index => $student)
            <tr style="height: 22px;">
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $index + 1 }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1; mso-number-format:'\@';">{{ $student->nis }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1; mso-number-format:'\@';">{{ $student->nisn }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $student->nama }}</td>

                @if(count($tps) > 0)
                    @foreach($tps as $tp)
                        @php
                            $tpsRow = $existingTpsiswas[$student->id][$tp->id] ?? null;
                            $kktpVal = $tpsRow ? $tpsRow->kktp : ''; // Default 1 (Tercapai)
                            $tampilVal = $tpsRow ? $tpsRow->tampilkan : ''; // Default 1 (Tampil)
                        @endphp
                        <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $kktpVal }}</td>
                        <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $tampilVal }}</td>
                    @endforeach
                @else
                    @for($i = 1; $i <= 8; $i++)
                        <td style="text-align: center; border: 1px solid #cbd5e1;"></td>
                        <td style="text-align: center; border: 1px solid #cbd5e1;"></td>
                    @endfor
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ 4 + (count($tps) > 0 ? count($tps) * 2 : 16) }}" style="text-align: center; border: 1px solid #cbd5e1; color: #94a3b8; height: 30px;">
                    Belum ada siswa terdaftar di kelas ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
