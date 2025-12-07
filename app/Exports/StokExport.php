<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StokExport implements FromArray, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function styles(Worksheet $sheet)
    {
        // Auto width columns
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        // Merge title and date rows for cleaner look
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');

        // Freeze header so scroll keeps titles visible
        $sheet->freezePane('A5');

        // Style header title (row 1)
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Style date row (row 2)
        $sheet->getStyle('A2:D2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']],
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // Style table header (row 4)
        $sheet->getStyle('A4:D4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B9BD5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Hitung baris
        $rowCount = count($this->data);
        $lastRow = $rowCount; // baris total
        $headerRow = 4;
        $firstDataRow = 5;
        $lastDataRow = $lastRow - 1; // sebelum baris total

        // Border untuk tabel + total
        $sheet->getStyle('A' . $headerRow . ':D' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D9D9D9'],
                ],
            ],
        ]);

        // Style total row
        $sheet->getStyle('A' . $lastRow . ':D' . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
        ]);

        // Align kolom angka + zebra striping untuk keterbacaan
        if ($lastDataRow >= $firstDataRow) {
            $sheet->getStyle('A' . $firstDataRow . ':A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $firstDataRow . ':D' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Number format ribuan
            $sheet->getStyle('D' . $firstDataRow . ':D' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0');

            // Zebra striping - alternate row colors (mulai dari putih)
            for ($r = $firstDataRow; $r <= $lastDataRow; $r++) {
                if (($r - $firstDataRow) % 2 == 1) {
                    // Baris ganjil (2nd, 4th, 6th data row) - light gray
                    $sheet->getStyle('A' . $r . ':D' . $r)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                    ]);
                }
                // Baris genap tetap putih (default, tidak perlu di-set)
            }
        }
        // Total row align
        $sheet->getStyle('D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A' . $lastRow . ':D' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}
