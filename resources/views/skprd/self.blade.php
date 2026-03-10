@extends('layouts.app')
@section('page-title', 'Self Assessment')
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


<div class="row" id="summary-cards"></div>
<div class="card shadow mb-4">
           <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary" id="table-title">
                Detail SPTPD </h6>
            <div class="d-flex justify-content-end mb-3">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm shadow-sm"
         style="max-width:520px">

        {{-- Status --}}
        <select id="filterStatus"
                class="form-select form-select-sm rounded-0 rounded-start"
                style="max-width:120px">
            <option value="">Semua</option>
            <option value="lunas">Lunas</option>
            <option value="sebagian">Sebagian</option>
            <option value="belum">Belum</option>
        </select>

        {{-- Page size --}}
        <select id="pageSize"
                class="form-select form-select-sm rounded-0"
                style="max-width:80px">
                 <option value="10">10</option>
                 <option value="20">20</option>
                 <option value="50">50</option>
                 <option value="100">100</option>
        </select>

        {{-- Search --}}
        <input type="text"
               id="searchInput"
               class="form-control form-control-sm rounded-0"
               placeholder="Cari WP / NPWPD / No SK">

        {{-- Icon --}}
        <span class="input-group-text rounded-0 rounded-end bg-white">
            <i class="fas fa-search opacity-75"></i>
        </span>

    </div>
</div>
</div>

</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                <tr>
                    <th class="text-center w-12">No</th>
                    <th>SK</th>
                    <th>Wajib Pajak</th>
                    <th class="text-right">Ketetapan</th>
                    <th class="text-right">Bayar</th>
                    <th class="text-right">Sisa</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>

            <tbody id="skprd-table-body"
                class="divide-y divide-slate-100 text-[13px] text-slate-700">
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Klik salah satu card untuk melihat data
                    </td>
                </tr>
            </tbody>
        </table>

    </div> 
    <!-- Pagination -->
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
@endsection

@push('scripts')
<script src="{{ asset('js/skprd/self.js') }}"></script>
@endpush
