@extends('layouts.app')

@section('page-title', 'Home')

@section('content')
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
<div class="container-fluid">

    <!-- Preset Filter Tanggal -->
    <div class="row mb-2">
        <div class="col">
            <button class="btn btn-sm btn-primary" onclick="setPresetTanggal('today')">Hari ini</button>
            <button class="btn btn-sm btn-primary" onclick="setPresetTanggal('month')">Bulan ini</button>
            <button class="btn btn-sm btn-primary" onclick="setPresetTanggal('year')">Tahun ini</button>
        </div>
    </div>

    <div class="row mb-3 align-items-end">
        <div class="col-md-3">
            <input type="text" id="rangeTanggal" class="form-control" placeholder="Pilih range tanggal">
        </div>

        <div class="col-md-auto">
            <button id="btnApplyRange" class="btn btn-secondary btn-icon-split btn-sm">
                <span class="icon text-white-50"><i class="fas fa-arrow-right"></i></span>
                <span class="text">Terapkan</span>
            </button>
        </div>

        <div class="col-md-auto">
            <button id="btnResetRange" class="btn btn-primary btn-icon-split btn-sm">
                <span class="icon text-white-50"><i class="fas fa-trash"></i></span>
                <span class="text">Reset</span>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div id="summary-cards" class="row mb-4"></div>

    <!-- Detail Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom">
                <h6 id="table-title" class="m-0 font-weight-bold text-primary">
                Detail SKPD – Pilih jenis pajak di atas
                </h6>

            <div class="d-flex align-items-center gap-2">
                <!-- Search -->
                <input id="searchInput" 
                    type="text" 
                    class="form-control form-control-sm shadow-sm" 
                    placeholder="Cari No SK / Nama WP / NPWPD..." 
                    style="width: 260px;">

                <!-- Filter Status -->
                <select id="filterStatus" 
                        class="form-select form-select-sm shadow-sm border"
                        style="width: 135px; height: 32px;">
                    <option value="">Semua Status</option>
                    <option value="lunas">Lunas</option>
                    <option value="sebagian">Sebagian</option>
                    <option value="belum">Belum Bayar</option>
                </select>

                <!-- Page Size -->
                <select id="pageSize" 
                        class="form-select form-select-sm shadow-sm border"
                        style="width: 105px; height: 32px;">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>

                <!-- Tombol Export Excel -->
                <button id="btnExportExcel" 
                    class="btn btn-sm btn-success shadow-sm"
                    title="Export ke Excel">  
                    <i class="fas fa-file-excel me-1"></i> Export
                </button>

            </div>
        </div>

    <!-- card-body dan pagination -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>SK</th>
                            <th>Wajib Pajak</th>
                            <th class="text-end">Ketetapan</th>
                            <th class="text-end">Bayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="skprd-table-body">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                Pilih salah satu card di atas untuk melihat detail
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="card-footer d-flex justify-content-between align-items-center">
            <button id="btnPrev" class="btn btn-sm btn-secondary">Prev</button>
            <span id="pageInfo" class="small text-muted">Halaman 1 / 1</span>
            <button id="btnNext" class="btn btn-sm btn-secondary">Next</button>
        </div>
    </div>

</div>
</div>
</div>
@endsection

@section('styles')
<style>
    .summary-card:hover {
        transform: translateY(-5px);
        transition: all 0.2s ease;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Perbaikan visual dropdown agar tidak flat */
    .form-select {
        border: 1px solid #ced4da;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }

    .form-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }

    .form-select:hover {
        border-color: #adb5bd;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.45em 0.9em;
    }

    .btn-success {
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    }
</style>
@endsection

@push('scripts')
<script src="{{ asset('js/skprd/combined.js') }}"></script>
@endpush