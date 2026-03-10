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

let searchMode = 'normal'; // 'normal' atau 'wp'
let currentNpwpd = '';
let currentNamaWp = '';

// =======================
// INIT & EVENT LISTENER
// =======================
document.addEventListener('DOMContentLoaded', () => {
    console.log('combined.js loaded');
    setPresetTanggal('year');
    bindSearch();
    bindPagination();
    initRangePicker();

    // Filter status
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

    // Page size
    const pageSizeEl = document.getElementById('pageSize');
    if (pageSizeEl) {
        pageSizeEl.addEventListener('change', e => {
            perPage = parseInt(e.target.value);
            console.log('Page size diubah:', perPage);
            currentPage = 1;
            loadTableCombined();
        });
    }

    //Export
    const btnExport = document.getElementById('btnExportExcel');
    if (btnExport) {
        btnExport.addEventListener('click', () => {
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
                a.download = 'Tagihan_WP.xlsx';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            })
            .catch(err => {
                console.error('Export error:', err);
                alert('Gagal export Excel: ' + err.message);
            });
        });
    }
});

function initRangePicker() {
    // Pastikan locale 'id' sudah ada, fallback ke 'en' kalau gagal
    if (!flatpickr.l10ns.id) {
        console.warn('Locale "id" tidak ditemukan, fallback ke default (en)');
    }

    rangePicker = flatpickr("#rangeTanggal", {
        mode: "range",
        dateFormat: "Y-m-d",
        locale: flatpickr.l10ns.id || "en",  // fallback aman
        allowInput: true,
        onReady: function(selectedDates, dateStr, instance) {
            console.log('Flatpickr siap dengan locale:', instance.config.locale);
        }
    });

    const btnApply = document.getElementById('btnApplyRange');
    const btnReset = document.getElementById('btnResetRange');

    if (btnApply) btnApply.onclick = applyRangeTanggal;
    if (btnReset) btnReset.onclick = resetRangeTanggal;
}

function applyRangeTanggal() {
    const dates = rangePicker.selectedDates;
    if (dates.length !== 2) {
        alert('Pilih range tanggal lengkap');
        return;
    }

    tglDari   = formatDateLocal(dates[0]);
    tglSampai = formatDateLocal(dates[1]);

    currentJenis = '';
    currentPage  = 1;
    searchMode = 'normal';
    currentNpwpd = '';
    currentNamaWp = '';
    document.getElementById('searchInput').value = ''; // reset search box
    document.getElementById('table-title').textContent = 'Detail SKPRD – Pilih jenis pajak di atas';

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
        tglDari   = formatDateLocal(now);
        tglSampai = formatDateLocal(now);
    } else if (type === 'month') {
        tglDari   = formatDateLocal(new Date(now.getFullYear(), now.getMonth(), 1));
        tglSampai = formatDateLocal(now);
    } else if (type === 'year') {
        tglDari   = formatDateLocal(new Date(now.getFullYear(), 0, 1));
        tglSampai = formatDateLocal(now);
    }

    currentJenis = '';
    currentPage = 1;
    searchMode = 'normal';
    currentNpwpd = '';
    currentNamaWp = '';
    document.getElementById('searchInput').value = '';
    document.getElementById('table-title').textContent = 'Detail SKPRD – Pilih jenis pajak di atas';

    loadSummaryCombined();
    clearTable();
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
    console.log('Load summary dengan periode:', tglDari, 's/d', tglSampai);
    fetch('/api/skprd/combined/summary', {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({
            tgl_dari: tglDari,
            tgl_sampai: tglSampai,
            flag: flagOffice
        })
    })
    .then(r => {
        if (!r.ok) throw new Error(`Summary HTTP ${r.status}`);
        return r.json();
    })
    .then(res => {
        console.log('Summary response:', res);
        renderSummaryCards(res.summary_by_jenis || []);
    })
    .catch(err => {
        console.error('Error load summary:', err);
        document.getElementById('summary-cards').innerHTML = '<div class="col-12 text-danger">Gagal memuat ringkasan</div>';
    });
}

function renderSummaryCards(data) {
    const wrap = document.getElementById('summary-cards');
    wrap.innerHTML = '';

    if (!data.length) {
        wrap.innerHTML = '<div class="col-12 text-muted text-center py-4">Tidak ada data</div>';
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
            document.getElementById('searchInput').value = '';
            document.getElementById('table-title').textContent = `Detail SKPRD ${getJenisLabel(currentJenis)}`;
            loadTableCombined();
        };

        wrap.appendChild(col);
    });
}

