<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportExport implements FromQuery, WithCustomChunkSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $module,
        private readonly array $headings,
        private readonly Builder $query
    ) {}

    public function query(): Builder
    {
        return (clone $this->query)
            ->with('assignedTo')
            ->orderBy('id');
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        $values = match ($this->module) {
            'leads' => [
                $row->id,
                $row->name,
                $row->company_name,
                $row->email,
                $row->phone,
                $row->source,
                $row->status,
                $row->assignedTo?->name ?? 'Unassigned',
                $row->created_at?->toDateTimeString(),
            ],
            'customers' => [
                $row->id,
                $row->name,
                $row->company,
                $row->email,
                $row->phone,
                $row->status,
                $row->lead_id,
                $row->assignedTo?->name ?? 'Unassigned',
                $row->created_at?->toDateTimeString(),
            ],
            'follow_ups' => [
                $row->id,
                $row->lead_id,
                $row->type,
                $row->date?->toDateTimeString(),
                $row->status,
                $row->remark,
                $row->assignedTo?->name ?? 'Unassigned',
            ],
            'activities' => [
                $row->id,
                $row->lead_id,
                $row->customer_id,
                $row->type,
                $row->subject,
                $row->activity_date?->toDateTimeString(),
                $row->status,
                $row->assignedTo?->name ?? 'Unassigned',
            ],
        };

        return self::escapeFormulaValues($values);
    }

    public function rows(): array
    {
        return $this->query()
            ->get()
            ->mapWithKeys(fn (Model $row) => [
                $row->getKey() => array_combine($this->headings, $this->map($row)),
            ])
            ->values()
            ->all();
    }

    public static function escapeFormulaValues(array $values): array
    {
        return array_map(
            fn (mixed $value) => is_string($value) && preg_match('/^\s*[=+\-@]/u', $value)
                ? "'".$value
                : $value,
            $values
        );
    }
}
