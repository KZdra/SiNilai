@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-trophy text-warning mr-2"></i>{{ __('Master Data Ekstrakurikuler') }}
                    </h1>
                    <p class="text-muted small mb-0">Kelola daftar kegiatan ekstrakurikuler dan template predikat capaian rapor sekolah.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active">Data Ekstrakurikuler</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline card-outline-tabs shadow-sm">
                <div class="card-header p-0 border-bottom-0 bg-light">
                    <ul class="nav nav-tabs" id="eskulTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold py-3 px-4" id="tab-eskul-link" data-toggle="pill" href="#tab-eskul" role="tab" aria-controls="tab-eskul" aria-selected="true">
                                <i class="fas fa-running text-primary mr-2"></i> Daftar Ekstrakurikuler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold py-3 px-4" id="tab-predikat-link" data-toggle="pill" href="#tab-predikat" role="tab" aria-controls="tab-predikat" aria-selected="false">
                                <i class="fas fa-magic text-warning mr-2"></i> Template Predikat & Narasi Rapor
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="eskulTabContent">

                        <!-- ── TAB 1: DAFTAR EKSTRAKURIKULER ── -->
                        <div class="tab-pane fade show active" id="tab-eskul" role="tabpanel" aria-labelledby="tab-eskul-link">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-1">Daftar Kegiatan Ekstrakurikuler</h5>
                                    <p class="text-muted small mb-0">Kegiatan eskul yang dapat diikuti oleh siswa di sekolah.</p>
                                </div>
                                <button class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="addEskulBtn">
                                    <i class="fas fa-plus mr-1"></i> Tambah Ekstrakurikuler
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered w-100" id="eskulTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">No</th>
                                            <th>Nama Ekstrakurikuler</th>
                                            <th style="width: 120px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ── TAB 2: TEMPLATE PREDIKAT & NARASI RAPOR ── -->
                        <div class="tab-pane fade" id="tab-predikat" role="tabpanel" aria-labelledby="tab-predikat-link">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-1">Master Predikat & Template Narasi</h5>
                                    <p class="text-muted small mb-0">Digunakan sebagai tombol cepat (*smart preset*) saat pengisian nilai eskul di rapor.</p>
                                </div>
                                <button class="btn btn-success btn-sm font-weight-bold shadow-sm" id="addPredikatBtn">
                                    <i class="fas fa-plus mr-1"></i> Tambah Predikat Rapor
                                </button>
                            </div>

                            <div class="alert alert-info border-0 shadow-sm py-2 px-3 mb-3 small">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Panduan Placeholder:</strong> Gunakan variabel <code>{eskul}</code> pada kolom kalimat template. Sistem akan otomatis mengganti <code>{eskul}</code> dengan nama eskul siswa saat tombol predikat diklik.
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered w-100" id="predikatTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">No</th>
                                            <th style="width: 90px;" class="text-center">Kode</th>
                                            <th style="width: 150px;">Predikat / Nama</th>
                                            <th>Template Narasi Rapor</th>
                                            <th style="width: 80px;" class="text-center">Urutan</th>
                                            <th style="width: 120px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah & Edit Eskul -->
    <div class="modal fade" id="eskulModal" tabindex="-1" role="dialog" aria-labelledby="eskulModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="eskulModalLabel">Input Ekstrakurikuler</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="eskulForm">
                    <div class="modal-body py-3">
                        <input type="hidden" name="eskul_id" id="eskul_id">
                        <div class="form-group mb-2">
                            <label for="nama_eskul" class="font-weight-bold small text-muted text-uppercase mb-1">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                            <input type="text" maxlength="100" class="form-control font-weight-bold" id="nama_eskul" name="nama_eskul" placeholder="Contoh: Pramuka, Paskibra, Futsal..." required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah & Edit Predikat Eskul -->
    <div class="modal fade" id="predikatModal" tabindex="-1" role="dialog" aria-labelledby="predikatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="predikatModalLabel">Input Template Predikat</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="predikatForm">
                    <div class="modal-body py-3">
                        <input type="hidden" name="predikat_id" id="predikat_id">

                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="form-group mb-0">
                                    <label for="predikat_kode" class="font-weight-bold small text-muted text-uppercase mb-1">Kode <span class="text-danger">*</span></label>
                                    <input type="text" maxlength="10" class="form-control font-weight-bold text-uppercase" id="predikat_kode" placeholder="Misal: A, B" required>
                                </div>
                            </div>
                            <div class="col-md-8 mb-2">
                                <div class="form-group mb-0">
                                    <label for="predikat_nama" class="font-weight-bold small text-muted text-uppercase mb-1">Nama Predikat <span class="text-danger">*</span></label>
                                    <input type="text" maxlength="50" class="form-control font-weight-bold" id="predikat_nama" placeholder="Misal: Sangat Baik, Baik" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6 mb-2">
                                <div class="form-group mb-0">
                                    <label for="predikat_badge" class="font-weight-bold small text-muted text-uppercase mb-1">Warna Badge</label>
                                    <select id="predikat_badge" class="form-control font-weight-bold">
                                        <option value="success" class="text-success font-weight-bold">Hijau (Success)</option>
                                        <option value="primary" class="text-primary font-weight-bold">Biru (Primary)</option>
                                        <option value="warning" class="text-warning font-weight-bold">Kuning (Warning)</option>
                                        <option value="secondary" class="text-secondary font-weight-bold">Abu-abu (Secondary)</option>
                                        <option value="info" class="text-info font-weight-bold">Cyan (Info)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="form-group mb-0">
                                    <label for="predikat_urutan" class="font-weight-bold small text-muted text-uppercase mb-1">Urutan Tombol</label>
                                    <input type="number" min="1" max="99" class="form-control" id="predikat_urutan" value="1">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-2 mb-1">
                            <label for="predikat_narasi" class="font-weight-bold small text-muted text-uppercase mb-1">
                                Template Narasi Rapor <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="predikat_narasi" rows="3" placeholder="Gunakan {eskul} untuk nama kegiatan eskul secara otomatis..." required></textarea>
                            <small class="form-text text-muted">
                                Contoh: <em>Sangat aktif, berdisiplin tinggi, dan menunjukkan capaian prestasi yang memuaskan dalam kegiatan {eskul}.</em>
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold px-3">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // ── Datatable 1: Ekstrakurikuler ──────────────────────
            var eskulTable = $('#eskulTable').DataTable({
                "responsive": true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('meskul.getdata') }}",
                    "type": "GET",
                    "error": function() {
                        SwalHelper.showError('Gagal mengambil data ekstrakurikuler.');
                    }
                },
                "columns": [
                    {
                        "data": null,
                        "orderable": false,
                        "className": "text-center font-weight-bold text-muted",
                        "render": function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        "data": "nama_eskul",
                        "className": "font-weight-bold text-dark"
                    },
                    {
                        "data": null,
                        "orderable": false,
                        "className": "text-center",
                        "render": function(data, type, row) {
                            return `
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info editEskulBtn" data-id="${row.id}" data-nama="${row.nama_eskul}" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger delEskulBtn" data-id="${row.id}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            $("#addEskulBtn").on("click", function() {
                $('#eskul_id').val('');
                $('#nama_eskul').val('');
                $('#eskulModalLabel').text('Tambah Ekstrakurikuler');
                $('#eskulModal').modal('show');
            });

            $('#eskulForm').submit(function(e) {
                e.preventDefault();
                let id = $('#eskul_id').val();
                let url = id ? `/meskul/${id}` : "{{ route('meskul.store') }}";
                let method = id ? "PUT" : "POST";
                let data = {
                    nama_eskul: $('#nama_eskul').val().trim()
                };

                apiService(url, method, data)
                    .then(response => {
                        SwalHelper.showSuccess(response.message);
                        $('#eskulModal').modal('hide');
                        eskulTable.ajax.reload(null, false);
                    })
                    .catch(err => {
                        SwalHelper.showError(err.responseJSON?.message || 'Terjadi kesalahan.');
                    });
            });

            $("#eskulTable").on("click", ".editEskulBtn", function() {
                let id = $(this).data('id');
                let nama = $(this).data('nama');
                $('#eskul_id').val(id);
                $('#nama_eskul').val(nama);
                $('#eskulModalLabel').text('Edit Ekstrakurikuler');
                $('#eskulModal').modal('show');
            });

            $("#eskulTable").on("click", ".delEskulBtn", function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: "Hapus Ekstrakurikuler?",
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Hapus!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        apiService(`/meskul/${id}`, 'DELETE')
                            .then(response => {
                                SwalHelper.showSuccess(response.message);
                                eskulTable.ajax.reload(null, false);
                            })
                            .catch(err => {
                                SwalHelper.showError(err.responseJSON?.message || 'Gagal menghapus data.');
                            });
                    }
                });
            });


            // ── Datatable 2: Predikat & Narasi ────────────────────
            var predikatTable = null;

            $('#tab-predikat-link').on('shown.bs.tab', function() {
                if (!predikatTable) {
                    predikatTable = $('#predikatTable').DataTable({
                        "responsive": true,
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": "{{ route('meskul.predikat.getdata') }}",
                            "type": "GET",
                            "error": function() {
                                SwalHelper.showError('Gagal mengambil data template predikat.');
                            }
                        },
                        "columns": [
                            {
                                "data": null,
                                "orderable": false,
                                "className": "text-center font-weight-bold text-muted",
                                "render": function(data, type, row, meta) {
                                    return meta.row + meta.settings._iDisplayStart + 1;
                                }
                            },
                            {
                                "data": "kode",
                                "className": "text-center",
                                "render": function(data, type, row) {
                                    const badge = row.badge_color || 'primary';
                                    return `<span class="badge badge-${badge} font-weight-bold px-2 py-1">${data}</span>`;
                                }
                            },
                            {
                                "data": "nama",
                                "className": "font-weight-bold text-dark"
                            },
                            {
                                "data": "template_narasi",
                                "render": function(data) {
                                    if (!data) return '-';
                                    return data.replace(/\{eskul\}/g, '<span class="badge badge-light border text-primary font-weight-bold px-1">{eskul}</span>');
                                }
                            },
                            {
                                "data": "urutan",
                                "className": "text-center font-weight-bold"
                            },
                            {
                                "data": null,
                                "orderable": false,
                                "className": "text-center",
                                "render": function(data, type, row) {
                                    return `
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info editPredikatBtn" 
                                                data-id="${row.id}" 
                                                data-kode="${row.kode}" 
                                                data-nama="${row.nama}" 
                                                data-badge="${row.badge_color || 'primary'}" 
                                                data-narasi="${row.template_narasi}" 
                                                data-urutan="${row.urutan}" 
                                                title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger delPredikatBtn" data-id="${row.id}" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    `;
                                }
                            }
                        ]
                    });
                } else {
                    predikatTable.columns.adjust().responsive.recalc();
                }
            });

            $("#addPredikatBtn").on("click", function() {
                $('#predikat_id').val('');
                $('#predikat_kode').val('');
                $('#predikat_nama').val('');
                $('#predikat_badge').val('primary');
                $('#predikat_narasi').val('');
                $('#predikat_urutan').val('1');
                $('#predikatModalLabel').text('Tambah Template Predikat Rapor');
                $('#predikatModal').modal('show');
            });

            $('#predikatForm').submit(function(e) {
                e.preventDefault();
                let id = $('#predikat_id').val();
                let url = id ? `/meskul/predikat/${id}` : "{{ route('meskul.predikat.store') }}";
                let method = id ? "PUT" : "POST";
                let data = {
                    kode: $('#predikat_kode').val().trim(),
                    nama: $('#predikat_nama').val().trim(),
                    badge_color: $('#predikat_badge').val(),
                    template_narasi: $('#predikat_narasi').val().trim(),
                    urutan: $('#predikat_urutan').val()
                };

                apiService(url, method, data)
                    .then(response => {
                        SwalHelper.showSuccess(response.message);
                        $('#predikatModal').modal('hide');
                        if (predikatTable) predikatTable.ajax.reload(null, false);
                    })
                    .catch(err => {
                        SwalHelper.showError(err.responseJSON?.message || 'Terjadi kesalahan.');
                    });
            });

            $(document).on("click", ".editPredikatBtn", function() {
                $('#predikat_id').val($(this).data('id'));
                $('#predikat_kode').val($(this).data('kode'));
                $('#predikat_nama').val($(this).data('nama'));
                $('#predikat_badge').val($(this).data('badge'));
                $('#predikat_narasi').val($(this).data('narasi'));
                $('#predikat_urutan').val($(this).data('urutan'));
                $('#predikatModalLabel').text('Edit Template Predikat Rapor');
                $('#predikatModal').modal('show');
            });

            $(document).on("click", ".delPredikatBtn", function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: "Hapus Template Predikat?",
                    text: "Predikat ini tidak akan muncul lagi di tombol cepat rapor.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Hapus!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        apiService(`/meskul/predikat/${id}`, 'DELETE')
                            .then(response => {
                                SwalHelper.showSuccess(response.message);
                                if (predikatTable) predikatTable.ajax.reload(null, false);
                            })
                            .catch(err => {
                                SwalHelper.showError(err.responseJSON?.message || 'Gagal menghapus template predikat.');
                            });
                    }
                });
            });
        });
    </script>
@endsection
