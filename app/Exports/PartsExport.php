<?php

namespace App\Exports;

use App\Models\PartTransfer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PartsExport implements FromCollection, WithHeadings, WithStyles, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return PartTransfer::select('part_id', 'technician_id', 'quantity', 'manager_id', 'created_at')->get();
    }

    public function headings(): array
    {
        return [
            'Part ID',
            'Technician ID',
            'Quantity',
            'Manager ID',
            'Transfer Date',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Указываем стили для шапки таблицы (первая строка)
            1 => ['font' => ['bold' => true]],
            'A1:E1' => [
                'font' => [
                    'bold' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFE0B2'],
                ],
            ],
        ];
    }

    public function map($row): array
    {
        return [
            $row->part_id,
            $row->technician_id,
            $row->quantity,
            $row->manager_id,
            $row->created_at->format('m-d-Y H:i:s'), // формат даты MM-DD-YYYY HH:MI:SS
        ];
    }
}
