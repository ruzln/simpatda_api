<?php

namespace App\Http\Controllers;

// use DB;
use App\Models\Ketetapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class KetetapanController extends Controller
{
    public function SelfAsessment(Request $request)
    {
        $tglDari = $request->input('tglmulai');
        $tglSampai = $request->input('tglsampai');
        $inJenisPajak  = $request->input('in_jenispajak');

        $result = DB::connection('firebird')
            ->select("SELECT * FROM DAFTAR_SPTPD_V2(:TGLDARI, :TGLSAMPAI, :IN_JENISPAJAK)", [
                'TGLDARI' => $tglDari,
                'TGLSAMPAI' => $tglSampai,
                'IN_JENISPAJAK' => $inJenisPajak
            ]);

        // $result = DB::select ("CALL DAFTAR_SPTPD_V2 ('$param_1','$param_2','$param_3')");

        // DB::select('EXEC DAFTAR_SPTPD_V2(TGLDARI, TGLSAMPAI, IN_JENISPAJAK)', array($tglawal,$tglakhir,$jenis));

        // $result = Ketetapan::join('KETETAPAN_DETAIL', 'KETETAPAN.ID', '=', 'KETETAPAN_DETAIL.ID_KETETAPAN')
        //     ->whereBetween('KETETAPAN.TANGGAL', ['2023-01-01', '2023-12-30'])
        //     ->whereIn('KETETAPAN.JENISPAJAK', ['AIRTN'])
        //     ->whereIn('KETETAPAN.NAMA', [
        //         'PT. KERRY SAWIT INDONESIA',
        //         'PT. MUSTIKA SEMBULUH',
        //         'PT. RIMBA HARAPAN SAKTI',
        //         'PT. SARANA TITIAN PERMATA'
        //     ])
        //     ->orderBy('KETETAPAN.TANGGAL')
        //     ->select(
        //         DB::raw('EXTRACT(DAY FROM KETETAPAN.TANGGAL) || \'/\' || EXTRACT(MONTH FROM KETETAPAN.TANGGAL) || \'/\' || EXTRACT(YEAR FROM KETETAPAN.TANGGAL) AS TGL_KETETAPAN'),
        //         DB::raw('EXTRACT(DAY FROM KETETAPAN.TGLJATUHTEMPO) || \'/\' || EXTRACT(MONTH FROM KETETAPAN.TGLJATUHTEMPO) || \'/\' || EXTRACT(YEAR FROM KETETAPAN.TGLJATUHTEMPO) AS TGLJATUHTEMPO'),
        //         'KETETAPAN.NOMOR AS NO_KETETAPAN',
        //         'KETETAPAN.NPWPD',
        //         'KETETAPAN.NAMA',
        //         'KETETAPAN.KET AS MASA_PAJAK',
        //         'KETETAPAN_DETAIL.MASA',
        //         'KETETAPAN_DETAIL.JUMLAH AS POKOK',
        //         'KETETAPAN.JML_SELURUH'
        //     )
        //     ->get();

        return response()->json(['data' => $result]);
    }
}
