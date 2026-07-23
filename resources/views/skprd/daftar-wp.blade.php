@extends('layouts.app')

@section('page-title', 'Daftar Wajib Pajak')

@section('content')

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Wajib Pajak</h1>
    </div>

    <!-- Preset Tanggal -->
    <div class="row mb-3">
        <div class="col">
            <button class="btn btn-sm btn-primary me-2" onclick="setPresetTanggal('today')">Hari ini</button>
            <button class="btn btn-sm btn-primary me-2" onclick="setPresetTanggal('month')">Bulan ini</button>
            <button class="btn btn-sm btn-primary me-2" onclick="setPresetTanggal('year')">Tahun ini</button>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari NPWPD atau Nama WP...">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>NPWPD</th>
                            <th>Nama Wajib Pajak</th>
                            <th>Alamat</th>
                            <th class="text-end">Total Tagihan</th>
                            <th class="text-end">Total Sisa</th>
                            <th class="text-center">Jumlah SK</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="daftar-wp-body"></tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <button id="btnPrev" class="btn btn-secondary">Prev</button>
                <span id="pageInfo" class="text-muted"></span>
                <button id="btnNext" class="btn btn-secondary">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Tagihan WP -->
<div class="modal fade" id="modalWpDetail" tabindex="-1" aria-labelledby="modalWpTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalWpTitle">Detail Tagihan WP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalWpContent">
                <!-- Diisi oleh JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/skprd/daftar-wp.js') }}"></script>
@endpush