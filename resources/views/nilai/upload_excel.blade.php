@extends('layouts.app')

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-file-excel text-success mr-2"></i>Upload Nilai Mapel (Excel)
                </h1>
                <p class="text-muted small mb-0">Pengisian nilai praktis berbasis berkas Excel (.xlsx) dengan lembar kerja (sheet) per kelas.</p>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('value.index') }}">Asesmen Sumatif</a></li>
                    <li class="breadcrumb-item active">Upload Excel</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- Panduan Singkat Banner -->
        <div class="card card-outline card-success shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-start">
                    <div class="mr-3 text-success">
                        <i class="fas fa-lightbulb fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-dark mb-1">Cara Cepat Pengisian Nilai Guru Mata Pelajaran:</h6>
                        <ol class="small text-muted mb-0 pl-3">
                            <li>Pilih <strong>Mata Pelajaran</strong> dan <strong>Periode Semester</strong> yang akan diisi nilainya.</li>
                            <li>Klik tombol <strong>"Unduh Format Excel Siswa"</strong>. Sistem akan menyiapkan berkas Excel yang diawali sheet <strong>Daftar TP (Tujuan Pembelajaran)</strong> di tab pertama sebagai acuan kompetensi bersama, lalu diikuti sheet per kelas yang berisi daftar siswa.</li>
                            <li>Buka berkas di komputer / HP Anda, isi nilai pada kolom <strong>Sumatif 1 s.d 10 (sesuai TP), STS, dan SAS</strong> (skala 0 - 100), lalu simpan.</li>
                            <li>Unggah kembali berkas Excel tersebut pada form di samping. Seluruh nilai di semua sheet kelas akan langsung terimpor secara otomatis!</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Langkah 1 & 2: Pilih Mapel & Download Template -->
            <div class="col-lg-5 col-md-12 mb-3">
                <div class="card card-primary card-outline shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark m-0">
                            <span class="badge badge-primary mr-1">Langkah 1 & 2</span> Pilih Mapel & Unduh Format
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <form id="templateForm">
                            <div class="form-group mb-3">
                                <label for="mapel_id" class="font-weight-bold">
                                    <i class="fas fa-book text-primary mr-1"></i>Mata Pelajaran:
                                </label>
                                <select name="mapel_id" id="mapel_id" class="form-control font-weight-bold" required>
                                    <option value="" selected disabled>-- Pilih Mata Pelajaran --</option>
                                    @foreach($mapelList as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->nama_mapel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="fst_id" class="font-weight-bold">
                                    <i class="fas fa-calendar-alt text-primary mr-1"></i>Fase / Semester / Tahun Ajaran:
                                </label>
                                <select name="fst_id" id="fst_id" class="form-control" required>
                                    @foreach($fstList as $fst)
                                        <option value="{{ $fst->id }}">
                                            Tahun Ajaran {{ $fst->tahun_ajaran }} - Semester {{ $fst->semester }} (Fase {{ $fst->fase }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label for="class_scope" class="font-weight-bold">
                                    <i class="fas fa-school text-primary mr-1"></i>Lingkup Kelas dalam Template:
                                </label>
                                <select name="class_id" id="class_scope" class="form-control">
                                    <option value="all" selected>🌟 Semua Kelas Sekaligus (Multi-Sheet / 1 Tab per Kelas)</option>
                                    <optgroup label="Atau Unduh 1 Kelas Spesifik:">
                                        @foreach($classList as $cls)
                                            <option value="{{ $cls->id }}">Kelas {{ $cls->class_name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <small class="form-text text-muted">
                                    Pilih opsi <em>Semua Kelas</em> untuk mendapatkan 1 file Excel yang langsung memiliki tab untuk setiap kelas.
                                </small>
                            </div>

                            <button type="button" class="btn btn-success btn-block font-weight-bold py-2 shadow-sm" id="btnDownloadTemplate">
                                <i class="fas fa-file-excel mr-2 fa-lg"></i> Unduh Format Excel Siswa (.xlsx)
                            </button>
                        </form>
                    </div>
                    <div class="card-footer bg-light text-muted small py-2">
                        <i class="fas fa-info-circle mr-1"></i> Format Excel resmi SiNilai kompatibel dengan Microsoft Excel 2010+, Google Spreadsheet, dan WPS Office.
                    </div>
                </div>
            </div>

            <!-- Langkah 3: Upload File Excel -->
            <div class="col-lg-7 col-md-12 mb-3">
                <div class="card card-success card-outline shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark m-0">
                            <span class="badge badge-success mr-1">Langkah 3</span> Unggah Berkas Nilai (.xlsx)
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <form id="uploadForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="mapel_id" id="upload_mapel_id">
                            <input type="hidden" name="fst_id" id="upload_fst_id">

                            <!-- Target info badge -->
                            <div class="alert alert-light border mb-3 py-2 px-3">
                                <div class="row small">
                                    <div class="col-sm-6">
                                        <strong class="text-muted d-block">Target Mata Pelajaran:</strong>
                                        <span class="font-weight-bold text-primary" id="badgeTargetMapel">Belum Dipilih di Langkah 1</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong class="text-muted d-block">Target Periode:</strong>
                                        <span class="font-weight-bold text-dark" id="badgeTargetFst">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="excel_file" class="font-weight-bold">
                                    Pilih Berkas Excel Hasil Pengisian:
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xlsx, .xls" required>
                                    <label class="custom-file-label" for="excel_file" id="fileLabel">Pilih file .xlsx / .xls...</label>
                                </div>
                                <small class="form-text text-muted">
                                    Maksimal ukuran file: 10 MB. Seluruh sheet kelas di dalam file akan diproses otomatis.
                                </small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold d-block">Metode Pembaruan Nilai:</label>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="overwrite_1" name="overwrite" value="1" class="custom-control-input" checked>
                                    <label class="custom-control-label font-weight-normal" for="overwrite_1">
                                        Timpa nilai yang sudah ada (Overwrite)
                                    </label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="overwrite_0" name="overwrite" value="0" class="custom-control-input">
                                    <label class="custom-control-label font-weight-normal" for="overwrite_0">
                                        Hanya isi kolom nilai yang masih kosong di web
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm" id="btnSubmitUpload">
                                <i class="fas fa-cloud-upload-alt mr-2 fa-lg"></i> Proses & Simpan Nilai ke Rapor
                            </button>
                        </form>
                    </div>
                    <div class="card-footer bg-light text-muted small py-2">
                        <i class="fas fa-shield-alt mr-1"></i> Setiap nilai yang diimpor akan tercatat secara otomatis pada log audit sistem untuk menjamin akuntabilitas data.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script type="module">
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Update target badges saat user memilih mapel atau semester
        function syncTargetInfo() {
            let mapelText = $('#mapel_id option:selected').text();
            let mapelVal  = $('#mapel_id').val();
            let fstText   = $('#fst_id option:selected').text();
            let fstVal    = $('#fst_id').val();

            if (mapelVal) {
                $('#badgeTargetMapel').text(mapelText);
                $('#upload_mapel_id').val(mapelVal);
            } else {
                $('#badgeTargetMapel').text('Belum Dipilih di Langkah 1');
                $('#upload_mapel_id').val('');
            }

            if (fstVal) {
                $('#badgeTargetFst').text(fstText);
                $('#upload_fst_id').val(fstVal);
            }
        }

        $('#mapel_id, #fst_id').on('change', syncTargetInfo);
        syncTargetInfo();

        // Tampilkan nama file saat dipilih
        $('#excel_file').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $('#fileLabel').text(fileName || 'Pilih file .xlsx / .xls...');
        });

        // Handler Download Template
        $('#btnDownloadTemplate').on('click', function() {
            let mapelId = $('#mapel_id').val();
            let fstId   = $('#fst_id').val();
            let classId = $('#class_scope').val();

            if (!mapelId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Mapel',
                    text: 'Silakan pilih Mata Pelajaran terlebih dahulu sebelum mengunduh format Excel.'
                });
                return;
            }

            let downloadUrl = "{{ route('nilai_import.download') }}?mapel_id=" + mapelId + "&fst_id=" + fstId + "&class_id=" + classId;
            window.location.href = downloadUrl;
        });

        // Handler Submit Upload Form
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();

            let mapelId = $('#upload_mapel_id').val();
            let fstId   = $('#upload_fst_id').val();
            let fileInput = $('#excel_file')[0];

            if (!mapelId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Mapel Terlebih Dahulu',
                    text: 'Silakan tentukan Mata Pelajaran pada kolom Langkah 1 di sebelah kiri.'
                });
                return;
            }

            if (!fileInput.files || fileInput.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Berkas Excel',
                    text: 'Silakan pilih berkas Excel (.xlsx) yang sudah diisi nilai.'
                });
                return;
            }

            let mapelName = $('#badgeTargetMapel').text();
            let formData = new FormData(this);

            Swal.fire({
                title: 'Proses Impor Nilai Excel?',
                html: `Sistem akan membaca seluruh lembar kerja kelas di berkas Excel untuk Mata Pelajaran:<br><strong>${mapelName}</strong>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-file-import mr-1"></i> Ya, Mulai Impor',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengimpor Nilai Siswa...',
                        html: 'Sedang membaca seluruh sheet kelas dan mencocokkan data siswa.<br>Mohon tunggu sebentar...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('nilai_import.process') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            let summaryHtml = `<p class="mb-3 font-weight-bold text-success">${res.message}</p>`;

                            if (res.sheet_summaries && res.sheet_summaries.length > 0) {
                                summaryHtml += `
                                    <div class="table-responsive" style="max-height: 250px;">
                                        <table class="table table-sm table-striped text-left small border mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Kelas / Sheet</th>
                                                    <th class="text-center">Berhasil</th>
                                                    <th class="text-center">Dilewati</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                `;

                                res.sheet_summaries.forEach(function(s) {
                                    summaryHtml += `
                                        <tr>
                                            <td><strong>${s.class_name}</strong></td>
                                            <td class="text-center text-success font-weight-bold">${s.imported} siswa</td>
                                            <td class="text-center text-muted">${s.skipped}</td>
                                        </tr>
                                    `;
                                });

                                summaryHtml += `</tbody></table></div>`;
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Impor Berhasil Selesai!',
                                html: summaryHtml,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#excel_file').val('');
                                $('#fileLabel').text('Pilih file .xlsx / .xls...');
                            });
                        },
                        error: function(xhr) {
                            let errMsg = xhr.responseJSON?.message || 'Gagal memproses berkas Excel.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Impor Nilai',
                                text: errMsg,
                                confirmButtonText: 'Tutup'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
