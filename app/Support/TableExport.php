<?php

namespace App\Support;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Ekspor query ke CSV / XLSX (sinkron, cukup untuk volume lead website).
 */
class TableExport
{
    /**
     * @param  array<string, callable(Model): mixed>  $columns  judul kolom => pengambil nilai
     */
    public static function download(Builder $query, array $columns, string $format, string $baseName): BinaryFileResponse
    {
        $format = $format === 'csv' ? 'csv' : 'xlsx';
        $path = tempnam(sys_get_temp_dir(), 'export').'.'.$format;

        $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(array_keys($columns)));

        $query->lazyById(500)->each(function (Model $record) use ($writer, $columns): void {
            $writer->addRow(Row::fromValues(array_map(
                fn (callable $value) => self::scalar($value($record)),
                array_values($columns),
            )));
        });

        $writer->close();

        return response()
            ->download($path, $baseName.'-'.now()->format('Ymd-His').'.'.$format)
            ->deleteFileAfterSend();
    }

    private static function scalar(mixed $value): string|int|float|bool|null
    {
        return match (true) {
            $value instanceof BackedEnum => $value->value,
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i'),
            is_scalar($value), $value === null => $value,
            default => (string) $value,
        };
    }
}
