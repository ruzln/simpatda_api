// =======================
// STATE GLOBAL
// =======================

let currentJenis  = ''
let currentPage   = 1
let perPage       = 10        // ⬅️ SATU SUMBER
let currentQuery  = ''
let currentStatus = ''
let totalPage     = 1

let tglDari
let tglSampai
let rangePicker

document.addEventListener('DOMContentLoaded', () => {
    setPresetTanggal('year')
    bindSearch()
    bindPagination()
    initRangePicker()
})

function renderPagination(meta) {
    document.getElementById('pagination-info').innerHTML =
        `Menampilkan ${meta.from}–${meta.to} dari ${meta.total} data`
}

function initRangePicker() {
    rangePicker = flatpickr("#rangeTanggal", {
        mode: "range",
        dateFormat: "Y-m-d",
        locale: "id",
        allowInput: true
    })

    document.getElementById('btnApplyRange').onclick = applyRangeTanggal
    document.getElementById('btnResetRange').onclick = resetRangeTanggal
}

function applyRangeTanggal() {
    const dates = rangePicker.selectedDates

    if (dates.length !== 2) {
        alert('Pilih range tanggal lengkap')
        return
    }

    tglDari   = formatDateLocal(dates[0])
    tglSampai = formatDateLocal(dates[1])

    currentJenis = ''
    currentPage  = 1

    loadSummarySelf()
    clearTable()
}
function resetRangeTanggal() {
    rangePicker.clear()
    setPresetTanggal('year')
}

function formatDateLocal(date) {
    const y = date.getFullYear()
    const m = String(date.getMonth() + 1).padStart(2, '0')
    const d = String(date.getDate()).padStart(2, '0')
    return `${y}-${m}-${d}`
}

function setPresetTanggal(type) {
    const now = new Date()

    if (type === 'today') {
        tglDari   = formatDateLocal(now)
        tglSampai = formatDateLocal(now)
    }

    if (type === 'month') {
        tglDari   = formatDateLocal(new Date(now.getFullYear(), now.getMonth(), 1))
        tglSampai = formatDateLocal(now)
    }

    if (type === 'year') {
        tglDari   = formatDateLocal(new Date(now.getFullYear(), 0, 1))
        tglSampai = formatDateLocal(now)
    }

    currentJenis = ''
    currentPage = 1

    loadSummarySelf()
    clearTable()
}

// =======================
// INIT
// =======================
document.addEventListener('DOMContentLoaded', () => {
    setPresetTanggal('year') // sudah trigger summary
    bindSearch()
    bindPagination()
})


function headers() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
    }
}

// =======================
// SUMMARY CARD
// =======================
function loadSummarySelf() {
    fetch('/api/skprd/self/summary', {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({
            tgl_dari: tglDari,
            tgl_sampai: tglSampai
        })
    })
    .then(r => r.json())
    .then(res => renderSummaryCards(res.summary_by_jenis))
    .catch(err => console.error('loadSummarySelf error:', err))
}


function renderSummaryCards(data) {
    const wrap = document.getElementById('summary-cards')
    wrap.innerHTML = ''

    if (!data || !data.length) {
        wrap.innerHTML = `
            <div class="col-12 text-muted">
                Tidak ada data
            </div>
        `
        return
    }

    data.forEach(item => {
        const col = document.createElement('div')
        col.className = 'col-xl-3 col-md-6 mb-4'

        col.innerHTML = `
            <div class="card border-left-success shadow h-100 py-2 summary-card"
                 style="cursor:pointer">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                 ${getJenisLabel(item.jenis)}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${item.total_data}
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
        `

        col.onclick = () => {
            currentJenis = item.jenis
            currentPage  = 1
            loadTableSelf()
        }

        wrap.appendChild(col)
    })
}


// =======================
// SEARCH
// =======================
function bindSearch() {
    const input = document.getElementById('searchInput')
    let timer

    input.addEventListener('keyup', () => {
        clearTimeout(timer)
        timer = setTimeout(() => {
            currentQuery = input.value.trim()
            currentPage = 1
            loadTableSelf()
        }, 500)
    })
}

// =======================
// PAGINATION
// =======================
function bindPagination() {
    document.getElementById('btnPrev').onclick = () => {
        if (currentPage > 1) {
            currentPage--
            loadTableSelf()
        }
    }

    document.getElementById('btnNext').onclick = () => {
        if (currentPage < totalPage) {   // ⬅️ FIX PENTING
            currentPage++
            loadTableSelf()
        }
    }
}

// =======================
// LOAD TABLE
// =======================
function loadTableSelf() {
    if (!currentJenis) return

    fetch('/api/skprd/self', {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({
            tgl_dari: tglDari,
            tgl_sampai: tglSampai,
            jenis: currentJenis,
            q: currentQuery,
            status: currentStatus,   // ⬅️ TAMBAH
            page: currentPage,
            per_page: perPage        // ⬅️ PAKAI SATU VAR
        })
    })
    .then(r => r.json())
    .then(renderTableSelf)
}

function renderTableSelf(res) {
    const tbody = document.getElementById('skprd-table-body')
    tbody.innerHTML = ''

    if (!res.data || res.data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    Tidak ada data
                </td>
            </tr>`
        updatePagination(res.meta)
        return
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

        tbody.innerHTML += `
            <tr class="align-middle">
                <!-- NO -->
                <td class="text-center">
                    ${(currentPage - 1) * perPage + i + 1}
                </td>

                <!-- SK -->
                <td>
                    <div class="text-xs text-muted">${row.tgl_sk}</div>
                    <div class="font-medium text-slate-800">${row.no_sk}</div>
                </td>

                <!-- WP -->
                <td>
                    <div class="font-medium text-slate-800">
                        ${row.nama_wp}
                    </div>
                    <div class="text-xs text-muted">
                        NPWPD: ${row.npwpd}
                    </div>
                </td>

                <!-- KETETAPAN -->
                <td class="text-right font-semibold">
                    ${rupiah(row.jml_pajak)}
                </td>

                <!-- BAYAR -->
                <td class="text-right text-emerald-600">
                    ${rupiah(row.jml_bayar)}
                </td>

                <!-- SISA -->
                <td class="text-right ${row.jml_sisa > 0 ? 'text-rose-600' : 'text-emerald-600'}">
                    ${rupiah(row.jml_sisa)}
                </td>

                <!-- STATUS -->
                <td class="text-center">
                    ${statusBadge}
                </td>
            </tr>
        `
    })

    updatePagination(res.meta)

    document.getElementById('table-title').innerText =
        `Detail SPTPD ${getJenisLabel(currentJenis)}`
}

// =======================
// PAGINATION INFO
// =======================
function updatePagination(meta) {
    totalPage = Math.ceil(meta.total / meta.per_page) // ⬅️ UPDATE GLOBAL

    document.getElementById('pageInfo').innerText =
        `Page ${meta.page} / ${totalPage}`

    document.getElementById('btnPrev').disabled = meta.page <= 1
    document.getElementById('btnNext').disabled = meta.page >= totalPage
}


function clearTable() {
    document.getElementById('skprd-table-body').innerHTML = `
        <tr>
            <td colspan="10" class="text-center text-muted">
                Klik salah satu card untuk melihat data
            </td>
        </tr>
    `
}


// =======================
// UTIL
// =======================
function rupiah(n) {
    return 'Rp ' + (n ?? 0).toLocaleString('id-ID')
}

document.getElementById('filterStatus').addEventListener('change', e => {
    currentStatus = e.target.value
    currentPage = 1
    loadTableSelf()
})

document.getElementById('pageSize').addEventListener('change', e => {
    perPage = parseInt(e.target.value)
    currentPage = 1
    loadTableSelf()
})
