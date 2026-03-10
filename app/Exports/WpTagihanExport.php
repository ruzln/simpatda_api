<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WpTagihanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tgl SK',
            'No SK',
            'Jenis Pajak',
            'Nama Wajib Pajak',
            'NPWPD',
            'Ketetapan (Rp)',
            'Bayar (Rp)',
            'Tgl Bayar',
            'Sisa (Rp)',
            'Status',
        ];
    }

    public function map($row): array
    {
        $bayar = $row->jml_bayar ?? $row->JML_BAYAR ?? $row->JML_TBP ?? 0;
        $sisa  = $row->jml_sisa  ?? $row->JML_SISA ?? 0;
        $pajak = $row->jml_pajak ?? $row->JML_PAJAK ?? 0;

        $status = 'Belum Bayar';
        if ($bayar > 0 && $bayar < $pajak) {
            $status = 'Sebagian';
        } elseif ($sisa == 0) {
            $status = 'Lunas';
        }

        return [
            '-', // No (akan diisi otomatis di Excel)
            $row->tgl_sk ?? '-',
            $row->no_sk ?? $row->NO_SK ?? $row->NO_SPTPD ?? '-',
            $row->jenis_pajak ?? $row->JENIS_PAJAK ?? $row->JENIS ?? 'LAINNYA',
            $row->nama_wp ?? $row->NAMA_WP ?? '-',
            $row->npwpd ?? $row->NPWPD ?? '-',
            number_format($pajak, 0, ',', '.'),
            number_format($bayar, 0, ',', '.'),
            $row->tgl_bayar ?? '-',
            number_format($sisa, 0, ',', '.'),
            $status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4CAF50']],
                'font' => ['color' => ['argb' => 'FFFFFFFF']],
                'alignment' => ['horizontal' => 'center'],
            ],
            // Nominal rata kanan
            'G:K' => ['alignment' => ['horizontal' => 'right']],
        ];
    }
}