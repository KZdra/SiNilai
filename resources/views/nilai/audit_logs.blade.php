@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-history mr-2 text-primary"></i>Audit Trail Perubahan Nilai
                    </h1>
                    <p class="text-muted small mb-0">Rekam jejak transparansi mutasi nilai akademik siswa (siapa, kapan, sebelum vs sesudah).</p>
                </div>
                <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                    <a href="{{ route('value.index') }}" class="btn btn-outline-secondary btn-sm mr-1">
                        <i class="fas fa-pen mr-1"></i>Asesmen Sumatif
                    </a>
                    <a href="{{ route('nilaiakhir.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-graduation-cap mr-1"></i>Nilai Akhir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Filter Box -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body bg-light rounded">
                    <form id="filterForm">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label class="small font-weight-bold text-secondary text-uppercase">Cari Siswa / User</label>
                                <input type="text" id="filter_keyword" name="keyword" class="form-control form-control-sm" placeholder="Nama siswa, NIS, atau nama guru...">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold text-secondary text-uppercase">Jenis Aksi</label>
                                <select name="action" id="filter_action" class="form-control form-control-sm">
                                    <option value="">-- Semua Aksi --</option>
                                    <option value="INPUT_BARU">INPUT_BARU</option>
                                    <option value="UPDATE">UPDATE</option>
                                    <option value="DELETE">DELETE</option>
                                    <option value="IMPORT_CSV">IMPORT_CSV</option>
                                    <option value="SYNC_CBT">SYNC_CBT</option>
                                    <option value="KENAIKAN_KELAS">KENAIKAN_KELAS</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small font-weight-bold text-secondary text-uppercase">Mata Pelajaran</label>
                                <select name="mapel_id" id="filter_mapel" class="form-control form-control-sm">
                                    <option value="">-- Semua Mata Pelajaran --</option>
                                    @foreach ($mapelList as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->nama_mapel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold text-secondary text-uppercase">Dari Tanggal</label>
                                <input type="date" id="filter_start_date" name="start_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold text-secondary text-uppercase">Sampai Tanggal</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" id="filter_end_date" name="end_date" class="form-control">
                                    <div class="input-group-append">
                                        <button type="button" id="btnFilterSearch" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                        <button type="button" id="btnFilterReset" class="btn btn-secondary" title="Reset Filter"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Log -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-list-alt text-secondary mr-2"></i>Daftar Riwayat Perubahan Nilai
                    </h5>
                    <span class="badge badge-light border px-3 py-2 font-weight-normal text-secondary" id="totalLogsBadge">
                        Audit Trail Log
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-sm w-100" id="auditTable">
                            <thead class="bg-light text-secondary small text-uppercase">
                                <tr>
                                    <th style="width: 140px;">Waktu</th>
                                    <th>Pengubah / Akun</th>
                                    <th>Siswa</th>
                                    <th>Mapel & Semester</th>
                                    <th class="text-center" style="width: 110px;">Aksi</th>
                                    <th>Rincian Nilai (Lama vs Baru)</th>
                                    <th class="text-center" style="width: 100px;">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            function getActionBadge(action) {
                let cls = 'badge-secondary';
                if (action === 'INPUT_BARU') cls = 'badge-success';
                else if (action === 'UPDATE') cls = 'badge-primary';
                else if (action === 'DELETE') cls = 'badge-danger';
                else if (action === 'IMPORT_CSV') cls = 'badge-info';
                else if (action === 'SYNC_CBT') cls = 'badge-warning';
                else if (action === 'KENAIKAN_KELAS') cls = 'badge-dark';
                return `<span class="badge ${cls} px-2 py-1 font-weight-bold">${action || '-'}</span>`;
            }

            function renderRincian(row) {
                if (row.action === 'KENAIKAN_KELAS') {
                    return `
                        <div class="small">
                            <div class="text-dark mb-1">${row.reason || ''}</div>
                            <div>
                                <span class="badge badge-secondary"><i class="fas fa-sign-out-alt mr-1"></i>${row.old_value || '-'}</span>
                                <i class="fas fa-arrow-right text-muted mx-1"></i>
                                <span class="badge badge-success"><i class="fas fa-sign-in-alt mr-1"></i>${row.new_value || '-'}</span>
                            </div>
                        </div>
                    `;
                }
                let oldVal = {};
                let newVal = {};
                try { oldVal = JSON.parse(row.old_values || '{}') || {}; } catch (e) {}
                try { newVal = JSON.parse(row.new_values || '{}') || {}; } catch (e) {}

                if (row.action === 'INPUT_BARU' || row.action === 'IMPORT_CSV') {
                    let items = [];
                    for (let k in newVal) {
                        if (newVal[k] !== null && newVal[k] !== undefined) {
                            items.push(`<span class="mr-2"><strong>${k.replace('value_', '')}:</strong> ${newVal[k]}</span>`);
                        }
                    }
                    return `<div class="text-success small"><i class="fas fa-plus-circle mr-1"></i>${items.join(' ')}</div>`;
                }

                if (row.action === 'DELETE') {
                    return '<div class="text-danger small"><i class="fas fa-trash mr-1"></i>Data nilai dihapus oleh operator.</div>';
                }

                let allKeys = Array.from(new Set([...Object.keys(oldVal), ...Object.keys(newVal)]));
                let diffs = [];
                allKeys.forEach(k => {
                    let o = oldVal[k] !== undefined ? oldVal[k] : '-';
                    let n = newVal[k] !== undefined ? newVal[k] : '-';
                    if (o != n) {
                        diffs.push(`
                            <div class="mb-1">
                                <span class="badge badge-light border">${k.replace('value_', '')}</span>:
                                <span class="text-danger text-decoration-line-through mr-1"><del>${o}</del></span>
                                <i class="fas fa-arrow-right text-muted small mr-1"></i>
                                <span class="text-success font-weight-bold">${n}</span>
                            </div>
                        `);
                    }
                });
                return diffs.length ? `<div class="small">${diffs.join('')}</div>` : '<span class="text-muted small">-</span>';
            }

            let auditTable = $('#auditTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route('audit.getData') }}",
                    type: "GET",
                    data: function(d) {
                        d.keyword = $('#filter_keyword').val();
                        d.action = $('#filter_action').val();
                        d.mapel_id = $('#filter_mapel').val();
                        d.start_date = $('#filter_start_date').val();
                        d.end_date = $('#filter_end_date').val();
                    },
                    dataSrc: function(json) {
                        let total = json.recordsFiltered !== undefined ? json.recordsFiltered : (json.data ? json.data.length : 0);
                        $('#totalLogsBadge').text(`Total ${total} Log Terfilter`);
                        return json.data;
                    }
                },
                columns: [
                    {
                        data: 'created_at',
                        render: function(data) {
                            if (!data) return '-';
                            let d = new Date(data);
                            let dateStr = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                            let timeStr = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                            return `<div class="font-weight-bold text-dark">${dateStr}</div><small class="text-muted">${timeStr}</small>`;
                        }
                    },
                    {
                        data: 'user_name',
                        render: function(data) {
                            return `<i class="fas fa-user-circle text-secondary mr-1"></i><strong>${data || 'Sistem / Guest'}</strong>`;
                        }
                    },
                    {
                        data: 'student_name',
                        render: function(data, type, row) {
                            if (data) {
                                return `<div class="font-weight-bold text-dark">${data}</div><small class="text-muted">NIS: ${row.student_nis || '-'}</small>`;
                            }
                            return '<span class="text-muted font-italic"><i class="fas fa-users mr-1"></i>Massal / Rombel</span>';
                        }
                    },
                    {
                        data: 'nama_mapel',
                        render: function(data, type, row) {
                            if (data) {
                                let sub = row.fase ? `Fase ${row.fase} (${row.semester || ''})` : '';
                                return `<span class="badge badge-light border">${data}</span><div class="small text-muted mt-1">${sub}</div>`;
                            }
                            return '<span class="badge badge-light border text-muted">Struktur Rombel</span>';
                        }
                    },
                    {
                        data: 'action',
                        className: 'text-center',
                        render: function(data) {
                            return getActionBadge(data);
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return renderRincian(row);
                        }
                    },
                    {
                        data: 'ip_address',
                        className: 'text-center',
                        render: function(data) {
                            return `<code class="small text-dark">${data || '-'}</code>`;
                        }
                    }
                ]
            });

            $('#btnFilterSearch').on('click', function() {
                auditTable.ajax.reload();
            });

            $('#filter_action, #filter_mapel').on('change', function() {
                auditTable.ajax.reload();
            });

            $('#filter_keyword').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    auditTable.ajax.reload();
                }
            });

            $('#btnFilterReset').on('click', function() {
                $('#filter_keyword').val('');
                $('#filter_action').val('').trigger('change');
                $('#filter_mapel').val('').trigger('change');
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                auditTable.ajax.reload();
            });
        });
    </script>
@endsection