// =======================
// SEARCH
// =======================
function bindSearch() {
    const input = document.getElementById('searchInput');
    if (!input) {
        console.warn('Input search tidak ditemukan');
        return;
    }

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

            console.log('Search input:', q);

            // Deteksi NPWPD (format dengan titik atau panjang angka)
            if (/^\d{1,2}\.\d{8}\.\d{2}\.\d{2}$/.test(q) || q.replace(/\D/g, '').length > 12) {
                currentNpwpd = q.toUpperCase();
                currentNamaWp = '';
                searchMode = 'wp';
                document.getElementById('table-title').textContent = `Daftar Tagihan NPWPD: ${q}`;
            } else {
                currentNamaWp = q.toUpperCase();
                currentNpwpd = '';
                searchMode = 'wp';
                document.getElementById('table-title').textContent = `Daftar Tagihan WP: ${q}`;
            }

            currentPage = 1;
            loadTableCombined();
        }, 600);
    });
}

// =======================
// PAGINATION
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

// =======================
// LOAD TABLE
// =======================
function loadTableCombined() {
    document.getElementById('skprd-table-body').innerHTML = `
    <tr>
        <td colspan="7" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Memuat data...</div>
        </td>
    </tr>`;
    console.log('[TABLE] Load dipanggil | Mode:', searchMode, '| Jenis:', currentJenis, '| Status:', currentStatus, '| Page:', currentPage);

    if (searchMode === 'wp' && !currentNpwpd && !currentNamaWp) {
        console.warn('[TABLE] Mode WP tapi tidak ada NPWPD/Nama');
        return;
    }

    if (searchMode === 'normal' && !currentJenis) {
        console.warn('[TABLE] Mode normal tapi currentJenis kosong');
        document.getElementById('skprd-table-body').innerHTML = `
            <tr><td colspan="7" class="text-center text-warning py-5">
                Pilih jenis pajak atau cari NPWPD/Nama WP
            </td></tr>`;
        return;
    }

    const body = {
        tgl_dari: tglDari,
        tgl_sampai: tglSampai,
        flag: flagOffice,
        page: currentPage,
        per_page: perPage,
        status: currentStatus || null,  // kirim null kalau kosong
    };

    if (searchMode === 'wp') {
        if (currentNpwpd) body.npwpd = currentNpwpd;
        if (currentNamaWp) body.nama_wp = currentNamaWp;
    } else {
        body.jenis = currentJenis;
    }

    console.log('[TABLE] Body request:', body);

    fetch('/api/skprd/combined', {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify(body)
    })
    .then(r => {
        console.log('[TABLE] Status HTTP:', r.status);
        if (!r.ok) {
            return r.text().then(text => { throw new Error(`HTTP ${r.status}: ${text}`); });
        }
        return r.json();
    })
    .then(res => {
        console.log('[TABLE] Response diterima:', res);
        renderTableCombined(res);
    })
    .catch(err => {
        console.error('[TABLE] Error:', err);
        document.getElementById('skprd-table-body').innerHTML = `
            <tr><td colspan="7" class="text-center text-danger py-5">
                Gagal memuat: ${err.message}
            </td></tr>`;
    });
}

