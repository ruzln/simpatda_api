// =======================
// JENIS PAJAK LOOKUP (GLOBAL)
// =======================
window.JENIS_PAJAK_LABEL = {
    rekla : 'Pajak Reklame',
    resto : 'PBJT Makan Minum',
    pener : 'PBJT Tenaga Listrik',
    airtn : 'Pajak Air Tanah',
    hotel : 'PBJT Jasa Pehotelan',
    mgolc : 'Pajak MBLB'
}

// =======================
// HELPER
// =======================
window.getJenisLabel = function (kode) {
    if (!kode) return '-'
    return window.JENIS_PAJAK_LABEL[kode.toLowerCase()] ?? kode
}
