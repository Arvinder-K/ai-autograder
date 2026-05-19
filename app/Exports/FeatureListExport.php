<?php

namespace App\Exports;

use App\Models\FeatureList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FeatureListExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(private FeatureList $featureList) {}

    public function collection()
    {
        return $this->featureList->items->map(function ($item) {
            $row = [
                'Sr. No' => $item->sr_no,
                'Feature Cluster' => $item->feature_cluster,
                'Feature' => $item->feature,
                'Detailed Workflow' => $item->detailed_workflow,
                'Feature Description' => $item->feature_description,
                'Table Name' => $item->table_name,
                'Table Column Names' => $item->table_column_names,
            ];

            if ($this->featureList->format_type === 'procode') {
                $row['Technology Stack'] = $item->technology_stack;
                $row['Actor User'] = $item->actor_user;
            } elseif ($this->featureList->format_type === 'agentic') {
                $row['Agent Type'] = $item->agent_type;
                $row['Actor User'] = $item->actor_user;
            }

            return $row;
        });
    }

    public function headings(): array
    {
        $headings = ['Sr. No', 'Feature Cluster', 'Feature', 'Detailed Workflow', 'Feature Description', 'Table Name', 'Table Column Names'];

        if ($this->featureList->format_type === 'procode') {
            $headings[] = 'Technology Stack';
            $headings[] = 'Actor User';
        } elseif ($this->featureList->format_type === 'agentic') {
            $headings[] = 'Agent Type';
            $headings[] = 'Actor User';
        }

        return $headings;
    }

    public function title(): string
    {
        return ucfirst($this->featureList->format_type) . ' Features';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '193E6B'],
                ],
            ],
        ];
    }
}
