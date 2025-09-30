<?php
 
namespace App\Exports;
 
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
 
class ComparisonExport implements FromCollection, WithHeadings
{
    protected $schemaDiff;
    protected $dataDiff;
    protected $type;
 
    public function __construct($schemaDiff, $dataDiff, $type)
    {
        $this->schemaDiff = $schemaDiff;
        $this->dataDiff   = $dataDiff;
        $this->type       = $type;
    }
 
    public function collection()
    {
        $rows = [];
 
        // Schema differences
        if ($this->type === 'schema' || $this->type === null) {
            foreach ($this->schemaDiff as $table => $queries) {
                foreach ($queries as $query) {
                    $rows[] = ['Schema', $table, $query];
                }
            }
        }
 
        // Data differences
        if ($this->type === 'data' || $this->type === null) {
            foreach ($this->dataDiff as $table => $queries) {
                foreach ($queries as $query) {
                    $rows[] = ['Data', $table, $query];
                }
            }
        }
 
        return new Collection($rows);
    }
 
    public function headings(): array
    {
        return ['Type', 'Table', 'Query / Description'];
    }
}