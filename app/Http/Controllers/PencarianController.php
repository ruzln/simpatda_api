<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Ketetapan;
use App\Models\WajibPajak;
use Illuminate\Http\Request;


class PencarianController extends Controller
{
    public function index()
    {
        $wajibPajakList = WajibPajak::pluck('NAMA', 'NAMA_PEMILIK');
        return view('pencarian.index', compact('wajibPajakList'));
    }

    public function SelfAsessment()
    {
        // $wajibPajakList = WajibPajak::pluck('NAMA', 'NAMA_PEMILIK');
        return view('pencarian.SelfAsessment');
    }

    public function hasilPencarian(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $nama = $request->input('nama');
    
        // Cek apakah input tanggal_awal, tanggal_akhir, dan nama tidak kosong
        if ($tanggalAwal && $tanggalAkhir && $nama) {
            $result = Ketetapan::join('KETETAPAN_DETAIL', 'KETETAPAN.ID', '=', 'KETETAPAN_DETAIL.ID_KETETAPAN')
                ->whereBetween('KETETAPAN.TANGGAL', [$tanggalAwal, $tanggalAkhir])
                ->where('KETETAPAN.NAMA', $nama)
                ->select(
                    'KETETAPAN.TANGGAL AS TGL_KETETAPAN',
                    'KETETAPAN.TGLJATUHTEMPO AS TGLJATUHTEMPO',
                    'KETETAPAN.NOMOR AS NO_KETETAPAN',
                    'KETETAPAN.NPWPD',
                    'KETETAPAN.NAMA',
                    'KETETAPAN.KET AS MASA_PAJAK',
                    'KETETAPAN_DETAIL.JUMLAH AS POKOK',
                    'KETETAPAN.JML_SELURUH'
                )
                ->orderBy('KETETAPAN.JENISPAJAK')
                ->get();
    
            // return response()->json(['result' => $result]);
            return View ('pencarian.index', ['result' => $result]);
        } else {
            return response()->json(['result' => []]);
        }
    }
}
