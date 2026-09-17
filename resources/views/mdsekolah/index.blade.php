@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">
                        <i class="fas fa-school text-primary mr-2"></i>{{ __('Profil & Data Master Sekolah') }}
                    </h1>
                    <p class="text-muted small mb-0 mt-1">Identitas resmi lembaga, alamat operasional, dan pejabat penandatangan yang dicetak pada cover & lembar rapor.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item">Data Master</li>
                        <li class="breadcrumb-item active">Profil Sekolah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- ── PANDUAN, TIPS & PERINGATAN (UX HELPER) ────────────────────────── -->
            <div class="row">
                <div class="col-md-7 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100 mb-0" style="border-radius: 8px;">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title font-weight-bold text-info mb-0">
                                <i class="fas fa-lightbulb mr-2"></i>Tips Pengisian Profil & Identitas Sekolah
                            </h6>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-2 px-3 small text-secondary">
                            <ul class="pl-3 mb-0">
                                <li class="mb-1">
                                    <strong>Otomatisasi Dokumen Rapor:</strong> Nama sekolah, NPSN, dan alamat lengkap di bawah ini akan otomatis dicetak pada <strong>Kop Surat Lembar Nilai, Cover Rapor Siswa, Lembar Biodata</strong>, dan <strong>Leger Nilai</strong>.
                                </li>
                                <li class="mb-1">
                                    <strong>Kesesuaian Dapodik:</strong> Pastikan Nama Lembaga, NPSN, dan NSS sesuai dengan SK Izin Operasional Kemendikbudristek / Kemenag resmi.
                                </li>
                                <li>
                                    <strong>Kontak & Alamat:</strong> Lengkapi Kode Pos, Kecamatan, dan Kabupaten/Kota karena format baku rapor Kurikulum Merdeka mewajibkan identitas domisili sekolah secara utuh.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 mb-3">
                    <div class="card card-outline card-warning shadow-sm h-100 mb-0" style="border-radius: 8px;">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title font-weight-bold text-warning mb-0">
                                <i class="fas fa-signature mr-2"></i>Ketentuan Penandatangan Rapor
                            </h6>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-2 px-3 small text-secondary">
                            <ul class="pl-3 mb-0">
                                <li class="mb-1">
                                    <strong>Gelar Kepala Sekolah:</strong> Cantumkan nama lengkap beserta seluruh gelar resmi (contoh: <code class="text-dark font-weight-bold">Dr. H. Ahmad Sudrajat, M.M.Pd.</code>).
                                </li>
                                <li>
                                    <strong>NIP Kepala Sekolah:</strong> Untuk ASN cantumkan 18 digit NIP resmi. Jika sekolah swasta / yayasan atau belum ber-NIP, isikan nomor yayasan, NUPTK, atau tanda strip (<code class="text-dark font-weight-bold">-</code>).
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── FORM PROFIL SEKOLAH ─────────────────────────────────────────── -->
            <form id="form-sekolah">
                <input type="hidden" name="id" id="id" value="{{ $sekolah->id ?? '' }}">

                <div class="row">
                    <!-- Kartu 1: Identitas Inti Lembaga -->
                    <div class="col-lg-6 mb-3">
                        <div class="card shadow-sm border-0 h-100" style="border-radius: 10px;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-university text-primary mr-2"></i>1. Identitas Inti Lembaga
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_sekolah" class="font-weight-bold text-dark">
                                        Nama Sekolah <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-school text-muted"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="nama_sekolah" name="nama_sekolah"
                                            value="{{ $sekolah->nama_sekolah ?? '' }}" placeholder="Contoh: SMK ICB CINTA TEKNIKA" required>
                                    </div>
                                    <small class="form-text text-muted">Ditampilkan di header kop surat dan halaman depan rapor.</small>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="npsn" class="font-weight-bold text-dark">
                                                NPSN Sekolah <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-id-card text-muted"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="npsn" name="npsn"
                                                    value="{{ $sekolah->npsn ?? '' }}" placeholder="Contoh: 20219292" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="nss" class="font-weight-bold text-dark">NSS Sekolah</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="nss" name="nss"
                                                    value="{{ $sekolah->nss ?? '' }}" placeholder="Nomor Statistik Sekolah">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="website" class="font-weight-bold text-dark">Website Resmi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-globe text-muted"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="website" name="website"
                                            value="{{ $sekolah->website ?? '' }}" placeholder="Contoh: https://smkicb.sch.id">
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="email" class="font-weight-bold text-dark">Email Resmi Sekolah</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                        </div>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ $sekolah->email ?? '' }}" placeholder="Contoh: info@smkicb.sch.id">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu 2: Alamat & Wilayah Operasional -->
                    <div class="col-lg-6 mb-3">
                        <div class="card shadow-sm border-0 h-100" style="border-radius: 10px;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-map-marked-alt text-primary mr-2"></i>2. Alamat & Wilayah Operasional
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="alamat_sekolah" class="font-weight-bold text-dark">
                                        Alamat Jalan / Gedung <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="alamat_sekolah" name="alamat_sekolah"
                                            value="{{ $sekolah->alamat_sekolah ?? '' }}" placeholder="Contoh: Jl. Atlas Tengah No.2, Babakan Surabaya" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="desa_kelurahan" class="font-weight-bold text-dark">Desa / Kelurahan</label>
                                            <input type="text" class="form-control" id="desa_kelurahan" name="desa_kelurahan"
                                                value="{{ $sekolah->desa_kelurahan ?? '' }}" placeholder="Kelurahan">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="kecamatan" class="font-weight-bold text-dark">Kecamatan</label>
                                            <input type="text" class="form-control" id="kecamatan" name="kecamatan"
                                                value="{{ $sekolah->kecamatan ?? '' }}" placeholder="Kecamatan">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="kabupaten_kota" class="font-weight-bold text-dark">Kabupaten / Kota</label>
                                            <input type="text" class="form-control" id="kabupaten_kota" name="kabupaten_kota"
                                                value="{{ $sekolah->kabupaten_kota ?? '' }}" placeholder="Kota / Kab">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="provinsi" class="font-weight-bold text-dark">Provinsi</label>
                                            <input type="text" class="form-control" id="provinsi" name="provinsi"
                                                value="{{ $sekolah->provinsi ?? '' }}" placeholder="Provinsi">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="kode_pos" class="font-weight-bold text-dark">Kode Pos</label>
                                    <div class="input-group" style="max-width: 200px;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-mail-bulk text-muted"></i></span>
                                        </div>
                                        <input type="number" class="form-control" id="kode_pos" name="kode_pos"
                                            value="{{ $sekolah->kode_pos ?? '' }}" placeholder="40281">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu 3: Pejabat Penandatangan Rapor -->
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow-sm border-0" style="border-radius: 10px;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-signature text-primary mr-2"></i>3. Pejabat Penandatangan Dokumen & Rapor Resmi
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-md-0">
                                            <label for="nama_kepala_sekolah" class="font-weight-bold text-dark">
                                                Nama Lengkap Kepala Sekolah (Beserta Gelar) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-user-tie text-muted"></i></span>
                                                </div>
                                                <input type="text" class="form-control font-weight-bold" id="nama_kepala_sekolah"
                                                    name="nama_kepala_sekolah" value="{{ $sekolah->nama_kepala_sekolah ?? '' }}"
                                                    placeholder="Contoh: Drs. H. Ahmad Sudrajat, M.M.Pd." required>
                                            </div>
                                            <small class="form-text text-muted">Nama ini dicetak pada kolom tanda tangan kepala sekolah di buku rapor.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="nip_kepala_sekolah" class="font-weight-bold text-dark">
                                                NIP / NUPTK Kepala Sekolah
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-id-badge text-muted"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="nip_kepala_sekolah"
                                                    name="nip_kepala_sekolah" value="{{ $sekolah->nip_kepala_sekolah ?? '' }}"
                                                    placeholder="18 digit NIP atau tanda strip (-) jika bukan ASN">
                                            </div>
                                            <small class="form-text text-muted">Isikan strip (-) jika sekolah swasta atau tidak memiliki NIP resmi.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light py-3 d-flex justify-content-end align-items-center" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                                <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-2"></i> Simpan Perubahan Data Sekolah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            $("#form-sekolah").submit(function(event) {
                event.preventDefault();
                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('datasekolah.store') }}",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Disimpan!',
                            text: response.message || 'Profil data sekolah berhasil diperbarui.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan Data',
                            text: xhr.responseJSON?.message || "Terjadi kesalahan saat menyimpan data sekolah."
                        });
                    }
                });
            });
        });
    </script>
@endsection

