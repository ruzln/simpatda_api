
@extends('layouts.app')
@section('page-title', 'Home')
@section('content')
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
<div class="container-fluid">

<div class="row mb-2">
    <div class="col">
        <button class="btn btn-sm btn-primary" onclick="setPresetTanggal('today')">Hari ini</button>
        <button class="btn btn-sm btn-primary" onclick="setPresetTanggal('month')">Bulan ini</button>
        <button class="btn btn-sm btn-primary" onclick="setPresetTanggal('year')">Tahun ini</button>
    </div>
</div>
<div class="row mb-2 align-items-end">
    <div class="col-md-3">
        <input type="text"
               id="rangeTanggal"
               class="form-control"
               placeholder="Pilih range tanggal">
    </div>

    <div class="col-md-auto">
        <button id="btnApplyRange"
                class="btn btn-secondary btn-icon-split btn-sm">
            <span class="icon text-white-50">
                <i class="fas fa-arrow-right"></i>
            </span>
            <span class="text">Ok</span>
        </button>
    </div>

    <div class="col-md-auto">
        <button id="btnResetRange"
                class="btn btn-primary btn-icon-split btn-sm">
            <span class="icon text-white-50">
                <i class="fas fa-trash"></i>
            </span>
            <span class="text">Reset</span>
        </button>
    </div>
</div>

    <!-- Summary Cards -->
    <div id="summary-cards" class="row"></div>

    <!-- Detail Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 id="table-title" class="m-0 font-weight-bold text-primary">
                Detail SKPRD – Pilih jenis pajak di atas
            </h6>
            <div class="d-flex align-items-center gap-3">
                <input id="searchInput" type="text" class="form-control form-control-sm" placeholder="Cari No SK / Nama WP / NPWPD..." style="width: 220px;">
                <button id="btnExportExcel" class="btn btn-sm btn-success d-none" title="Export ke Excel">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
                <select id="filterStatus" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Semua Status</option>
                    <option value="lunas">Lunas</option>
                    <option value="sebagian">Sebagian</option>
                    <option value="belum">Belum Bayar</option>
                </select>

                <select id="pageSize" class="form-select form-select-sm" style="width: auto;">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="skprdTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" ">No</th>
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

            <!-- Pagination Controls -->
            <div class="card-footer d-flex justify-content-between align-items-center">
                <button id="btnPrev" class="btn btn-sm btn-secondary">
                    Prev
                </button>
                <span id="pagination-info"></span>
                <span id="pageInfo" class="small text-muted"></span>

                <button id="btnNext" class="btn btn-sm btn-secondary">
                    Next
                </button>
            </div>
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
    .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.8em;
    }
    .btn-success i {
    font-size: 1.1rem;
    }
</style>
@endsection

@push('scripts')

<!-- JS Combined -->
<script src="{{ asset('js/skprd/combined.js') }}"></script>
@endpush