@extends('layouts.app')

@section('styles')
<!-- jsTree CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<style>
    /* Styling kustom untuk jsTree agar selaras dengan AdminLTE */
    .jstree-default .jstree-wholerow-clicked {
        background: #e0e7ff !important;
        border-radius: 4px;
    }
    .jstree-default .jstree-wholerow-hovered {
        background: #f1f5f9 !important;
        border-radius: 4px;
    }
    .jstree-default .jstree-clicked {
        color: #1e293b !important;
        font-weight: 600;
    }
    .jstree-anchor {
        font-family: inherit;
        font-size: 0.95rem;
    }
    .tree-container {
        min-height: 480px;
        max-height: 650px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #ffffff;
        padding: 12px;
    }
    .pdf-preview-box {
        min-height: 480px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
</style>
@endsection

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-folder-open text-warning mr-2"></i>Penjelajah Arsip Raport
                </h1>
                <p class="text-muted small mb-0">Eksplorasi hierarkis berkas cetak rapor siswa di penyimpanan storage menggunakan pohon navigasi jsTree.</p>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('nilaiakhir.index') }}">Nilai Akhir & Rapor</a></li>
                    <li class="breadcrumb-item active">Arsip Raport (jsTree)</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- Stat Row -->
        <div class="row mb-3">
            <div class="col-md-6 col-12">
                <div class="info-box shadow-sm mb-3">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-file-pdf"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Total Berkas Raport Tersimpan</span>
                        <span class="info-box-number h5 font-weight-bold" id="statTotalFiles">{{ $stats['total_files'] }} File PDF</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="info-box shadow-sm mb-3">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-database"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Kapasitas Storage Raport</span>
                        <span class="info-box-number h5 font-weight-bold">{{ $stats['total_size'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Explorer Split Pane -->
        <div class="row">
            <!-- Kolom Kiri: Pohon Direktori jsTree -->
            <div class="col-lg-5 col-md-12 mb-3">
                <div class="card card-outline card-primary shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark m-0">
                            <i class="fas fa-sitemap text-primary mr-1"></i> Direktori Berkas (jsTree)
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <!-- Toolbar Pencarian & Kontrol Tree -->
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="treeSearchInput" class="form-control" placeholder="Cari nama siswa, kelas, atau semester...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="btnClearSearch" title="Reset Pencarian">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-default" id="btnExpandAll" title="Buka Semua Folder">
                                    <i class="fas fa-expand-arrows-alt mr-1"></i> Buka Semua
                                </button>
                                <button type="button" class="btn btn-default" id="btnCollapseAll" title="Tutup Semua Folder">
                                    <i class="fas fa-compress-arrows-alt mr-1"></i> Tutup Semua
                                </button>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnRefreshTree" title="Muat Ulang Direktori">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh
                            </button>
                        </div>

                        <!-- jsTree Container -->
                        <div id="raportTree" class="tree-container">
                            <div class="text-center py-5 text-muted" id="treeLoader">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary d-block"></i>
                                Memuat struktur berkas dari storage...
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light py-2 text-muted small">
                        <i class="fas fa-info-circle mr-1"></i> Klik pada ikon berkas PDF untuk menampilkan pratinjau dan opsi unduh.
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pratinjau & Informasi Berkas -->
            <div class="col-lg-7 col-md-12 mb-3">
                <!-- State Kosong / Belum Memilih File -->
                <div class="card card-outline card-secondary shadow-sm h-100" id="emptyPreviewCard">
                    <div class="card-body pdf-preview-box text-center p-5">
                        <i class="fas fa-file-pdf fa-4x text-muted mb-3"></i>
                        <h5 class="text-secondary font-weight-bold">Belum Ada Berkas yang Dipilih</h5>
                        <p class="text-muted small max-w-sm mb-0">
                            Silakan pilih salah satu berkas rapor siswa (<code>.pdf</code>) pada pohon folder di sebelah kiri untuk melihat informasi lengkap dan pratinjau dokumen langsung.
                        </p>
                    </div>
                </div>

                <!-- State Aktif: File Terpilih -->
                <div class="card card-outline card-danger shadow-sm h-100 d-none" id="fileDetailCard">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h5 class="card-title font-weight-bold text-dark m-0 d-flex align-items-center">
                                <i class="fas fa-file-pdf text-danger fa-lg mr-2"></i>
                                <span id="previewStudentName">-</span>
                            </h5>
                        </div>
                        <div class="mt-2 mt-sm-0">
                            <a href="#" target="_blank" class="btn btn-sm btn-outline-primary font-weight-bold mr-1" id="btnOpenTab">
                                <i class="fas fa-external-link-alt mr-1"></i> Buka Tab Baru
                            </a>
                            <a href="#" class="btn btn-sm btn-success font-weight-bold mr-1" id="btnDownloadPdf">
                                <i class="fas fa-download mr-1"></i> Unduh PDF
                            </a>
                            @if (Auth::user()->role_id == 1)
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnDeletePdf" title="Hapus Berkas dari Server">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <!-- Metadata Card Mini -->
                        <div class="bg-light p-3 rounded mb-3 border">
                            <div class="row small">
                                <div class="col-sm-6 mb-1">
                                    <strong class="text-muted d-block">Nama Berkas:</strong>
                                    <span class="font-weight-bold text-dark" id="metaFileName">-</span>
                                </div>
                                <div class="col-sm-6 mb-1">
                                    <strong class="text-muted d-block">Ukuran Dokumen:</strong>
                                    <span class="badge badge-secondary px-2 py-1" id="metaFileSize">-</span>
                                </div>
                                <div class="col-sm-6 mb-1">
                                    <strong class="text-muted d-block">Lokasi Penyimpanan:</strong>
                                    <code class="text-dark font-weight-bold" id="metaFilePath">-</code>
                                </div>
                                <div class="col-sm-6 mb-1">
                                    <strong class="text-muted d-block">Waktu Pembuatan / Modifikasi:</strong>
                                    <span class="text-muted" id="metaFileModified">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Iframe Preview PDF -->
                        <div class="embed-responsive" style="height: 520px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; background: #525659;">
                            <iframe id="pdfPreviewFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<!-- jsTree JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js" defer></script>
<script type="module">
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        let currentSelectedFile = null;

        // Inisialisasi jsTree
        function initTree() {
            $('#raportTree').jstree({
                'core': {
                    'data': {
                        'url': "{{ route('raport_explorer.tree') }}",
                        'dataType': 'json',
                        'data': function(node) {
                            return { 'id': node.id };
                        }
                    },
                    'themes': {
                        'name': 'default',
                        'responsive': true,
                        'dots': true,
                        'icons': true
                    },
                    'multiple': false
                },
                'plugins': ['search', 'types', 'wholerow'],
                'types': {
                    'default': {
                        'icon': 'fas fa-folder text-warning'
                    },
                    'root': {
                        'icon': 'fas fa-archive text-primary'
                    },
                    'folder': {
                        'icon': 'fas fa-folder text-warning'
                    },
                    'pdf': {
                        'icon': 'fas fa-file-pdf text-danger'
                    }
                },
                'search': {
                    'case_insensitive': true,
                    'show_only_matches': true
                }
            }).on('loaded.jstree', function() {
                $('#treeLoader').remove();
            }).on('select_node.jstree', function(e, data) {
                let node = data.node;
                if (node && node.data && node.data.is_file) {
                    displayFilePreview(node.data);
                }
            });
        }

        initTree();

        // Tampilkan Pratinjau PDF
        function displayFilePreview(fileData) {
            currentSelectedFile = fileData;

            $('#previewStudentName').text(fileData.student_name || fileData.filename);
            $('#metaFileName').text(fileData.filename);
            $('#metaFileSize').text(fileData.size);
            $('#metaFilePath').text(fileData.path);
            $('#metaFileModified').text(fileData.modified_at);

            $('#btnOpenTab').attr('href', fileData.url);
            $('#btnDownloadPdf').attr('href', fileData.download_url);

            $('#pdfPreviewFrame').attr('src', fileData.url);

            $('#emptyPreviewCard').addClass('d-none');
            $('#fileDetailCard').removeClass('d-none');
        }

        // Live Search Input Handler
        let searchTimeout = null;
        $('#treeSearchInput').on('keyup', function() {
            let val = $(this).val();
            if (searchTimeout) clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                $('#raportTree').jstree(true).search(val);
            }, 300);
        });

        $('#btnClearSearch').on('click', function() {
            $('#treeSearchInput').val('');
            $('#raportTree').jstree(true).clear_search();
        });

        // Kontrol Expand & Collapse
        $('#btnExpandAll').on('click', function() {
            $('#raportTree').jstree('open_all');
        });

        $('#btnCollapseAll').on('click', function() {
            $('#raportTree').jstree('close_all');
        });

        $('#btnRefreshTree').on('click', function() {
            $('#raportTree').jstree(true).refresh();
        });

        // Hapus Arsip Berkas PDF (Khusus Admin)
        $('#btnDeletePdf').on('click', function() {
            if (!currentSelectedFile) return;

            Swal.fire({
                title: 'Hapus Berkas Raport?',
                html: `Apakah Anda yakin ingin menghapus berkas:<br><strong>${currentSelectedFile.filename}</strong> dari penyimpanan storage server?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus berkas...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('raport_explorer.destroy') }}",
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            path: currentSelectedFile.path
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Reset preview ke kosong
                            $('#fileDetailCard').addClass('d-none');
                            $('#emptyPreviewCard').removeClass('d-none');
                            $('#pdfPreviewFrame').attr('src', '');
                            currentSelectedFile = null;

                            // Refresh tree
                            $('#raportTree').jstree(true).refresh();
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Gagal menghapus berkas rapor.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
