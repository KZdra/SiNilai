@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Data Sekolah') }}</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body p-2">
                            <form id="form-sekolah">
                                <input type="hidden" name="id" id="id" value="{{$sekolah->id ?? ''}}">
                                <div class="form-group">
                                    <label for="nama_sekolah">Nama Sekolah</label>
                                    <input type="text" class="form-control" id="nama_sekolah" name="nama_sekolah"
                                        value="{{ $sekolah->nama_sekolah ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="npsn">NPSN Sekolah</label>
                                    <input type="text" class="form-control" id="npsn" name="npsn"
                                        value="{{ $sekolah->npsn ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="nss">NSS Sekolah</label>
                                    <input type="text" class="form-control" id="nss" name="nss"
                                        value="{{ $sekolah->nss ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_sekolah">Alamat Sekolah</label>
                                    <input type="text" class="form-control" id="alamat_sekolah" name="alamat_sekolah"
                                        value="{{ $sekolah->alamat_sekolah ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="kode_pos">Kode Pos</label>
                                    <input type="number" class="form-control" id="kode_pos" name="kode_pos"
                                        value="{{ $sekolah->kode_pos ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="desa_kelurahan">Desa / Kelurahan</label>
                                    <input type="text" class="form-control" id="desa_kelurahan" name="desa_kelurahan"
                                        value="{{ $sekolah->desa_kelurahan ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="kecamatan">Kecamatan</label>
                                    <input type="text" class="form-control" id="kecamatan" name="kecamatan"
                                        value="{{ $sekolah->kecamatan ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="kabupaten_kota">Kabupaten / Kota</label>
                                    <input type="text" class="form-control" id="kabupaten_kota" name="kabupaten_kota"
                                        value="{{ $sekolah->kabupaten_kota ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="provinsi">Provinsi</label>
                                    <input type="text" class="form-control" id="provinsi" name="provinsi"
                                        value="{{ $sekolah->provinsi ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="website">Website</label>
                                    <input type="text" class="form-control" id="website" name="website"
                                        value="{{ $sekolah->website ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ $sekolah->email ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="nama_kepala_sekolah">Nama Kepala Sekolah</label>
                                    <input type="text" class="form-control" id="nama_kepala_sekolah"
                                        name="nama_kepala_sekolah" value="{{ $sekolah->nama_kepala_sekolah ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="nip_kepala_sekolah">NIP Kepala Sekolah</label>
                                    <input type="text" class="form-control" id="nip_kepala_sekolah"
                                        name="nip_kepala_sekolah" value="{{ $sekolah->nip_kepala_sekolah ?? '' }}">
                                </div>
                                <button type="submit" class="btn btn-success">Simpan Data</button>
                                <a class="btn btn-danger" id="clearAll" 
                                style="display: none;" data-id="{{$sekolah->id ?? ''}}">Hapus Data</a>
                            </form>
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
                        SwalHelper.showSuccess(response.message);
                        setTimeout(function() {
                                        location
                                            .reload(); // Refresh halaman setelah berhasil
                                    }, 2000);
                    },
                    error: function(xhr) {
                        SwalHelper.showError(xhr.responseJSON?.message ||
                            "Gagal menyimpan data.")
                    }
                });
            });

            if ($('#id').val()) {
                $('#clearAll').show();
                $('#clearAll').click(function() {
                    let id = $(this).data('id');
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, Hapus!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/datasekolah/${id}`,
                                method: "DELETE",
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    });

                                    setTimeout(function() {
                                        location
                                            .reload(); // Refresh halaman setelah berhasil
                                    }, 2000);
                                },
                                error: function(r) {
                                    Swal.fire("Gagal!", "Terjadi kesalahan, coba lagi!",
                                        "error");
                                }
                            });
                        }
                    });
                })
            }
        });
    </script>
@endsection
