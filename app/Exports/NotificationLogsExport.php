<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\NotificationLog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NotificationLogsExport implements FromView, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected Collection $logs
    ) {
    }

    public function view(): View
    {
        return view('exports.notification-logs-excel', [
            'logs' => $this->logs,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }
}
