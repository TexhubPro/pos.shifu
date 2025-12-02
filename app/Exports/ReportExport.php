<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromArray, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected array $headings,
        protected array $rows,
        protected array $metaRows = [],
    ) {}

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $data = [];

        foreach ($this->metaRows as $metaRow) {
            $data[] = $metaRow;
        }

        if (! empty($this->metaRows)) {
            $data[] = [];
        }

        $data[] = $this->headings;

        foreach ($this->rows as $row) {
            $data[] = $row;
        }

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        $metaRowsCount = count($this->metaRows);
        $headingRowIndex = $metaRowsCount + (empty($this->metaRows) ? 1 : 2);

        return [
            $headingRowIndex => ['font' => ['bold' => true]],
        ];
    }
}
