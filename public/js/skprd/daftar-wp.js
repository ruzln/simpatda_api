// =======================
// STATE GLOBAL
// =======================
let currentPage = 1;
let perPage = 20;
let currentQuery = '';
let totalPage = 1;

let tglDari;
let tglSampai;

// =======================
// INIT
// =======================
function setPresetTanggal(type) {
    const now = new Date();
    let dari, sampai;

    switch (type) {
        case 'today':
            dari = sampai = now.toISOString().split('T')[0];
            break;
        case 'month':
            dari = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
            sampai = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
            break;
        case 'year':
            dari = `${now.getFullYear()}-01-01`;
            sampai = `${now.getFullYear()}-12-31`;
            break;
    }

    tglDari = dari;
    tglSampai = sampai;

    // Reset ke halaman 1 dan reload
    currentPage = 1;
    loadDaftarWp();
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('daftar-wp.js loaded');

    // Set tanggal default (tahun ini)
    const now = new Date();
    tglDari = `${now.getFullYear()}-01-01`;
    tglSampai = `${now.getFullYear()}-12-31`;

    bindSearch();
    bindPagination();
    loadDaftarWp();
});

// =======================
// LOAD DAFTAR WP
// =======================
function loadDaftarWp() {
    const tbody = document.getElementById('daftar-wp-body');
    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </td>
        </tr>`;

    fetch('/api/skprd/daftar-wp', {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({
            tgl_dari: tglDari,
            tgl_sampai: tglSampai,
            q: currentQuery,
            page: currentPage,
            per_page: perPage
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'ok' && Array.isArray(res.data)) {
            renderDaftarWp(res);
        } else {
            console.error('Response tidak valid:', res);
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-5">Format data tidak valid</td></tr>`;
        }
    })
    .catch(err => {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-5">Gagal memuat data</td></tr>`;
    });
}

