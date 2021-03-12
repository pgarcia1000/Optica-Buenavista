<?php

namespace App\Abstracts;

use App\Traits\Import as ImportHelper;
use App\Utilities\Date;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

abstract class Import implements ToModel, SkipsOnError, SkipsOnFailure, WithChunkReading, WithHeadingRow, WithMapping, WithValidation
{
    use Importable, ImportHelper;

    public $empty_field = 'empty---';

    public function map($row): array
    {
        $row['company_id'] = session('company_id');

        // Make enabled field integer
        if (isset($row['enabled'])) {
            $row['enabled'] = (int) $row['enabled'];
        }

        // Make reconciled field integer
        if (isset($row['reconciled'])) {
            $row['reconciled'] = (int) $row['reconciled'];
        }

        $date_fields = ['paid_at', 'invoiced_at', 'billed_at', 'due_at', 'issued_at', 'created_at', 'transferred_at'];
        foreach ($date_fields as $date_field) {
            if (!isset($row[$date_field])) {
                continue;
            }

            try {
                $row[$date_field] = Date::parse(ExcelDate::excelToDateTimeObject($row[$date_field]))
                                        ->format('Y-m-d H:i:s');
            } catch (InvalidFormatException | \Exception $e) {
                Log::info($e->getMessage());
            }
        }

        return $row;
    }

    public function rules(): array
    {
        return [];
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function onFailure(Failure ...$failures)
    {
        $sheet = Str::snake((new \ReflectionClass($this))->getShortName());

        foreach ($failures as $failure) {
            // @todo remove after 3.2 release https://github.com/Maatwebsite/Laravel-Excel/issues/1834#issuecomment-474340743
            if (collect($failure->values())->first() == $this->empty_field) {
                continue;
            }

            $message = trans('messages.error.import_column', [
                'message' => collect($failure->errors())->first(),
                'sheet' => $sheet,
                'line' => $failure->row(),
            ]);

            flash($message)->error()->important();
       }
    }

    public function onError(\Throwable $e)
    {
        flash($e->getMessage())->error()->important();
    }

    public function isNotValid($row)
    {
        return Validator::make($row, $this->rules())->fails();
    }

    public function isEmpty($row, $fields)
    {
        $fields = Arr::wrap($fields);

        foreach ($fields as $field) {
            if (!empty($row[$field])) {
                continue;
            }

            return true;
        }

        return false;
    }
}
