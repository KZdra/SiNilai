@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Nilai Akhir') }}</h1>
                    <button class="mt-2 btn btn-primary" id="pickClassBtn">Pilih Kelas</button>
                    <button class="mt-2 btn btn-success" id="expBtn1" style="display: none;"><i
                            class="fas fa-file-excel"></i> Export Nilai Akhir</button>
                    <button class="mt-2 btn btn-success" id="expBtn2" style="display: none;"><i class="fas fa-medal"></i>
                        Export Ranking Siswa</button>
                    <button class="mt-2 btn btn-info" id="expBtn4" style="display: none;"><i class="fas fa-print"></i>
                        Print Raport Berurutan</button>
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
                    <div class="card" id="pickClass" style="display: none">
                        <div class="card-body p-2">
                            <form id="filterForm">
                                <div class="form-group">
                                    <label for="class_id">Kelas</label>
                                    @if ($className)
                                        <select name="class_id" id="class_id" class="form-control" disabled>
                                            <option value="{{ Auth::user()->class_id }}" selected>{{ $className }}
                                            </option>
                                        </select>
                                    @else
                                        <select name="class_id" id="class_id" class="form-control">
                                            <option value="" selected disabled>Pilih Kelas</option>
                                            @foreach ($classList as $index => $class)
                                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-success">Submit</button>
                            </form>
                        </div>
                    </div>
                    <div class="card" id="pickFst" style="display: none;">
                        <div class="card-body p-2">
                            <form id="fstForm">
                                <div class="form-group">
                                    <label for="fst_id">Fase/Semester/Tahun Ajaran dan faseSemester</label>
                                    <select name="fst_id" id="fst_id" class="form-control">
                                        <option value="" selected disabled> Pilih Fase/Semester/Tahun Ajaran dan
                                            faseSemester</option>
                                        @foreach ($fstList as $index => $fst)
                                            <option value="{{ $fst->id }}">
                                                {{ ucwords($fst->fase) . '/' . $fst->semester . '/' . $fst->tahun_ajaran . '/' . ucfirst($fst->ta) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">Submit</button>
                            </form>
                        </div>
                    </div>
                    <div class="card" id="resultTable" style="display: none">
                        <div class="card-header">
                            <h5>Kelas: <span id="ClassSel"></span></h5>
                            <h5>Fase/Semester/Tahun: <span id="FstSel"></span></h5>
                            {{-- <h5>Tanggal Print: <input type="date" name="tgl_print" id="tgl_print" class="form-control"> --}}
                            </h5>
                            <button class="mt-2 btn btn-dark" id="expBtn3" style="display: none;" data-toggle="modal"
                                data-target="#SettingRaportCenter"><i class="fas fa-cog"></i> Pengaturan Raport</button>

                        </div>
                        <div class="card-body p-2">

                            <table class="table table-striped table-bordered" id="valueTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Nilai Rata Rata Semua Mapel</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>


                </div>
            </div>
            <!-- /.row -->
            <div class="modal fade" id="SettingRaportCenter" tabindex="-1" role="dialog"
                aria-labelledby="SettingRaportCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="SettingRaportLongTitle">Pengaturan Sebelum Print Raport</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="SetRaportForm">
                            <div class="modal-body">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="tanggalPrint" class="form-label">Tanggal Print</label>
                                        <input type="date" class="form-control" id="tanggalPrint" required />
                                    </div>

                                    <div class="mb-3">
                                        <label for="boxKeputusan" class="form-label">Box Keputusan</label>
                                        <textarea class="form-control" id="boxKeputusan" rows="3" placeholder="Bila Tidak Ada Tidak Usah Di Isikan"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </div>
            
            <!-- Modal Print Berurutan -->
            <div class="modal fade" id="BulkPrintModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Print Raport Berurutan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <h5 id="bpProgress" class="text-secondary">Siswa 1 dari X</h5>
                            <h3 id="bpStudentName" class="text-primary font-weight-bold my-3">-</h3>
                            <div class="d-flex justify-content-center mt-4" style="gap: 15px;">
                                <button class="btn btn-secondary" id="bpPrevBtn"><i class="fas fa-chevron-left"></i> Prev</button>
                                <button class="btn btn-success" id="bpPrintBtn"><i class="fas fa-print"></i> Print Sekarang</button>
                                <button class="btn btn-primary" id="bpNextBtn">Next <i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // State
            let class_id = null;
            let class_name = ''; // Simpan nama kelas
            let fst_id = null;
            let fst_name = '';
            //Setting Raport Section
            let tgl_print = sessionStorage.getItem('tgl_print') ?? null;
            let keputusan = sessionStorage.getItem('keputusan') ?? '';
            // NIlai Section (Filter)
            $('#tgl_print').change(function() {
                tgl_print = $('#tgl_print').val()
                $('#valueTable').DataTable().ajax.reload(null, false);
            })
            $('#pickClassBtn').click(function() {
                $("#pickClass").slideToggle(300);
            })
            $('#filterForm').submit(function(e) {
                e.preventDefault();
                class_id = $('#class_id').val();
                class_name = $('#class_id option:selected').text();
                if (!class_id) {
                    SwalHelper.showError('Silahkan Pilih Kelas Terlebih Dahulu');
                    return;
                }
                $("#pickFst").show()
                $("#pickClass").hide()

            })
            $('#fstForm').submit(function(e) {
                e.preventDefault();
                fst_id = $('#fst_id').val();
                fst_name = $('#fst_id option:selected').text();

                if (!fst_id) {
                    SwalHelper.showError('Silahkan Pilih Fase/Semester/Tahun Ajaran Terlebih Dahulu');
                    return;
                }
                if ($.fn.DataTable.isDataTable('#valueTable')) {
                    $('#valueTable').DataTable()
                        .destroy(); // Hancurkan DataTables lama sebelum memuat ulang
                }
                $("#ClassSel").text(class_name);
                $("#FstSel").text(fst_name);

                $('#valueTable').DataTable({
                    "paging": false,
                    "responsive": true,
                    "ordering": false,
                    "ajax": {
                        "url": "{{ route('nilaiakhir.getAllStudentAVG') }}", // Ganti dengan URL API Anda
                        "type": "GET",
                        "data": {
                            class_id: class_id,
                            fst_id: fst_id
                        },
                        "dataSrc": 'data'
                    },
                    "columns": [{
                            "data": null,
                            "render": function(data, type, row, meta) {
                                return meta.row + 1; // Index + 1
                            }
                        },
                        {
                            "data": "student_name"
                        },
                        {
                            "data": "class_name"
                        },
                        {
                            "data": "avg_nilai_semua_mapel",
                            "render": function(data) {
                                if (data) {
                                    return `<input type="text" value="${Math.round(data)}" class="form-control" disabled></input>`
                                } else {
                                    return '-';
                                }
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                let exportUrl = "{!! route('nilaiakhir.detailNilaiAkhir', ['student_id' => '__STUDENT_ID__', 'class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']) !!}";
                                exportUrl = exportUrl.replace('__STUDENT_ID__', row.student_id).replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);

                                let exportUrl2 = "{!! route('nilaiakhir.print', ['student_id' => '__STUDENT_ID__', 'class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']) !!}";
                                exportUrl2 = exportUrl2.replace('__STUDENT_ID__', row.student_id).replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);
                                return `
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info dropdown-toggle"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item btn-detail" data-url="${exportUrl}" href="#"><i class="fas fa-info-circle text-primary"></i>&nbsp;Detail</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item btn-print" data-url="${exportUrl2}" href="#"><i class="fas fa-print text-success" ></i>&nbsp;Print</a>
                                                </div>
                                            </div>
                                `;


                            }
                        }
                    ]
                });
                $("#pickFst").hide(300);
                $("#resultTable").show(300);
                $("#expBtn1").show(300);
                $("#expBtn2").show(400);
                $("#expBtn4").show(400);
                $("#expBtn3").show(500);

            });


            $(document).on("click", ".btn-print", function(e) {
                e.preventDefault();
                let url = $(this).data("url"); // Get base PDF URL
                let tgl = sessionStorage.getItem('tgl_print') ?? '';
                let kpt = sessionStorage.getItem('keputusan') ?? '';
                
                url += `&tgl_print=${encodeURIComponent(tgl)}&keputusan=${encodeURIComponent(kpt)}`;
                exportpdf(url);
            });
            
            $(document).on("click", ".btn-detail", function(e) {
                e.preventDefault();
                let url = $(this).data("url"); 
                let tgl = sessionStorage.getItem('tgl_print') ?? '';
                url += `&tgl_print=${encodeURIComponent(tgl)}`;
                window.location.href = url;
            });
            $('#SetRaportForm').submit(function(e) {
                e.preventDefault();
                tgl_print = $('#tanggalPrint').val();
                keputusan = $('#boxKeputusan').val();
                sessionStorage.setItem('tgl_print', tgl_print);
                sessionStorage.setItem('keputusan', keputusan);
                SwalHelper.showSuccess('Pengaturan Raport Berhasil Disimpan!')
                setTimeout(function() {
                    $('#SettingRaportCenter').modal('hide');
                    console.log(tgl_print, keputusan);
                }, 2000);
            })
            $('#expBtn1').on('click', function() {
                let excelUrl = @json(route('nilaiakhir.exportexcel', ['class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']));

                // Gunakan hasil replace
                excelUrl = excelUrl.replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);


                $.ajax({
                    url: excelUrl,
                    method: 'GET',
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data, _, xhr) {
                        var contentDisposition = xhr.getResponseHeader('Content-Disposition');
                        var fileName = `Nilai_Akhir_${class_name}.xlsx`; // Default name

                        if (contentDisposition) {
                            var matches = /filename="([^"]*)"/.exec(contentDisposition);
                            if (matches != null && matches[1]) {
                                fileName = matches[1]; // Extract filename from the header
                            }
                        }

                        var a = document.createElement('a');
                        var url = window.URL.createObjectURL(data);
                        a.href = url;
                        a.download = fileName;
                        document.body.append(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                        SwalHelper.showSuccess('Berhasil DiExport!')
                    }
                });
            });

            $('#expBtn2').on('click', function() {
                let rankingUrl = @json(route('nilaiakhir.exportranking', ['class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']));
                rankingUrl = rankingUrl.replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);

                $.ajax({
                    url: rankingUrl,
                    method: 'GET',
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data, _, xhr) {
                        var contentDisposition = xhr.getResponseHeader('Content-Disposition');
                        var fileName = `Ranking_Siswa_${class_name}.xlsx`;

                        if (contentDisposition) {
                            var matches = /filename="([^"]*)"/.exec(contentDisposition);
                            if (matches != null && matches[1]) {
                                fileName = matches[1];
                            }
                        }

                        var a = document.createElement('a');
                        var url = window.URL.createObjectURL(data);
                        a.href = url;
                        a.download = fileName;
                        document.body.append(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                        SwalHelper.showSuccess('Ranking Berhasil DiExport!')
                    }
                });
            });

            function exportpdf(url) {
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        let newWindow = window.open(data.pdf_url,
                            '_blank');
                        setTimeout(() => newWindow.print(),
                            1000);
                        // console.log(data);
                    });
            }

            // --- Bulk Print Berurutan Logic ---
            let currentPrintIndex = 0;
            let tableData = [];

            $('#expBtn4').click(function() {
                let dt = $('#valueTable').DataTable();
                tableData = dt.rows().data().toArray();
                if(tableData.length === 0) {
                    SwalHelper.showError('Tidak ada data siswa.');
                    return;
                }
                currentPrintIndex = 0;
                updateBulkPrintUI();
                $('#BulkPrintModal').modal('show');
            });

            function updateBulkPrintUI() {
                if (currentPrintIndex < 0) currentPrintIndex = 0;
                if (currentPrintIndex >= tableData.length) currentPrintIndex = tableData.length - 1;
                
                let student = tableData[currentPrintIndex];
                $('#bpProgress').text(`Siswa ${currentPrintIndex + 1} dari ${tableData.length}`);
                $('#bpStudentName').text(student.student_name);
                
                $('#bpPrevBtn').prop('disabled', currentPrintIndex === 0);
                $('#bpNextBtn').prop('disabled', currentPrintIndex === tableData.length - 1);
            }

            $('#bpPrevBtn').click(function() {
                currentPrintIndex--;
                updateBulkPrintUI();
            });

            $('#bpNextBtn').click(function() {
                currentPrintIndex++;
                updateBulkPrintUI();
            });

            $('#bpPrintBtn').click(function() {
                let student = tableData[currentPrintIndex];
                
                // Get current settings
                let tgl = sessionStorage.getItem('tgl_print') ?? '';
                let kpt = sessionStorage.getItem('keputusan') ?? '';

                let exportUrl2 ="{!! route('nilaiakhir.print', ['student_id' => '__STUDENT_ID__', 'class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']) !!}";
                exportUrl2 = exportUrl2.replace('__STUDENT_ID__', student.student_id).replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);
                exportUrl2 += `&tgl_print=${encodeURIComponent(tgl)}&keputusan=${encodeURIComponent(kpt)}`;
                                      
                let btn = $(this);
                let originalHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyiapkan PDF...');
                
                fetch(exportUrl2)
                    .then(response => response.json())
                    .then(data => {
                        btn.prop('disabled', false).html(originalHtml);
                        let newWindow = window.open(data.pdf_url, '_blank');
                        setTimeout(() => newWindow.print(), 1000);
                    })
                    .catch(err => {
                        btn.prop('disabled', false).html(originalHtml);
                        SwalHelper.showError('Gagal menyiapkan PDF.');
                    });
            });
            // --- End Bulk Print Berurutan Logic ---

        });
    </script>
@endsection
