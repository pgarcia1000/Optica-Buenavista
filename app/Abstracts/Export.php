<?php

namespace App\Abstracts;

use App\Events\Export\HeadingsPreparing;
use App\Events\Export\RowsPreparing;
use App\Utilities\Date;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

abstract class Export implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    public $ids;

    public $fields;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
        $this->fields = $this->fields();
    }

    public function title(): string
    {
        return Str::snake((new \ReflectionClass($this))->getShortName());
    }

    public function fields(): array
    {
        return [];
    }

    public function map($model): array
    {
        $map = [];

        $date_fields = ['paid_at', 'invoiced_at', 'billed_at', 'due_at', 'issued_at', 'created_at', 'transferred_at'];

        $evil_chars = ['=', '+', '-', '@'];

        foreach ($this->fields as $field) {
            $value = $model->$field;

            if (in_array($field, $date_fields)) {
                $value = ExcelDate::PHPToExcel(Date::parse($value)->format('Y-m-d'));
            }

            // Prevent CSV injection https://security.stackexchange.com/a/190848
            if (Str::startsWith($value, $evil_chars)) {
                $value = "'" . $value;
            }

            $map[] = $value;
        }

        return $map;
    }

    public function headings(): array
    {
        event(new HeadingsPreparing($this));

        return $this->fields;
    }

    public function prepareRows($rows)
    {
        event(new RowsPreparing($this, $rows));

        return $rows;
    }
}
