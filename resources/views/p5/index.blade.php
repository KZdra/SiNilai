@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-shapes mr-2 text-primary"></i>Modul Projek P5
                    </h1>
                    <p class="text-muted small mb-0">Projek Penguatan Profil Pelajar Pancasila (Kurikulum Merdeka) & Cetak Rapor Projek.</p>
                </div>
                <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                    <button class="btn btn-success shadow-sm" id="btnTambahProjek">
                        <i class="fas fa-plus-circle mr-1"></i>Tambah Projek P5
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Filter Box -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body bg-light rounded">
                    <form method="GET" action="{{ route('p5.index') }}" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-5">
                                <label for="class_id" class="font-weight-bold small text-uppercase text-secondary">Pilih Kelas</label>
                                <select name="class_id" id="class_id" class="form-control" onchange="document.getElementById('filterForm').submit()">
                                    @include('partials.select_class_options', ['classList' => $classList, 'selectedId' => $selectedClassId])
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="fst_id" class="font-weight-bold small text-uppercase text-secondary">Fase / Semester / Tahun Ajaran</label>
                                <select name="fst_id" id="fst_id" class="form-control" onchange="document.getElementById('filterForm').submit()">
                                    @include('partials.select_fst_options', ['fstList' => $fstList, 'selectedId' => $selectedFstId])
                                </select>
                            </div>
                            <div class="col-md-2 mt-2 mt-md-0">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-sync-alt mr-1"></i>Muat Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Projek P5 -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-folder-open text-warning mr-2"></i>Daftar Projek P5 Kelas Aktif
                    </h5>
                    <span class="badge badge-primary px-3 py-2 font-weight-normal">{{ count($projekList) }} Projek Terdaftar</span>
                </div>
                <div class="card-body p-0">
                    @if(count($projekList) == 0)
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-check fa-4x text-muted mb-3 opacity-50"></i>
                            <h5 class="text-secondary font-weight-bold">Belum Ada Projek P5 di Kelas & Semester Ini</h5>
                            <p class="text-muted small">Klik tombol <strong>"Tambah Projek P5"</strong> di atas untuk membuat projek baru dan memilih target subelemen dimensi Pancasila.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small text-uppercase">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Tema & Nama Projek</th>
                                        <th>Fasilitator</th>
                                        <th class="text-center">Target Subelemen</th>
                                        <th class="text-center">Status Penilaian</th>
                                        <th class="text-center" style="width: 220px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($projekList as $idx => $prj)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <span class="badge badge-info px-2 py-1 mb-1 font-weight-normal">{{ $prj->tema }}</span>
                                                <div class="font-weight-bold text-dark">{{ $prj->nama_projek }}</div>
                                                <small class="text-muted">{{ Str::limit($prj->deskripsi, 90) }}</small>
                                            </td>
                                            <td>
                                                <i class="fas fa-chalkboard-teacher text-muted mr-1"></i>
                                                {{ $prj->fasilitator_name ?: 'Wali Kelas' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary px-2 py-1">
                                                    {{ $prj->subelemen_count }} Subelemen
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-pill badge-light border px-3 py-1 font-weight-bold text-success">
                                                    <i class="fas fa-check-circle mr-1"></i>{{ $prj->students_assessed_count }} Siswa Dinilai
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('p5.penilaian', $prj->id) }}" class="btn btn-sm btn-primary mr-1" title="Input Penilaian Siswa">
                                                    <i class="fas fa-pen-nib mr-1"></i>Nilai
                                                </a>
                                                <button class="btn btn-sm btn-warning mr-1 text-white btnEditProjek" data-id="{{ $prj->id }}" title="Edit Projek">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btnDeleteProjek" data-id="{{ $prj->id }}" data-nama="{{ $prj->nama_projek }}" title="Hapus Projek">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bagian Cetak Lembar Rapor P5 Siswa -->
            @if ($selectedClassId && $selectedFstId)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-file-pdf text-danger mr-2"></i>Cetak Lembar Rapor P5 Resmi Siswa
                    </h5>
                    <small class="text-muted">Standar Kurikulum Merdeka Kemendikbudristek + QR Code Verifikasi</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableCetakP5">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>NIS / NISN</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th class="text-center" style="width: 180px;">Cetak Rapor P5</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $students = DB::table('students')->where('class_id', $selectedClassId)->orderBy('nama')->get();
                                @endphp
                                @forelse ($students as $i => $std)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $std->nis }} / {{ $std->nisn ?: '-' }}</td>
                                        <td class="font-weight-bold text-dark">{{ $std->nama }}</td>
                                        <td><span class="badge badge-light border">{{ $std->class_id }}</span></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-danger btnCetakP5" 
                                                    data-student-id="{{ $std->id }}"
                                                    data-student-name="{{ $std->nama }}"
                                                    data-class-id="{{ $selectedClassId }}"
                                                    data-fst-id="{{ $selectedFstId }}">
                                                <i class="fas fa-print mr-1"></i>Cetak Rapor P5
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Tidak ada siswa pada kelas ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Modal Tambah/Edit Projek P5 -->
    <div class="modal fade" id="modalProjek" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalProjekTitle">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Projek P5
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formProjek">
                    @csrf
                    <input type="hidden" name="projek_id" id="form_projek_id">
                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                    <input type="hidden" name="fst_id" value="{{ $selectedFstId }}">

                    <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                        <div class="form-group">
                            <label class="font-weight-bold">Tema Projek P5 <span class="text-danger">*</span></label>
                            <select name="tema" id="form_tema" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Tema Resmi P5 --</option>
                                <option value="Gaya Hidup Berkelanjutan">Gaya Hidup Berkelanjutan</option>
                                <option value="Kearifan Lokal">Kearifan Lokal</option>
                                <option value="Bhinneka Tunggal Ika">Bhinneka Tunggal Ika</option>
                                <option value="Bangunlah Jiwa dan Raganya">Bangunlah Jiwa dan Raganya</option>
                                <option value="Suara Demokrasi">Suara Demokrasi</option>
                                <option value="Rekayasa dan Teknologi">Rekayasa dan Teknologi</option>
                                <option value="Kewirausahaan">Kewirausahaan</option>
                                <option value="Kebekerjaan">Kebekerjaan (SMK)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Judul / Nama Projek <span class="text-danger">*</span></label>
                            <input type="text" name="nama_projek" id="form_nama_projek" class="form-control" placeholder="Contoh: Pemilahan Sampah Organik Menjadi Kompos Cair" required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Deskripsi Singkat Projek</label>
                            <textarea name="deskripsi" id="form_deskripsi" class="form-control" rows="3" placeholder="Tuliskan tujuan dan gambaran pelaksanaan projek..."></textarea>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Fasilitator Projek</label>
                            <select name="fasilitator_id" id="form_fasilitator_id" class="form-control">
                                <option value="">-- Gunakan Akun Saat Ini ({{ Auth::user()->name }}) --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold mb-0 text-dark">
                                <i class="fas fa-check-double text-primary mr-1"></i>Pilih Target Subelemen Dimensi P5 <span class="text-danger">*</span>
                            </label>
                            <small class="text-muted">Centang capaian yang dinilai dalam projek ini</small>
                        </div>

                        <div class="accordion" id="accordionDimensi">
                            @foreach ($dimensiMaster as $dIdx => $dim)
                                <div class="card mb-2 border">
                                    <div class="card-header bg-light py-2" id="heading{{ $dim->id }}">
                                        <h6 class="mb-0">
                                            <button class="btn btn-link btn-block text-left text-dark font-weight-bold text-decoration-none p-0 collapsed" type="button" data-toggle="collapse" data-target="#collapse{{ $dim->id }}">
                                                <i class="fas fa-chevron-right mr-2 text-primary"></i>[{{ $dim->kode }}] {{ $dim->nama_dimensi }}
                                            </button>
                                        </h6>
                                    </div>

                                    <div id="collapse{{ $dim->id }}" class="collapse" data-parent="#accordionDimensi">
                                        <div class="card-body p-3">
                                            @foreach ($dim->elemen as $el)
                                                <div class="font-weight-bold text-secondary mb-1 small text-uppercase">
                                                    <i class="fas fa-angle-right mr-1"></i>{{ $el->nama_elemen }}
                                                </div>
                                                <div class="ml-3 mb-3">
                                                    @foreach ($el->subelemen as $sub)
                                                        <div class="custom-control custom-checkbox mb-2">
                                                            <input type="checkbox" class="custom-control-input subelemen-checkbox" id="sub_{{ $sub->id }}" name="subelemen_ids[]" value="{{ $sub->id }}">
                                                            <label class="custom-control-label" for="sub_{{ $sub->id }}">
                                                                <strong>{{ $sub->nama_subelemen }}</strong>
                                                                <br><small class="text-muted">{{ $sub->capaian_fase }}</small>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanProjek">
                            <i class="fas fa-save mr-1"></i>Simpan Projek
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Cetak Tanggal -->
    <div class="modal fade" id="modalPrintDate" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title font-weight-bold"><i class="fas fa-calendar-alt mr-2"></i>Tanggal Rapor P5</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Tanggal Cetak</label>
                        <input type="date" id="print_tgl_p5" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <p class="text-muted small mb-0">Siswa: <strong id="print_student_name_label"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnProsesCetakP5">
                        <i class="fas fa-print mr-1"></i>Unduh PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    // Tambah Projek
    $('#btnTambahProjek').on('click', function() {
        $('#formProjek')[0].reset();
        $('#form_projek_id').val('');
        $('#form_tema').val('').trigger('change');
        $('#form_fasilitator_id').val('').trigger('change');
        $('.subelemen-checkbox').prop('checked', false);
        $('#modalProjekTitle').html('<i class="fas fa-plus-circle mr-2"></i>Tambah Projek P5 Baru');
        $('#modalProjek').modal('show');
    });

    // Simpan Projek (Create / Update)
    $('#formProjek').on('submit', function(e) {
        e.preventDefault();
        
        let checkedSubs = $('.subelemen-checkbox:checked').length;
        if (checkedSubs === 0) {
            SwalHelper.showWarning('Pilih minimal 1 target subelemen dimensi P5!', 'Perhatian');
            return;
        }

        let projekId = $('#form_projek_id').val();
        let url = projekId ? '/p5/projek/' + projekId : '/p5/projek';
        let method = projekId ? 'PUT' : 'POST';

        Swal.fire({
            title: 'Menyimpan Projek...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(res) {
                SwalHelper.showSuccess(res.message, 'Berhasil!').then(() => {
                    location.reload();
                });
            },
            error: function(err) {
                let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Terjadi kesalahan sistem';
                SwalHelper.showError(msg, 'Gagal!');
            }
        });
    });

    // Edit Projek
    $('.btnEditProjek').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({ title: 'Memuat data...', didOpen: () => Swal.showLoading() });

        $.get('/p5/projek/' + id + '/edit', function(res) {
            Swal.close();
            $('#form_projek_id').val(res.projek.id);
            $('#form_tema').val(res.projek.tema).trigger('change');
            $('#form_nama_projek').val(res.projek.nama_projek);
            $('#form_deskripsi').val(res.projek.deskripsi);
            $('#form_fasilitator_id').val(res.projek.fasilitator_id).trigger('change');

            $('.subelemen-checkbox').prop('checked', false);
            res.subelemen_ids.forEach(function(subId) {
                $('#sub_' + subId).prop('checked', true);
            });

            $('#modalProjekTitle').html('<i class="fas fa-edit mr-2"></i>Edit Projek P5');
            $('#modalProjek').modal('show');
        }).fail(function() {
            SwalHelper.showError('Gagal memuat detail projek.', 'Error');
        });
    });

    // Hapus Projek
    $('.btnDeleteProjek').on('click', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');

        SwalHelper.showConfirm('Seluruh penilaian siswa pada projek "' + nama + '" akan ikut terhapus.', 'Hapus Projek P5?').then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/p5/projek/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        SwalHelper.showSuccess(res.message, 'Terhapus!').then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        SwalHelper.showError('Tidak dapat menghapus projek.', 'Gagal');
                    }
                });
            }
        });
    });

    // Cetak Rapor P5 Siswa
    let currentPrintData = null;
    $('.btnCetakP5').on('click', function() {
        currentPrintData = {
            student_id: $(this).data('student-id'),
            student_name: $(this).data('student-name'),
            class_id: $(this).data('class-id'),
            fst_id: $(this).data('fst-id')
        };
        $('#print_student_name_label').text(currentPrintData.student_name);
        $('#modalPrintDate').modal('show');
    });

    $('#btnProsesCetakP5').on('click', function() {
        if (!currentPrintData) return;

        let tglPrint = $('#print_tgl_p5').val();
        $('#modalPrintDate').modal('hide');

        Swal.fire({
            title: 'Membuat Dokumen Rapor P5...',
            text: 'Menyusun capaian dimensi & QR code verifikasi...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '{{ route("p5.cetak") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                student_id: currentPrintData.student_id,
                class_id: currentPrintData.class_id,
                fst_id: currentPrintData.fst_id,
                tgl_print: tglPrint
            },
            success: function(res) {
                Swal.close();
                if (res.pdf_url) {
                    window.open(res.pdf_url, '_blank');
                } else {
                    SwalHelper.showError('URL PDF tidak diterima dari server.', 'Gagal');
                }
            },
            error: function(err) {
                let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Terjadi kegagalan pembuatan PDF.';
                SwalHelper.showError(msg, 'Error');
            }
        });
    });
});
</script>
@endsection
