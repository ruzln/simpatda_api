<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PbbController extends Controller
{
    /**
     * =====================================================
     * PBB REALISASI (ADAPTER API)
     * =====================================================
     */
    public function realisasi(Request $request)
    {
        $request->validate([
            'tgl_dari'     => 'required|date',
            'tgl_sampai'   => 'required|date',

            'tahun_pajak'  => 'nullable|string',
            'status'       => 'nullable|in:lunas,kurang,lebih',
            'q'            => 'nullable|string',

            'page'         => 'nullable|integer|min:1',
            'per_page'     => 'nullable|integer|min:1|max:100',
        ]);

        $page     = (int) $request->input('page', 1);
        $perPage  = (int) $request->input('per_page', 20);

        /**
         * =================================================
         * FETCH API PBB EKSTERNAL
         * =================================================
         */
        $response = Http::timeout(30)->get(
            'http://103.157.26.47:81/api/pbb/front',
            [
                'tanggal_awal'  => $request->tgl_dari,
                'tanggal_akhir' => $request->tgl_sampai,
            ]
        );

        if (!$response->ok()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data PBB'
            ], 500);
        }

        $payload = $response->json();

        $rows = collect($payload['data'] ?? []);

        /**
         * =================================================
         * NORMALISASI + DERIVED STATUS
         * =================================================
         */
        $rows = $rows->map(function ($r) {
            $kurang = (int) $r['kurang_bayar'];
            $lebih  = (int) $r['lebih_bayar'];

            if ($lebih > 0) {
                $status = 'lebih';
            } elseif ($kurang > 0) {
                $status = 'kurang';
            } else {
                $status = 'lunas';
            }

            return [
                'nop'            => $r['nop'],
                'tahun_pajak'    => $r['thn_pajak'],

                'nama_wp'        => $r['nm_wp'],
                'alamat_wp'      => $r['jln_wp'],
                'kelurahan'      => $r['kelurahan_op'],

                'pokok'          => (int) $r['pokok_pbb_sppt'],
                'denda'          => (int) $r['denda'],
                'dibayar'        => (int) $r['total_yg_sdh_dibayar'],
                'kurang_bayar'   => $kurang,
                'lebih_bayar'    => $lebih,

                'pembayaran_ke'  => (int) $r['pembayaran_ke'],
                'tgl_bayar'      => substr($r['tgl_bayar'], 0, 10),

                'status'         => $status,
            ];
        });

        /**
         * =================================================
         * FILTER: TAHUN PAJAK
         * =================================================
         */
        if ($request->filled('tahun_pajak')) {
            $rows = $rows->filter(fn ($r) =>
                $r['tahun_pajak'] === $request->tahun_pajak
            );
        }

        /**
         * =================================================
         * FILTER: STATUS
         * =================================================
         */
        if ($request->filled('status')) {
            $rows = $rows->filter(fn ($r) =>
                $r['status'] === $request->status
            );
        }

        /**
         * =================================================
         * SEARCH (NOP / NAMA / KELURAHAN)
         * =================================================
         */
        if ($request->filled('q')) {
            $q = strtoupper($request->q);

            $rows = $rows->filter(fn ($r) =>
                str_contains(strtoupper($r['nop']), $q)
                || str_contains(strtoupper($r['nama_wp']), $q)
                || str_contains(strtoupper($r['kelurahan']), $q)
            );
        }

        /**
         * =================================================
         * SUMMARY
         * =================================================
         */
        $summary = [
            'total_transaksi' => $rows->count(),
            'total_realisasi' => $rows->sum('dibayar'),
            'lunas'           => $rows->where('status', 'lunas')->count(),
            'kurang_bayar'    => $rows->where('status', 'kurang')->count(),
            'lebih_bayar'     => $rows->where('status', 'lebih')->count(),
        ];

        /**
         * =================================================
         * PAGINATION
         * =================================================
         */
        $total = $rows->count();

        $data = $rows
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return response()->json([
            'status'  => 'ok',

            'periode' => [
                'tgl_dari'   => $request->tgl_dari,
                'tgl_sampai' => $request->tgl_sampai,
            ],

            'summary' => $summary,

            'meta' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],

            'data' => $data,
        ]);
    }
}