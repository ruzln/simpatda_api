// =======================
// STATE GLOBAL
// =======================
let currentJenis   = '';
let currentPage    = 1;
let perPage        = 10;
let currentQuery   = '';
let currentStatus  = '';
let totalPage      = 1;

let tglDari;
let tglSampai;
let rangePicker;

let flagOffice = 0;

let searchMode = 'normal';   // 'normal' = per jenis (klik card), 'wp' = semua jenis untuk 1 WP
let currentNpwpd = '';
let currentNamaWp = '';

// =======================
// INIT
// =======================
document.addEventListener('DOMContentLoaded', () => {
    console.log('combined.js loaded');

    setPresetTanggal('year');
    bindSearch();
    bindPagination();
    initRangePicker();

    // Filter Status
    const filterStatusEl = document.getElementById('filterStatus');
    if (filterStatusEl) {
        filterStatusEl.addEventListener('change', e => {
            currentStatus = e.target.value.trim();
            console.log('[FILTER] Status diubah menjadi:', currentStatus);
            currentPage = 1;
            loadTableCombined();
        });
    } else {
        console.warn('[FILTER] Elemen #filterStatus tidak ditemukan');
    }

    // Page Size
    const pageSizeEl = document.getElementById('pageSize');
    if (pageSizeEl) {
        pageSizeEl.addEventListener('change', e => {
            perPage = parseInt(e.target.value);
            console.log('Page size diubah:', perPage);
            currentPage = 1;
            loadTableCombined();
        });
    }

    // Tombol Export Excel
    const btnExport = document.getElementById('btnExportExcel');
    if (btnExport) {
        btnExport.addEventListener('click', () => {
            if (searchMode !== 'wp' || (!currentNpwpd && !currentNamaWp)) {
                alert('Silakan cari NPWPD atau Nama WP terlebih dahulu');
                return;
            }

            const body = {
                tgl_dari: tglDari,
                tgl_sampai: tglSampai,
                flag: flagOffice,
            };

            if (currentNpwpd) body.npwpd = currentNpwpd;
            if (currentNamaWp) body.nama_wp = currentNamaWp;

            fetch('/api/skprd/export-wp', {
                method: 'POST',
                headers: headers(),
                body: JSON.stringify(body)
            })
            .then(response => {
                if (!response.ok) throw new Error('Gagal export');
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Tagihan_WP_${currentNpwpd || currentNamaWp}_${new Date().toISOString().slice(0,10)}.csv`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            })
            .catch(err => {
                console.error('Export error:', err);
                alert('Gagal export data. Silakan coba lagi.');
            });
        });
    }
});

function initRangePicker() {
    rangePicker = flatpickr("#rangeTanggal", {
        mode: "range",
        dateFormat: "Y-m-d",
        locale: "id",
        allowInput: true
    });

    document.getElementById('btnApplyRange').onclick = applyRangeTanggal;
    document.getElementById('btnResetRange').onclick = resetRangeTanggal;
}

// =======================
// DATE & PRESET
// =======================
function applyRangeTanggal() {
    const dates = rangePicker.selectedDates;
    if (dates.length !== 2) {
        alert('Pilih range tanggal lengkap');
        return;
    }

    tglDari = formatDateLocal(dates[0]);
    tglSampai = formatDateLocal(dates[1]);

    resetToNormalMode();
    loadSummaryCombined();
    clearTable();
}

function resetRangeTanggal() {
    rangePicker.clear();
    setPresetTanggal('year');
}

function formatDateLocal(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function setPresetTanggal(type) {
    const now = new Date();

    if (type === 'today') {
        tglDari = formatDateLocal(now);
        tglSampai = formatDateLocal(now);
    } else if (type === 'month') {
        tglDari = formatDateLocal(new Date(now.getFullYear(), now.getMonth(), 1));
        tglSampai = formatDateLocal(now);
    } else if (type === 'year') {
        tglDari = formatDateLocal(new Date(now.getFullYear(), 0, 1));
        tglSampai = formatDateLocal(now);
    }

    resetToNormalMode();
    loadSummaryCombined();
    clearTable();
}

function resetToNormalMode() {
    currentJenis = '';
    currentPage = 1;
    searchMode = 'normal';
    currentNpwpd = '';
    currentNamaWp = '';
    currentQuery = '';
    currentStatus = '';
    document.getElementById('searchInput').value = '';
    document.getElementById('table-title').textContent = 'Detail SKPRD – Pilih jenis pajak di atas';
}

// =======================
// HEADERS
// =======================
function headers() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    };
}

// =======================
// SUMMARY CARDS
// =======================
function loadSummaryCombined() {
    fetch('/api/skprd/combined/summary', {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({
            tgl_dari: tglDari,
            tgl_sampai: tglSampai,
            flag: flagOffice
        })
    })
    .then(r => r.json())
    .then(res => renderSummaryCards(res.summary_by_jenis || []))
    .catch(err => {
        console.error('Error load summary:', err);
        document.getElementById('summary-cards').innerHTML = '<div class="col-12 text-danger">Gagal memuat ringkasan</div>';
    });
}

function renderSummaryCards(data) {
    const wrap = document.getElementById('summary-cards');
    wrap.innerHTML = '';

    if (!data.length) {
        wrap.innerHTML = '<div class="col-12 text-muted text-center py-4">Tidak ada data untuk periode ini</div>';
        return;
    }

    data.forEach(item => {
        const col = document.createElement('div');
        col.className = 'col-xl-3 col-md-6 mb-4';

        col.innerHTML = `
            <div class="card border-left-primary shadow h-100 py-2 summary-card" style="cursor:pointer">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                ${getJenisLabel(item.jenis)}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${item.total_data.toLocaleString('id-ID')}
                            </div>
                            <div class="small text-muted">
                                Pajak: ${rupiah(item.total_pajak)}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;

        col.onclick = () => {
            currentJenis = item.jenis;
            currentPage = 1;
            searchMode = 'normal';
            currentNpwpd = '';
            currentNamaWp = '';
            currentQuery = '';
            document.getElementById('searchInput').value = '';
            document.getElementById('table-title').textContent = `Detail SKPRD ${getJenisLabel(currentJenis)}`;
            loadTableCombined();
        };

        wrap.appendChild(col);
    });
}

// =======================
// SEARCH - DIPERBAIKI
// =======================
function bindSearch() {
    const input = document.getElementById('searchInput');
    if (!input) return;

    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const q = input.value.trim();

            if (q.length < 3) {
                currentQuery = '';
                currentNpwpd = '';
                currentNamaWp = '';
                searchMode = 'normal';
                document.getElementById('table-title').textContent = 
                    currentJenis ? `Detail SKPRD ${getJenisLabel(currentJenis)}` : 'Detail SKPRD – Pilih jenis pajak di atas';
                loadTableCombined();
                return;
            }

            // Deteksi NPWPD (format standar NPWPD)
            if (/^\d{1,2}\.\d{8}\.\d{2}\.\d{2}$/.test(q) || q.replace(/\D/g, '').length > 12) {
                currentNpwpd = q.toUpperCase();
                currentNamaWp = '';
                searchMode = 'wp';
                document.getElementById('table-title').textContent = `Daftar Semua Tagihan NPWPD: ${q}`;
            } else {
                // Search nama WP → tetap di dalam jenis yang sedang aktif (klik card)
                currentQuery = q;
                searchMode = 'normal';
                document.getElementById('table-title').textContent = 
                    `Detail SKPRD ${getJenisLabel(currentJenis)} - Pencarian: ${q}`;
            }

            currentPage = 1;
            loadTableCombined();
        }, 600);
    });
}