// =======================
// RENDER TABLE
// =======================
function renderTableCombined(res) {
    const tbody = document.getElementById('skprd-table-body');
    tbody.innerHTML = '';

   if (!res.data || res.data.length === 0) {
        let msg = 'Tidak ada data';
        if (currentStatus) {
            msg += ` untuk status "${currentStatus === 'lunas' ? 'Lunas' : currentStatus === 'sebagian' ? 'Sebagian' : 'Belum Bayar'}"`;
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
    }

    res.data.forEach((row, i) => {
        const pajak = Number(row.jml_pajak ?? row.JML_PAJAK ?? 0);
        const bayar = Number(row.jml_bayar ?? row.JML_BAYAR ?? row.jml_tbp ?? row.JML_TBP ?? 0);
        const sisa  = Number(row.jml_sisa  ?? row.JML_SISA ?? 0);
        const tglBayar = row.tgl_bayar ?? row.TGL_BAYAR ?? '';

        // STATUS BADGE - logika lebih akurat (pokok lunas = lunas, meskipun ada denda/sisa)
        if (bayar >= pajak) {
            if (sisa > 0) {
                statusBadge = `<span class="badge badge-info">(Sisa Denda ${rupiah(sisa)})</span>`;
            } else {
                statusBadge = `<span class="badge badge-success">Lunas</span>`;
            }
        } else if (bayar > 0) {
            statusBadge = `<span class="badge badge-warning">Sebagian</span>`;
        } else {
            statusBadge = `<span class="badge badge-danger">Belum Bayar</span>`;
        }

        const bayarHtml = bayar > 0
            ? `${rupiah(bayar)}<br><small class="text-muted">${tglBayar || '-'}</small>`
            : '-';

        tbody.innerHTML += `
            <tr class="align-middle">
                <td class="text-center">
                    ${(currentPage - 1) * perPage + i + 1}
                </td>
                <td>
                    <div class="text-xs text-muted">${row.tgl_sk ?? '-'}</div>
                    <div class="font-medium text-slate-800">${row.no_sk ?? row.NO_SK ?? row.NO_SPTPD ?? '-'}</div>
                </td>
                <td>
                    <div class="font-medium text-slate-800">
                        ${row.nama_wp ?? row.NAMA_WP ?? '-'}
                    </div>
                    <div class="text-xs text-muted">
                        NPWPD: ${row.npwpd ?? row.NPWPD ?? '-'}
                    </div>
                </td>
                <td class="text-right font-semibold">
                    ${rupiah(pajak)}
                </td>
                <td class="text-right text-emerald-600">
                    ${bayarHtml}
                </td>
                <td class="text-right ${sisa > 0 ? 'text-rose-600' : 'text-emerald-600'}">
                    ${rupiah(sisa)}
                </td>
                <td class="text-center">
                    ${statusBadge}
                </td>
            </tr>
        `;
    });

    updatePagination(res.meta);
    updateExportButton();

    // Update judul table sesuai mode
    if (searchMode === 'wp') {
        document.getElementById('table-title').textContent = 
            currentNpwpd ? `Daftar Tagihan NPWPD: ${currentNpwpd}` : `Daftar Tagihan WP: ${currentNamaWp}`;
    } else {
        document.getElementById('table-title').textContent = 
            `Detail SKPRD ${getJenisLabel(currentJenis)}`;
    }
}

// =======================
// PAGINATION & UTIL
// =======================
function updatePagination(meta) {
    totalPage = Math.ceil((meta.total || 0) / (meta.per_page || 10));

    document.getElementById('pageInfo').textContent = 
        `Halaman ${meta.page || 1} / ${totalPage}`;

    document.getElementById('btnPrev').disabled = (meta.page || 1) <= 1;
    document.getElementById('btnNext').disabled = (meta.page || 1) >= totalPage;
}

function clearTable() {
    document.getElementById('skprd-table-body').innerHTML = `
        <tr>
            <td colspan="7" class="text-center text-muted py-5">
                Pilih jenis pajak atau cari NPWPD/Nama WP
            </td>
        </tr>
    `;
}

function rupiah(value) {
    return 'Rp ' + (parseInt(value) || 0).toLocaleString('id-ID');
}

function getJenisLabel(jenis) {
    const labels = {
        'HOTEL':    'Pajak Hotel',
        'RESTO':    'Pajak Restoran',
        'HIBURAN':  'Pajak Hiburan',
        'PARKIR':   'Pajak Parkir',
        'MGOLC':    'Pajak Mineral & Batubara',
        'PENER':    'Pajak Penerangan Jalan',
        'REKLA':    'Reklame',
        'AIRTN':    'Air Tanah',
        'LAINNYA':  'Pajak Lainnya'
    };
    return labels[jenis?.toUpperCase()] || jenis?.toUpperCase() || 'Tidak Dikenal';
}

// Tombol Export Excel (hanya aktif di mode WP)
function updateExportButton() {
    const btnExport = document.getElementById('btnExportExcel');
    if (btnExport) {
        if (searchMode === 'wp' && (currentNpwpd || currentNamaWp)) {
            btnExport.classList.remove('d-none');
        } else {
            btnExport.classList.add('d-none');
        }
    }
}