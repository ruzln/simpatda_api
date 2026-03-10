<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    public function getData(Request $request)
    {
        $tahunSekarang = Carbon::now()->year;

        $data = Pegawai::all();
        //     ->whereRaw('EXTRACT(YEAR FROM TANGGAL) = ?', [$tahunSekarang])
        //     ->groupBy('JENISPAJAK', 'JML_SELURUH')
        //     ->get();
    
        // $formattedData = [];
    
        // foreach ($data as $item) {
        //     if (!isset($formattedData[$item->JENISPAJAK])) {
        //         $formattedData[$item->JENISPAJAK] = 0;
        //     }
        //     $formattedData[$item->JENISPAJAK] += $item->JML_SELURUH;
        // }
    
        // $formattedOutput = [];
        // foreach ($formattedData as $jenisPajak => $jumlah) {
        //     $formattedOutput[$jenisPajak] = 'Rp ' . number_format($jumlah, 2, ',', '.');
        // }
    
        return response()->json($data);
}
}