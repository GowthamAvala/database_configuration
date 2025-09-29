<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Exports the schema and data differences as a structured array to Excel.
 * This class handles passing the data and defining the header row.
 */
class ComparisonExport implements FromArray, WithHeadings
{
    /**
     * @var array
     */
    protected $exportData;

    /**
     * The constructor receives the collected difference data from the controller.
     *
     * @param array $exportData
     */
    public function __construct(array $exportData)
    {
        // The data array passed from the CompareController
        $this->exportData = $exportData;
    }

    /**
     * Returns the data array for the sheet content (the body of the Excel file).
     * This is required by the FromArray concern.
     *
     * @return array
     */
    public function array(): array
    {
        return $this->exportData;
    }

    /**
     * Defines the column headings for the Excel file.
     * This is required by the WithHeadings concern.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Type',
            'Table',
            'Query / Description'
        ];
    }
}
