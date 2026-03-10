<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class SkprdResource extends JsonResource
{
    public function toArray($request)
    {
        $waktuA = $this->WAKTU_A ?? null;
        $waktuB = $this->WAKTU_B ?? null;

        $masa = null;

        if ($waktuA && $waktuB) {
            $from = substr($waktuA, 0, 10);
            $to   = substr($waktuB, 0, 10);

            $masa = [
                'dari'   => $from,
                'sampai' => $to,
                'hari'   => Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1,
            ];
        }

        return [
            'tgl_sk' => substr($this->TGL_SK, 0, 10),
            'no_sk'  => $this->NO_SK,
            'nama_wp' => $this->NAMA_WP,
            'alamat_wp' => $this->ALAMAT_WP,
            'npwpd' => $this->NPWPD,

            // 'jumlah' => (float) $this->JUMLAH,
            'jml_pajak' => (float) $this->JML_PAJAK,
            'jml_bunga' => (float) $this->JML_BUNGA,
            'jml_kenaikan' => (float) $this->JML_KENAIKAN,
            'jml_bayar'=>(float) $this->JML_TBP,
            'jml_sisa' => (float) $this->JML_SISA,

            'tgl_jatuh_tempo'   => substr($this->TGL_JATUH_TEMPO, 0, 10),
            'tgl_bayar'         =>substr($this->TGL_TBP, 0, 10),

            // 🔥 MASA PEMASANGAN (OFFICIAL ONLY)
            'masa' => $masa,
        ];
    }
}