// =======================
// LOAD TABLE
// =======================
function loadTableCombined() {
    // Loading indicator
    document.getElementById('skprd-table-body').innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2">Memuat data...</div>
            </td>
        </tr>`;

    const body = {
        tgl_dari: tglDari,
        tgl_sampai: tglSampai,
        flag: flagOffice,
        page: currentPage,
        per_page: perPage,
        status: currentStatus || null,
    };

    if (searchMode === 'wp') {
        if (currentNpwpd) body.npwpd = currentNpwpd;
        if (currentNamaWp) body.nama_wp = currentNamaWp;
    } else {
        // Mode normal (klik card)
        if (!currentJenis) return;
        body.jenis = currentJenis;
        if (currentQuery) body.q = currentQuery;   // search di dalam jenis aktif
    }

    fetch('/api/skprd/combined', {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify(body)
    })
    .then(r => r.json())
    .then(res => renderTableCombined(res))
    .catch(err => {
        console.error('Load table error:', err);
        document.getElementById('skprd-table-body').innerHTML = `
            <tr><td colspan="7" class="text-center text-danger py-5">
                Gagal memuat data
            </td></tr>`;
    });
}

// =======================
// RENDER TABLE & LAINNYA
// =======================
// =======================
// RENDER TABLE
// =======================
function renderTableCombined(res) {
    const tbody = document.getElementById('skprd-table-body');
    tbody.innerHTML = '';

    if (!res.data || res.data.length === 0) {
        let msg = 'Tidak ada data ditemukan';
        if (currentStatus) {
            const statusText = currentStatus === 'lunas' ? 'Lunas' : 
                              currentStatus === 'sebagian' ? 'Sebagian' : 'Belum Bayar';
            msg += ` untuk status <strong>${statusText}</strong>`;
        }
        if (searchMode === 'wp') {
            msg += ` pada pencarian WP/NPWPD ini`;
        }
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    ${msg}. Coba ubah filter atau periode tanggal.
                </td>
            </tr>`;
        updatePagination(res.meta);
        updateExportButton();
        return;
    }

    res.data.forEach((row, i) => {
        const pajak = Number(row.jml_pajak ?? row.JML_PAJAK ?? 0);
        const bayar = Number(row.jml_bayar ?? row.JML_BAYAR ?? row.jml_tbp ?? row.JML_TBP ?? 0);
        const sisa  = Number(row.jml_sisa  ?? row.JML_SISA ?? 0);
        const tglBayar = row.tgl_bayar ?? row.TGL_BAYAR ?? '';

        // STATUS BADGE
        let statusBadge = `<span class="badge badge-danger">Belum Bayar</span>`;
        if (bayar >= pajak) {
            if (sisa > 0) {
                statusBadge = `<span class="badge badge-success">Lunas <small>(Ada Denda)</small></span>`;
            } else {
                statusBadge = `<span class="badge badge-success">Lunas</span>`;
            }
        } else if (bayar > 0) {
            statusBadge = `<span class="badge badge-warning">Sebagian</span>`;
        }

        const bayarHtml = bayar > 0
            ? `${rupiah(bayar)}<br><small class="text-muted">${tglBayar || '-'}</small>`
            : '-';

        tbody.innerHTML += `
            <tr class="align-middle">
                <td class="text-center">${(currentPage - 1) * perPage + i + 1}</td>
                <td>
                    <div class="text-xs text-muted">${row.tgl_sk ?? '-'}</div>
                    <div class="font-medium text-slate-800">${row.no_sk ?? row.NO_SK ?? row.NO_SPTPD ?? '-'}</div>
                </td>
                <td>
                    <div class="font-medium text-slate-800">${row.nama_wp ?? row.NAMA_WP ?? '-'}</div>
                    <div class="text-xs text-muted">NPWPD: ${row.npwpd ?? row.NPWPD ?? '-'}</div>
                </td>
                <td class="text-right font-semibold">${rupiah(pajak)}</td>
                <td class="text-right text-emerald-600">${bayarHtml}</td>
                <td class="text-right ${sisa > 0 ? 'text-rose-600' : 'text-emerald-600'}">${rupiah(sisa)}</td>
                <td class="text-center">${statusBadge}</td>
            </tr>
        `;
    });

    updatePagination(res.meta);
    updateExportButton();

    // Update judul table
    if (searchMode === 'wp') {
        document.getElementById('table-title').textContent = 
            currentNpwpd ? `Daftar Semua Tagihan NPWPD: ${currentNpwpd}` : `Daftar Semua Tagihan WP: ${currentNamaWp}`;
    } else {
        document.getElementById('table-title').textContent = `Detail SKPRD ${getJenisLabel(currentJenis)}`;
    }
}

