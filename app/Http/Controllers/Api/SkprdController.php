<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\SkprdResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WpTagihanExport;

class SkprdController extends Controller
{
    /**
     * SKPRD - OFFICE ASSESSMENT (REKLA, AIRTN)
     */
    public function office(Request $request)
    {
        $request->validate([
            'tgl_dari'   => 'required|date',
            'tgl_sampai' => 'required|date',
            'jenis'      => 'required|string',
            'flag'       => 'required|integer',
            'q'          => 'nullable|string',
            'status'     => 'nullable|in:lunas,sebagian,belum',
            'page'       => 'nullable|integer|min:1',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ]);

        $rows = collect(DB::connection('firebird')->select(
            "SELECT * FROM DAFTAR_SKPRD_V2(?, ?, ?, ?)",
            [
                $request->tgl_dari,
                $request->tgl_sampai,
                strtoupper($request->jenis),
                $request->flag
            ]
        ));

        if ($request->filled('q')) {
            $q = strtoupper($request->q);
            $rows = $rows->filter(fn ($r) =>
                str_contains(strtoupper($r->NAMA_WP ?? ''), $q)
                || str_contains(strtoupper($r->NPWPD ?? ''), $q)
                || str_contains(strtoupper($r->NO_SK ?? ''), $q)
            );
        }

        if ($request->filled('status')) {
            if ($request->status === 'lunas') {
                $rows = $rows->filter(fn ($r) => isset($r->JML_SISA) && (int)$r->JML_SISA === 0);
            } elseif ($request->status === 'sebagian') {
                $rows = $rows->filter(fn ($r) => 
                    isset($r->JML_TBP) && (int)$r->JML_TBP > 0 &&
                    isset($r->JML_SISA) && (int)$r->JML_SISA > 0
                );
            } elseif ($request->status === 'belum') {
                $rows = $rows->filter(fn ($r) => 
                    isset($r->JML_TBP) && (int)$r->JML_TBP == 0
                );
            }
        }

        return $this->buildResponse($rows, 'JML_TBP');
    }

    /**
     * SKPRD - SELF ASSESSMENT (HOTEL, RESTO, dll)
     */
    public function self(Request $request)
    {
        $request->validate([
            'tgl_dari'   => 'required|date',
            'tgl_sampai' => 'required|date',
            'jenis'      => 'required|string',
            'q'          => 'nullable|string',
            'jenis_card' => 'nullable|string',
            'page'       => 'nullable|integer|min:1',
            'per_page'   => 'nullable|integer|min:1|max:100',
            'status'     => 'nullable|in:lunas,sebagian,belum',
        ]);

        $rows = collect(DB::connection('firebird')->select(
            "SELECT * FROM DAFTAR_SPTPD_V2(?, ?, ?)",
            [
                $request->tgl_dari,
                $request->tgl_sampai,
                strtoupper($request->jenis)
            ]
        ));

        if ($request->filled('q')) {
            $q = strtoupper($request->q);
            $rows = $rows->filter(fn ($r) =>
                str_contains(strtoupper($r->NAMA_WP ?? ''), $q)
                || str_contains(strtoupper($r->NPWPD ?? ''), $q)
                || str_contains(strtoupper($r->NO_SPTPD ?? $r->NO_SK ?? ''), $q)
            );
        }

        if ($request->filled('jenis_card')) {
            $jenisCard = strtoupper($request->jenis_card);
            $rows = $rows->filter(fn ($r) =>
                strtoupper($r->JENIS_PAJAK ?? '') === $jenisCard
            );
        }

        if ($request->filled('status')) {
            if ($request->status === 'lunas') {
                $rows = $rows->filter(fn ($r) => isset($r->JML_SISA) && (int)$r->JML_SISA === 0);
            } elseif ($request->status === 'sebagian') {
                $rows = $rows->filter(fn ($r) => 
                    isset($r->JML_BAYAR) && (int)$r->JML_BAYAR > 0 &&
                    isset($r->JML_SISA) && (int)$r->JML_SISA > 0
                );
            } elseif ($request->status === 'belum') {
                $rows = $rows->filter(fn ($r) => 
                    isset($r->JML_BAYAR) && (int)$r->JML_BAYAR == 0
                );
            }
        }

        return $this->buildResponse($rows, 'JML_BAYAR');
    }

    /**
     * SUMMARY - OFFICE (CARD ONLY)
     */
    public function summaryOffice(Request $request)
    {
        $request->validate([
            'tgl_dari'   => 'required|date',
            'tgl_sampai' => 'required|date',
            'flag'       => 'required|integer',
        ]);

        return $this->summaryByJenis(
            ['REKLA', 'AIRTN'],
            fn ($jenis) => DB::connection('firebird')->select(
                "SELECT * FROM DAFTAR_SKPRD_V2(?, ?, ?, ?)",
                [$request->tgl_dari, $request->tgl_sampai, $jenis, $request->flag]
            ),
            'JML_TBP'
        );
    }

    /**
     * SUMMARY - SELF (CARD ONLY)
     */
    public function summarySelf(Request $request)
    {
        $request->validate([
            'tgl_dari'   => 'required|date',
            'tgl_sampai' => 'required|date',
        ]);

        return $this->summaryByJenis(
            ['HOTEL', 'RESTO', 'HIBURAN', 'PARKIR', 'MGOLC', 'PENER'],
            fn ($jenis) => DB::connection('firebird')->select(
                "SELECT * FROM DAFTAR_SPTPD_V2(?, ?, ?)",
                [$request->tgl_dari, $request->tgl_sampai, $jenis]
            ),
            'JML_BAYAR'
        );
    }

    /**
     * COMBINED SUMMARY - untuk card summary (self + office)
     */
    public function combinedSummary(Request $request)
    {
        $request->validate([
            'tgl_dari'   => 'required|date',
            'tgl_sampai' => 'required|date',
            'flag'       => 'sometimes|integer',
        ]);

        $flag = $request->input('flag', 0);

        $jenisSelf   = ['HOTEL', 'RESTO', 'HIBURAN', 'PARKIR', 'MGOLC', 'PENER', 'LAINNYA'];
        $jenisOffice = ['REKLA', 'AIRTN'];

        $summary = collect();

        foreach ($jenisSelf as $jenis) {
            $rows = collect(DB::connection('firebird')->select(
                "SELECT * FROM DAFTAR_SPTPD_V2(?, ?, ?)",
                [$request->tgl_dari, $request->tgl_sampai, strtoupper($jenis)]
            ));

            if ($rows->isEmpty()) continue;

            $summary->push([
                'jenis'        => strtoupper(trim($jenis)),
                'total_data'   => $rows->count(),
                'total_pajak'  => (int) $rows->sum('JML_PAJAK'),
            ]);
        }

        foreach ($jenisOffice as $jenis) {
            $rows = collect(DB::connection('firebird')->select(
                "SELECT * FROM DAFTAR_SKPRD_V2(?, ?, ?, ?)",
                [$request->tgl_dari, $request->tgl_sampai, strtoupper($jenis), $flag]
            ));

            if ($rows->isEmpty()) continue;

            $summary->push([
                'jenis'        => strtoupper(trim($jenis)),
                'total_data'   => $rows->count(),
                'total_pajak'  => (int) $rows->sum('JML_PAJAK'),
            ]);
        }

        $aggregated = $summary
            ->groupBy('jenis')
            ->map(fn ($group) => [
                'jenis'       => $group->first()['jenis'],
                'total_data'  => $group->sum('total_data'),
                'total_pajak' => $group->sum('total_pajak'),
            ])
            ->values();

        return response()->json([
            'status'           => 'ok',
            'summary_by_jenis' => $aggregated,
        ]);
    }

    /**
     * COMBINED DETAIL - untuk table (self + office)
     */
    public function combined(Request $request)
    {
        $request->validate([
            'tgl_dari'   => 'required|date',
            'tgl_sampai' => 'required|date',
            'jenis'      => 'nullable|string',
            'npwpd'      => 'nullable|string',
            'nama_wp'    => 'nullable|string',
            'flag'       => 'sometimes|integer',
            'q'          => 'nullable|string',
            'status'     => 'nullable|in:lunas,sebagian,belum',
            'page'       => 'nullable|integer|min:1',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ]);

        $flag = $request->input('flag', 0);
        $jenis = $request->jenis ? strtoupper(trim($request->jenis)) : null;
        $npwpd = $request->npwpd ? strtoupper(trim($request->npwpd)) : null;
        $namaWp = $request->nama_wp ? strtoupper(trim($request->nama_wp)) : null;

        $allRows = collect();

        // Mode cari per WP/NPWPD → ambil semua jenis
        if ($npwpd || $namaWp) {
            // Self - semua jenis
            $jenisSelf = ['HOTEL', 'RESTO', 'HIBURAN', 'PARKIR', 'MGOLC', 'PENER', 'LAINNYA'];
            foreach ($jenisSelf as $j) {
                $rows = collect(DB::connection('firebird')->select(
                    "SELECT * FROM DAFTAR_SPTPD_V2(?, ?, ?)",
                    [$request->tgl_dari, $request->tgl_sampai, $j]
                ));
                $allRows = $allRows->merge($rows);
            }

            // Office - semua jenis
            $jenisOffice = ['REKLA', 'AIRTN'];
            foreach ($jenisOffice as $j) {
                $rows = collect(DB::connection('firebird')->select(
                    "SELECT * FROM DAFTAR_SKPRD_V2(?, ?, ?, ?)",
                    [$request->tgl_dari, $request->tgl_sampai, $j, $flag]
                ));
                $allRows = $allRows->merge($rows);
            }
        } 
        // Mode biasa: per jenis (wajib ada jenis)
        elseif ($jenis) {
            $isSelf   = in_array($jenis, ['HOTEL', 'RESTO', 'HIBURAN', 'PARKIR', 'MGOLC', 'PENER', 'LAINNYA']);
            $isOffice = in_array($jenis, ['REKLA', 'AIRTN']);

            if (!$isSelf && !$isOffice) {
                return response()->json(['status' => 'error', 'message' => 'Jenis pajak tidak dikenal'], 422);
            }

            if ($isSelf) {
                $allRows = $allRows->merge(collect(DB::connection('firebird')->select(
                    "SELECT * FROM DAFTAR_SPTPD_V2(?, ?, ?)",
                    [$request->tgl_dari, $request->tgl_sampai, $jenis]
                )));
            }

            if ($isOffice) {
                $allRows = $allRows->merge(collect(DB::connection('firebird')->select(
                    "SELECT * FROM DAFTAR_SKPRD_V2(?, ?, ?, ?)",
                    [$request->tgl_dari, $request->tgl_sampai, $jenis, $flag]
                )));
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Jenis atau NPWPD/Nama WP harus diisi'], 422);
        }

        Log::info('Combined rows count after merge: ' . $allRows->count());

        // SEARCH q (jika ada dan bukan mode WP)
        if ($request->filled('q') && !$npwpd && !$namaWp) {
            $q = strtoupper($request->q);
            $allRows = $allRows->filter(function ($r) use ($q) {
                return str_contains(strtoupper($r->NAMA_WP ?? ''), $q)
                    || str_contains(strtoupper($r->NPWPD ?? ''), $q)
                    || str_contains(strtoupper($r->NO_SK ?? $r->NO_SPTPD ?? ''), $q);
            });
        }

        // Filter NPWPD (exact)
        if ($npwpd) {
            $allRows = $allRows->filter(fn($r) => strtoupper($r->NPWPD ?? '') === $npwpd);
        }

        // Filter nama WP (partial)
        if ($namaWp) {
            $allRows = $allRows->filter(fn($r) => str_contains(strtoupper($r->NAMA_WP ?? ''), $namaWp));
        }

        // Filter status dengan null safety ekstra (di akhir setelah merge & search)
        if ($request->filled('status')) {
            Log::info('Filter status diterapkan: ' . $request->status);

            if ($request->status === 'lunas') {
                $allRows = $allRows->filter(function ($r) {
                    $sisa = isset($r->JML_SISA) ? (int)$r->JML_SISA : 0;
                    return $sisa === 0;
                });
            } elseif ($request->status === 'sebagian') {
                $allRows = $allRows->filter(function ($r) {
                    $bayar = isset($r->JML_BAYAR) ? (int)$r->JML_BAYAR : (isset($r->JML_TBP) ? (int)$r->JML_TBP : 0);
                    $sisa  = isset($r->JML_SISA) ? (int)$r->JML_SISA : 0;
                    return $bayar > 0 && $sisa > 0;
                });
            } elseif ($request->status === 'belum') {
                $allRows = $allRows->filter(function ($r) {
                    $bayar = isset($r->JML_BAYAR) ? (int)$r->JML_BAYAR : (isset($r->JML_TBP) ? (int)$r->JML_TBP : 0);
                    return $bayar == 0;
                });
            }

            Log::info('Rows setelah filter status: ' . $allRows->count());
        }

      //  Log::info('Combined final rows count: ' . $allRows->count(), ['status' => $request->status ?? 'all']);

        // Jika $allRows bukan collection (jarang terjadi)
        if (!$allRows instanceof \Illuminate\Support\Collection) {
            Log::error('allRows bukan collection di combined');
            return response()->json(['status' => 'error', 'message' => 'Internal error: data tidak valid'], 500);
        }

        $fieldBayar = 'JML_BAYAR'; // default, atau bisa dinamis jika perlu

        return $this->buildResponse($allRows, $fieldBayar);
    }

    /**
     * HELPER: buildResponse
     */
    private function buildResponse($rows, $fieldBayar)
    {
        $page    = (int) request('page', 1);
        $perPage = (int) request('per_page', 10);

        $total   = $rows->count();
        $from    = ($page - 1) * $perPage + 1;
        $to      = min($page * $perPage, $total);

        $data = $rows
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return response()->json([
            'status' => 'ok',

            'summary' => [
                'total_data'  => $total,
                'total_pajak' => (int) $rows->sum('JML_PAJAK'),
                'total_bayar' => (int) $rows->sum($fieldBayar),
                'total_sisa'  => (int) $rows->sum('JML_SISA'),
            ],

            'summary_by_jenis' => $rows
                ->groupBy(fn ($r) => strtoupper(trim($r->JENIS_PAJAK ?? 'LAINNYA')))
                ->map(fn ($items, $jenis) => [
                    'jenis'        => $jenis,
                    'total_data'   => $items->count(),
                    'total_pajak'  => (int) $items->sum('JML_PAJAK'),
                    'total_bayar'  => (int) $items->sum($fieldBayar),
                    'total_sisa'   => (int) $items->sum('JML_SISA'),
                ])
                ->values(),

            'meta' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
                'from'     => $total ? $from : 0,
                'to'       => $total ? $to : 0,
            ],

            'data' => SkprdResource::collection($data),
        ]);
    }

    /**
     * HELPER: summaryByJenis
     */
    private function summaryByJenis(array $jenisList, callable $fetcher, string $fieldBayar)
    {
        $summary = collect();

        foreach ($jenisList as $jenis) {
            $rows = collect($fetcher($jenis));
            if ($rows->isEmpty()) continue;

            $summary->push([
                'jenis'        => $jenis,
                'total_data'   => $rows->count(),
                'total_pajak'  => (int) $rows->sum('JML_PAJAK'),
                'total_bayar'  => (int) $rows->sum($fieldBayar),
                'total_sisa'   => (int) $rows->sum('JML_SISA'),
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'summary_by_jenis' => $summary->values(),
        ]);
    }

    /**
     * Export Excel
     */
    public function exportWp(Request $request)
    {
        try {
            $request->validate([
                'tgl_dari'   => 'required|date',
                'tgl_sampai' => 'required|date',
                'npwpd'      => 'required_without:nama_wp|string',
                'nama_wp'    => 'required_without:npwpd|string',
                'flag'       => 'sometimes|integer',
            ]);

            $flag = $request->input('flag', 0);
            $npwpd = $request->npwpd ? strtoupper(trim($request->npwpd)) : null;
            $namaWp = $request->nama_wp ? strtoupper(trim($request->nama_wp)) : null;

            Log::info('Export WP dimulai', ['npwpd' => $npwpd, 'nama_wp' => $namaWp]);

            $allRows = collect();

            // Ambil semua jenis self
            $jenisSelf = ['HOTEL', 'RESTO', 'HIBURAN', 'PARKIR', 'MGOLC', 'PENER', 'LAINNYA'];
            foreach ($jenisSelf as $j) {
                $rows = collect(DB::connection('firebird')->select(
                    "SELECT * FROM DAFTAR_SPTPD_V2(?, ?, ?)",
                    [$request->tgl_dari, $request->tgl_sampai, $j]
                ));
                $allRows = $allRows->merge($rows);
            }

            // Ambil semua jenis office
            $jenisOffice = ['REKLA', 'AIRTN'];
            foreach ($jenisOffice as $j) {
                $rows = collect(DB::connection('firebird')->select(
                    "SELECT * FROM DAFTAR_SKPRD_V2(?, ?, ?, ?)",
                    [$request->tgl_dari, $request->tgl_sampai, $j, $flag]
                ));
                $allRows = $allRows->merge($rows);
            }

            Log::info('Rows setelah ambil semua jenis: ' . $allRows->count());

            // Filter NPWPD exact
            if ($npwpd) {
                $allRows = $allRows->filter(fn($r) => strtoupper($r->NPWPD ?? '') === $npwpd);
            }

            // Filter nama WP partial
            if ($namaWp) {
                $allRows = $allRows->filter(fn($r) => str_contains(strtoupper($r->NAMA_WP ?? ''), $namaWp));
            }

            Log::info('Rows setelah filter WP: ' . $allRows->count());

            if ($allRows->isEmpty()) {
                Log::warning('Tidak ada data untuk export WP');
                return response()->json(['status' => 'error', 'message' => 'Tidak ada tagihan untuk WP ini'], 404);
            }

            // Map data aman untuk export (hilangkan object nested)
            $exportRows = $allRows->map(function ($row) {
                return (object) [
                    'tgl_sk' => $row->tgl_sk ?? null,
                    'no_sk' => $row->no_sk ?? $row->NO_SK ?? $row->NO_SPTPD ?? null,
                    'nama_wp' => $row->nama_wp ?? $row->NAMA_WP ?? null,
                    'npwpd' => $row->npwpd ?? $row->NPWPD ?? null,
                    'jml_pajak' => $row->JML_PAJAK ?? 0,
                    'jml_bayar' => $row->JML_BAYAR ?? $row->JML_TBP ?? 0,
                    'tgl_bayar' => $row->tgl_bayar ?? null,
                    'jml_sisa' => $row->JML_SISA ?? 0,
                    'jenis_pajak' => $row->JENIS_PAJAK ?? $row->JENIS ?? 'LAINNYA',
                ];
            });

            $filename = 'Tagihan_WP_' . ($npwpd ?? str_replace(' ', '_', $namaWp ?? 'Unknown')) . '_' . now()->format('Ymd_His') . '.xlsx';

            Log::info('Mulai download Excel: ' . $filename);

            return Excel::download(
                new WpTagihanExport($exportRows),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Export WP gagal', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

        return Excel::download(
            new WpTagihanExport($exportRows),
            $filename
        );
        }
    }
}