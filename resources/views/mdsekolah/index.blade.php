@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Data Sekolah') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body p-2">
                            <form>

                                <div class="form-group">
                                    <label for="nama_sekolah">Nama Sekolah</label>
                                    <input type="text" class="form-control" id="nama_sekolah" name="nama_sekolah"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="npsn">NPSN Sekolah</label>
                                    <input type="text" inputmode="numeric" class="form-control" id="npsn"
                                        name="npsn" required>
                                </div>
                                <div class="form-group">
                                    <label for="nss">NSS Sekolah</label>
                                    <input type="text" class="form-control" id="nss" name="nss" required>
                                </div>
                                <div class="form-group">
                                    <label for="alamat_sekolah">Alamat Sekolah</label>
                                    <input type="text" class="form-control" id="alamat_sekolah" name="alamat_sekolah"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="kode_pos">Kode Pos</label>
                                    <input type="number" inputmode="numeric" class="form-control" id="kode_pos"
                                        name="kode_pos" required>
                                </div>

                                <div class="form-group">
                                    <label for="desa_kelurahan">Desa / Kelurahan</label>
                                    <input type="text" class="form-control" id="desa_kelurahan" name="desa_kelurahan"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="kecamatan">Kecamatan</label>
                                    <input type="text" class="form-control" id="kecamatan" name="kecamatan" required>
                                </div>

                                <div class="form-group">
                                    <label for="kabupaten_kota">Kabupaten / Kota</label>
                                    <input type="text" class="form-control" id="kabupaten_kota" name="kabupaten_kota"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="provinsi">Provinsi</label>
                                    <input type="text" class="form-control" id="provinsi" name="provinsi" required>
                                </div>

                                <div class="form-group">
                                    <label for="website">Website</label>
                                    <input type="text" class="form-control" id="website" name="website" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="nama_kepala_sekolah">Nama Kepala Sekolah</label>
                                    <input type="text" class="form-control" id="nama_kepala_sekolah"
                                        name="nama_kepala_sekolah" required>
                                </div>
                                <div class="form-group">
                                    <label for="nip_kepala_sekolah">Nip Kepala Sekolah</label>
                                    <input type="text" class="form-control" id="nip_kepala_sekolah"
                                        name="nip_kepala_sekolah" required>
                                </div>
                                <button type="submit" class="btn btn-success">Simpan Data</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection
