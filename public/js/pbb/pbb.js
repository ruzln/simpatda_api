// =======================
// GLOBAL STATE
// =======================
let rawData      = []
let filteredData = []

let currentPage  = 1
let perPage      = 20
let totalPage    = 1
let currentQuery = ''

let tglDari
let tglSampai
let currentTahun     = ''
let currentKelurahan = ''
// =======================
// INIT
// =======================
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('filterTahun').addEventListener('change', e => {
        currentTahun = e.target.value
        currentPage = 1
        applyFilter()
    })

    document.getElementById('filterKelurahan').addEventListener('change', e => {
        currentKelurahan = e.target.value
        currentPage = 1
        applyFilter()
    })
    setDefaultTanggal()
    bindSearch()
    bindPagination()
    bindPageSize()
    loadPbbData()
})

// =======================
// DEFAULT TANGGAL
// =======================
function setDefaultTanggal() {
    const now = new Date()
    const y = now.getFullYear()

    tglDari   = `${y}-01-01`
    tglSampai = formatDate(now)

    document.getElementById('periodeText').innerText =
        `1 Januari ${y} – ${now.toLocaleDateString('id-ID')}`
}

function formatDate(d) {
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
}

function buildFilterOptions(data) {
    const tahunSet = {}
    const kelSet   = {}

    data.forEach(r => {
        if (r.thn_pajak) tahunSet[r.thn_pajak] = true
        if (r.kelurahan_op) kelSet[r.kelurahan_op] = true
    })

    const tahunSelect = document.getElementById('filterTahun')
    const kelSelect   = document.getElementById('filterKelurahan')

    tahunSelect.innerHTML = '<option value="">Semua Tahun</option>'
    kelSelect.innerHTML   = '<option value="">Semua Kelurahan</option>'

    Object.keys(tahunSet).sort().forEach(t => {
        tahunSelect.innerHTML += `<option value="${t}">${t}</option>`
    })

    Object.keys(kelSet).sort().forEach(k => {
        kelSelect.innerHTML += `<option value="${k}">${k}</option>`
    })
}
// =======================
// FETCH API
// =======================
function loadPbbData() {
    const params = new URLSearchParams({
        tgl_dari: tglDari,
        tgl_sampai: tglSampai,
        page: currentPage,
        per_page: perPage,
        q: currentQuery,
        tahun: currentTahun,
        kelurahan: currentKelurahan
    })

    fetch(`http://103.157.26.47:81/api/pbb/front?${params}`)
        .then(r => r.json())
        .then(res => {
            renderTable(res.data)
            updateSummary(res)
            updatePagination(res.meta)
        })
        .catch(console.error)
}

// =======================
// SUMMARY
// =======================
function updateSummary(res) {
    document.getElementById('totalRealisasi').innerText =
        rupiah(res.total_realisasi ?? 0)

    document.getElementById('jumlahData').innerText =
        res.meta?.total ?? 0
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
            currentQuery = input.value.trim().toUpperCase()
            currentPage = 1
            applyFilter()
        }, 400)
    })
}

// =======================
// FILTER & PAGINATION
// =======================
function applyFilter() {
    filteredData = rawData.filter(r => {

        if (currentQuery) {
            const q = currentQuery
            if (
                !(r.nop || '').toUpperCase().includes(q) &&
                !(r.nm_wp || '').toUpperCase().includes(q)
            ) return false
        }

        if (currentTahun && r.thn_pajak !== currentTahun) {
            return false
        }

        if (currentKelurahan && r.kelurahan_op !== currentKelurahan) {
            return false
        }

        return true
    })

    totalPage = Math.max(1, Math.ceil(filteredData.length / perPage))
    renderTable()
}

// =======================
// RENDER TABLE
// =======================
function renderTable(rows) {
    const tbody = document.getElementById('pbb-table-body')
    tbody.innerHTML = ''

    if (!rows || rows.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    Tidak ada data
                </td>
            </tr>`
        return
    }

    rows.forEach((row, i) => {
        tbody.innerHTML += `
            <tr>
                <td class="text-center">
                    ${(currentPage - 1) * perPage + i + 1}
                </td>
                <td>${row.nop}</td>
                <td>
                    <div class="font-weight-semibold">${row.nm_wp}</div>
                    <small class="text-muted">${row.jln_wp}</small>
                </td>
                <td>${row.kelurahan_op}</td>
                <td class="text-right">${rupiah(row.pokok_pbb_sppt)}</td>
                <td class="text-right text-danger">${rupiah(row.denda)}</td>
                <td class="text-right text-success">${rupiah(row.total_yg_sdh_dibayar)}</td>
                <td class="text-right">${rupiah(row.kurang_bayar)}</td>
                <td>${formatTanggal(row.tgl_bayar)}</td>
            </tr>
        `
    })
}

// =======================
// PAGINATION
// =======================
function bindPagination() {
        document.getElementById('btnPrev').onclick = () => {
            if (currentPage > 1) {
                currentPage--
                loadPbbData()
            }
        }

        document.getElementById('btnNext').onclick = () => {
            if (currentPage < totalPage) {
                currentPage++
                loadPbbData()
            }
        }
}

function updatePagination(meta) {
    totalPage = Math.ceil(meta.total / meta.per_page)

    document.getElementById('pageInfo').innerText =
        `Page ${meta.page} / ${totalPage}`

    document.getElementById('btnPrev').disabled = meta.page <= 1
    document.getElementById('btnNext').disabled = meta.page >= totalPage
}

// =======================
// PAGE SIZE
// =======================
function bindPageSize() {
    document.getElementById('pageSize').addEventListener('change', e => {
        perPage = parseInt(e.target.value)
        currentPage = 1
        loadPbbData()
    })
}

// =======================
// UTIL
// =======================
function rupiah(n) {
    return 'Rp ' + (parseInt(n || 0)).toLocaleString('id-ID')
}

function formatTanggal(tgl) {
    if (!tgl) return '-'
    if (tgl.includes('/')) return tgl
    return new Date(tgl).toLocaleDateString('id-ID')
}