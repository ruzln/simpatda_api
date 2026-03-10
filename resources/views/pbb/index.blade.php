@extends('layouts.app')
@section('page-title', 'Realisasi PBB-P2')
@section('content')
<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>

            <small class="text-muted">
                Periode: <span id="periodeText">1 Januari – Hari ini</span>
            </small>
        </div>
    </div>

    {{-- ================= SUMMARY ================= --}}
    <div class="row mb-4" id="summary-cards">

        {{-- TOTAL REALISASI --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Realisasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"
                                 id="totalRealisasi">
                                Rp 0
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- JUMLAH TRANSAKSI --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Jumlah Transaksi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"
                                 id="jumlahData">
                                0
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    {{-- ================= TABLE ================= --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Detail Realisasi PBB
            </h6>
            <div class="d-flex flex-wrap gap-2 align-items-right mb-3">

                <!-- FILTER TAHUN -->
                <select id="filterTahun"
                        class="form-select form-select-sm w-auto">
                    <option value="">Tahun</option>
                </select>

                <!-- FILTER KELURAHAN -->
                <select id="filterKelurahan"
                        class="form-select form-select-sm w-auto">
                    <option value="">Semua Kelurahan</option>
                </select>

                <!-- PAGE SIZE -->
                <select id="pageSize"
                        class="form-select form-select-sm w-auto">
                    <option value="20">20 </option>
                    <option value="50">50 </option>
                    <option value="100">100 </option>
                </select>
                            <!-- SEARCH -->
                <input type="text"
                    id="searchInput"
                    class="form-control form-control-sm w-25"
                    placeholder="Cari NOP / Nama WP">
        </div>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>NOP</th>
                            <th>Nama Wajib Pajak</th>
                            <th>Kelurahan OP</th>
                            <th class="text-right">Pokok</th>
                            <th class="text-right">Denda</th>
                            <th class="text-right">Dibayar</th>
                            <th class="text-right">Kurang Bayar</th>
                            <th width="110">Tgl Bayar</th>
                        </tr>
                    </thead>

                    <tbody id="pbb-table-body" class="text-sm">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Data belum dimuat
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        {{-- ================= PAGINATION ================= --}}
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-muted" id="paginationInfo">
                Menampilkan 0 data
            </div>

            <div>
                <button class="btn btn-sm btn-outline-secondary"
                        id="btnPrev">
                    ‹ Prev
                </button>
                <span class="mx-2" id="pageInfo">Page 1 / 1</span>
                <button class="btn btn-sm btn-outline-secondary"
                        id="btnNext">
                    Next ›
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/pbb/pbb.js') }}"></script>
@endpush