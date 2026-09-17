<table>
    <thead>
        <tr>
            <th colspan="19" style="font-size: 13pt; font-weight: bold; text-align: center; background-color: #0f172a; color: #ffffff; height: 32px;">
                FORMAT IMPORT DATA SISWA - KELAS {{ strtoupper($class->class_name) }}
            </th>
        </tr>
        <tr style="background-color: #f8fafc; height: 24px;">
            <th colspan="6" style="font-weight: bold; text-align: left;">Kelas: {{ $class->class_name }}</th>
            <th colspan="13" style="font-size: 9pt; color: #64748b; text-align: right; font-style: italic;">
                Petunjuk: Isikan data siswa kelas ini. Format Tanggal Lahir: YYYY-MM-DD (Contoh: 2011-08-17). L/P: L (Laki-laki) / P (Perempuan).
            </th>
        </tr>
        <tr style="height: 28px;">
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #475569; width: 45px;">No</th>
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #475569; width: 110px;">NIS</th>
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #475569; width: 120px;">NISN</th>
            <th style="font-weight: bold; text-align: left; background-color: #1e293b; color: #ffffff; border: 1px solid #475569; width: 240px;">Nama Peserta Didik</th>
            <th style="font-weight: bold; text-align: center; background-color: #1e293b; color: #ffffff; border: 1px solid #475569; width: 85px;">Kelas</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #475569; width: 60px;">L/P</th>
            <th style="font-weight: bold; text-align: left; background-color: #2563eb; color: #ffffff; border: 1px solid #475569; width: 140px;">Tempat Lahir</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #475569; width: 120px;">Tanggal Lahir</th>
            <th style="font-weight: bold; text-align: center; background-color: #2563eb; color: #ffffff; border: 1px solid #475569; width: 100px;">Agama</th>
            <th style="font-weight: bold; text-align: left; background-color: #2563eb; color: #ffffff; border: 1px solid #475569; width: 160px;">Pendidikan Sebelumnya</th>
            <th style="font-weight: bold; text-align: left; background-color: #2563eb; color: #ffffff; border: 1px solid #475569; width: 250px;">Alamat Peserta Didik</th>
            <th style="font-weight: bold; text-align: left; background-color: #0f766e; color: #ffffff; border: 1px solid #475569; width: 160px;">Nama Ayah</th>
            <th style="font-weight: bold; text-align: left; background-color: #0f766e; color: #ffffff; border: 1px solid #475569; width: 160px;">Nama Ibu</th>
            <th style="font-weight: bold; text-align: left; background-color: #0f766e; color: #ffffff; border: 1px solid #475569; width: 140px;">Pekerjaan Ayah</th>
            <th style="font-weight: bold; text-align: left; background-color: #0f766e; color: #ffffff; border: 1px solid #475569; width: 140px;">Pekerjaan Ibu</th>
            <th style="font-weight: bold; text-align: left; background-color: #0f766e; color: #ffffff; border: 1px solid #475569; width: 250px;">Alamat Orang Tua</th>
            <th style="font-weight: bold; text-align: center; background-color: #d97706; color: #ffffff; border: 1px solid #475569; width: 50px;">S</th>
            <th style="font-weight: bold; text-align: center; background-color: #d97706; color: #ffffff; border: 1px solid #475569; width: 50px;">I</th>
            <th style="font-weight: bold; text-align: center; background-color: #d97706; color: #ffffff; border: 1px solid #475569; width: 50px;">A</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $idx => $s)
            <tr style="height: 22px;">
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $idx + 1 }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1; mso-number-format:'\@';">{{ $s->nis }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1; mso-number-format:'\@';">{{ $s->nisn }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $s->nama }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $class->class_name }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $s->jenis_kelamin }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $s->tempat_lahir }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $s->tanggal_lahir }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $s->agama }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $s->pendidikan_sebelumnya }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $s->alamat }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $s->nama_ayah }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $s->nama_ibu }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $s->pekerjaan_ayah }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $s->pekerjaan_ibu }}</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $s->alamat_orang_tua }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $s->sakit ?: '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $s->izin ?: '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $s->alpa ?: '' }}</td>
            </tr>
        @empty
            <tr style="height: 22px;">
                <td style="text-align: center; border: 1px solid #cbd5e1;">1</td>
                <td style="text-align: center; border: 1px solid #cbd5e1; mso-number-format:'\@';">1001</td>
                <td style="text-align: center; border: 1px solid #cbd5e1; mso-number-format:'\@';">0081234567</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Contoh Nama Siswa</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $class->class_name }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">L</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Jakarta</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">2011-05-12</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">Islam</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">SD Negeri 1</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Jl. Merdeka No. 10</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Nama Ayah</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Nama Ibu</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Karyawan Swasta</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Ibu Rumah Tangga</td>
                <td style="text-align: left; border: 1px solid #cbd5e1;">Jl. Merdeka No. 10</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">0</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">0</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">0</td>
            </tr>
        @endforelse
    </tbody>
</table>