function renderDaftarWp(res) {
    const tbody = document.getElementById('daftar-wp-body');
    tbody.innerHTML = '';

    if (res.data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
                    Tidak ada data ditemukan
                </td>
            </tr>`;
        updatePagination(res.meta);
        return;
    }

    res.data.forEach((wp, i) => {
        tbody.innerHTML += `
            <tr class="align-middle">
                <td class="text-center">${(currentPage - 1) * perPage + i + 1}</td>
                <td><strong>${wp.npwpd}</strong></td>
                <td>${wp.nama_wp}</td>
                <td class="text-muted small">${wp.alamat_wp || '-'}</td>
                <td class="text-end">${rupiah(wp.total_tagihan)}</td>
                <td class="text-end ${wp.total_sisa > 0 ? 'text-danger' : 'text-success'}">
                    ${rupiah(wp.total_sisa)}
                </td>
                <td class="text-center">${wp.jumlah_sk}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary btn-detail-wp"
                            data-npwpd="${wp.npwpd}"
                            data-nama="${wp.nama_wp}">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                </td>
            </tr>
        `;
    });

    updatePagination(res.meta);

    // Event listener tombol detail
    document.querySelectorAll('.btn-detail-wp').forEach(btn => {
        btn.addEventListener('click', function() {
            const npwpd = this.getAttribute('data-npwpd');
            const nama = this.getAttribute('data-nama');
            showWpDetailModal(npwpd, nama);
        });
    });
}

// =======================
// MODAL DETAIL TAGIHAN WP
// =======================
function showWpDetailModal(npwpd, namaWp) {
    const modalEl = document.getElementById('modalWpDetail');
    const modal = new bootstrap.Modal(modalEl);

    document.getElementById('modalWpTitle').textContent = `Detail Tagihan - ${namaWp} (${npwpd})`;
    document.getElementById('modalWpContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-3">Memuat detail tagihan...</div>
        </div>`;

    modal.show();

    fetch('/api/skprd/wp-tagihan', {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({
            tgl_dari: tglDari,
            tgl_sampai: tglSampai,
            npwpd: npwpd
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(res => {
        if (res.status === 'ok') {
            renderModalWpDetail(res.data || []);
        } else {
            throw new Error(res.message || 'Response tidak valid');
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('modalWpContent').innerHTML = `
            <div class="alert alert-danger">
                <strong>Gagal memuat detail tagihan</strong><br>
                ${err.message}<br>
                <small>Silakan cek console untuk detail error</small>
            </div>`;
    });
}

function renderModalWpDetail(data) {
    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>SK</th>
                        <th>Jenis Pajak</th>
                        <th class="text-end">Ketetapan</th>
                        <th class="text-end">Bayar</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>`;

    if (!data || data.length === 0) {
        html += `<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada tagihan untuk WP ini</td></tr>`;
    } else {
        data.forEach((row, i) => {
            const pajak = Number(row.jml_pajak ?? row.JML_PAJAK ?? 0);
            const bayar = Number(row.jml_bayar ?? row.JML_BAYAR ?? row.JML_TBP ?? 0);
            const sisa  = Number(row.jml_sisa ?? row.JML_SISA ?? 0);

            let statusBadge = `<span class="badge bg-danger">Belum Bayar</span>`;
            if (bayar >= pajak) {
                statusBadge = `<span class="badge bg-success">Lunas</span>`;
            } else if (bayar > 0) {
                statusBadge = `<span class="badge bg-warning">Sebagian</span>`;
            }

            html += `
                <tr>
                    <td>${i + 1}</td>
                    <td>
                        <small>${row.tgl_sk ?? row.TGL_SK ?? '-'}</small><br>
                        <strong>${row.no_sk ?? row.NO_SK ?? row.NO_SPTPD ?? '-'}</strong>
                    </td>
                    <td>${getJenisLabel(row.JENIS_PAJAK ?? row.JENIS ?? row.jenis_pajak ?? 'LAINNYA')}</td>
                    <td class="text-end">${rupiah(pajak)}</td>
                    <td class="text-end text-success">${rupiah(bayar)}</td>
                    <td class="text-end ${sisa > 0 ? 'text-danger' : 'text-success'}">${rupiah(sisa)}</td>
                    <td class="text-center">${statusBadge}</td>
                </tr>`;
        });
    }

    html += `</tbody></table></div>`;

    // Tambahkan info total di atas tabel
    const totalTagihan = data.reduce((sum, row) => sum + Number(row.jml_pajak ?? row.JML_PAJAK ?? 0), 0);
    const totalSisa    = data.reduce((sum, row) => sum + Number(row.jml_sisa ?? row.JML_SISA ?? 0), 0);

    html = `
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Total Tagihan:</strong> ${rupiah(totalTagihan)}
            </div>
            <div class="col-md-6 text-end">
                <strong>Total Sisa:</strong> <span class="${totalSisa > 0 ? 'text-danger' : 'text-success'}">${rupiah(totalSisa)}</span>
            </div>
        </div>
    ` + html;

    document.getElementById('modalWpContent').innerHTML = html;
}

// =======================
// SEARCH & PAGINATION
// =======================
function bindSearch() {
    const input = document.getElementById('searchInput');
    if (!input) return;

    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            currentQuery = input.value.trim();
            currentPage = 1;
            loadDaftarWp();
        }, 500);
    });
}

function bindPagination() {
    document.getElementById('btnPrev')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadDaftarWp();
        }
    });

    document.getElementById('btnNext')?.addEventListener('click', () => {
        if (currentPage < totalPage) {
            currentPage++;
            loadDaftarWp();
        }
    });
}

function updatePagination(meta) {
    totalPage = Math.ceil((meta.total || 0) / (meta.per_page || 20)) || 1;
    
    const pageInfo = document.getElementById('pageInfo');
    if (pageInfo) {
        pageInfo.textContent = `Halaman ${meta.page || 1} dari ${totalPage} (${meta.total || 0} data)`;
    }

    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');

    if (btnPrev) btnPrev.disabled = (meta.page || 1) <= 1;
    if (btnNext) btnNext.disabled = (meta.page || 1) >= totalPage;
}

// =======================
// UTIL
// =======================
function rupiah(value) {
    return 'Rp ' + (parseInt(value) || 0).toLocaleString('id-ID');
}

function getJenisLabel(jenis) {
    const labels = {
        'HOTEL': 'PBJT Jasa Perhotelan',
        'RESTO': 'PBJT Makan Minum',
        'HIBURAN': 'Pajak Hiburan',
        'PARKIR': 'Pajak Parkir',
        'MGOLC': 'Pajak MBLB',
        'PENER': 'PBJT Tenaga Listrik',
        'REKLA': 'Pajak Reklame',
        'AIRTN': 'Pajak Air Tanah',
        'LAINNYA': 'Pajak Lainnya'
    };
    return labels[jenis?.toUpperCase()] || jenis?.toUpperCase() || 'Pajak Lainnya';
}

// Headers (sama seperti combined.js)
function headers() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    };
}