// =======================
// PAGINATION & UTIL
// =======================
function updatePagination(meta) {
    totalPage = Math.ceil((meta.total || 0) / (meta.per_page || 10));
    document.getElementById('pageInfo').textContent = `Halaman ${meta.page || 1} / ${totalPage}`;
    document.getElementById('btnPrev').disabled = (meta.page || 1) <= 1;
    document.getElementById('btnNext').disabled = (meta.page || 1) >= totalPage;
}

function clearTable() {
    document.getElementById('skprd-table-body').innerHTML = `
        <tr>
            <td colspan="7" class="text-center text-muted py-5">
                Pilih jenis pajak atau cari NPWPD/Nama WP
            </td>
        </tr>`;
}

function rupiah(value) {
    return 'Rp ' + (parseInt(value) || 0).toLocaleString('id-ID');
}

function getJenisLabel(jenis) {
    const labels = {
        'HOTEL':    'PBJT Jasa Perhotelan',
        'RESTO':    'PBJT Makan/ Minum',
        'HIBURAN':  'PBJT Jasa Kesenian dan Hiburan',
        'PARKIR':   'Pajak Parkir',
        'MGOLC':    'Pajak MBLB',
        'PENER':    'PBJT Tenaga Listrik',
        'REKLA':    'Pajak Reklame',
        'AIRTN':    'Pajak Air Tanah',
        'LAINNYA':  'Pajak Lainnya'
    };
    return labels[jenis?.toUpperCase()] || jenis?.toUpperCase() || 'Tidak Dikenal';
}

// Tombol Export 
// Tombol Export Excel
function updateExportButton() {
    const btnExport = document.getElementById('btnExportExcel');
    if (!btnExport) return;

    // Tombol hanya muncul di mode WP (setelah search NPWPD atau nama WP)
    if (searchMode === 'wp' && (currentNpwpd || currentNamaWp)) {
        btnExport.classList.remove('d-none');
    } else {
        btnExport.classList.add('d-none');
    }
}

// =======================
// BIND PAGINATION
// =======================
function bindPagination() {
    document.getElementById('btnPrev')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadTableCombined();
        }
    });

    document.getElementById('btnNext')?.addEventListener('click', () => {
        if (currentPage < totalPage) {
            currentPage++;
            loadTableCombined();
        }
    });
}

function updateExportButton() {
    const btnExport = document.getElementById('btnExportExcel');
    if (btnExport) {
        btnExport.classList.toggle('d-none', searchMode !== 'wp' || (!currentNpwpd && !currentNamaWp));
    }
}