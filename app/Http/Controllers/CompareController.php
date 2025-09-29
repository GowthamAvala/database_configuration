<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Services\DatabaseCompareService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; 
// Import the new Export class (assuming it's in app/Exports)
use App\Exports\ComparisonExport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CompareController extends Controller
{
    private $dbCompareService;

    public function __construct(DatabaseCompareService $service)
    {
        $this->dbCompareService = $service;
    }

    public function showResult(Request $request)
    {
        $base   = $request->get('base');
        $target = $request->get('target');
        $type   = $request->get('type'); 

        try {
            [$schemaDiff, $dataDiff] = $this->getAllDiffs($base, $target, $type);

            // Pagination setup
            $perPage = 20;
            $page = request()->get('page', 1);

            $schemaPaginator = $this->paginateCollection($schemaDiff, $page, $perPage);
            $dataPaginator   = $this->paginateCollection($dataDiff, $page, $perPage);

            return view('result', [
                'base'            => $base,
                'target'          => $target,
                'type'            => $type,  
                'schemaPaginator' => $schemaPaginator,
                'dataPaginator'   => $dataPaginator
            ]);

        } catch (\Exception $e) {
            // Include logging for better debugging in a real application
            // \Log::error("Database comparison failed: " . $e->getMessage(), ['base' => $base, 'target' => $target]);
            return back()->with('error', '⚠️ Failed to fetch comparison: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Request $request)
    {
        $base   = $request->get('base');
        $target = $request->get('target');
        $type   = $request->get('type');

        try {
            [$schemaDiff, $dataDiff] = $this->getAllDiffs($base, $target, $type);
        } catch (\Exception $e) {
            return back()->with('error', '⚠️ Failed to fetch data for PDF: ' . $e->getMessage());
        }


        $pdf = Pdf::loadView('result-pdf', [
            'base'       => $base,
            'target'     => $target,
            'type'       => $type,
            'schemaDiff' => $schemaDiff,
            'dataDiff'   => $dataDiff
        ]);

        return $pdf->download("comparison_result_{$base}_vs_{$target}.pdf");
    }

    /**
     * Downloads the comparison results as an Excel file using Maatwebsite Excel 3.1+ API.
     */
    public function downloadExcel(Request $request)
    {
        $base   = $request->get('base');
        $target = $request->get('target');
        $type   = $request->get('type');

        try {
            [$schemaDiff, $dataDiff] = $this->getAllDiffs($base, $target, $type);
        } catch (\Exception $e) {
            return back()->with('error', '⚠️ Failed to fetch data for Excel: ' . $e->getMessage());
        }

        $exportData = [];

        // Collect Schema differences
        if ($type === 'schema' || $type === null) {
            foreach ($schemaDiff as $table => $queries) {
                // $queries is expected to be an array of SQL strings
                foreach ($queries as $query) {
                    // Structure: [Type, Table, Query]
                    $exportData[] = ['Schema', $table, $query];
                }
            }
        }

        // Collect Data differences
        if ($type === 'data' || $type === null) {
            foreach ($dataDiff as $table => $queries) {
                // $queries is expected to be an array of SQL strings (INSERT/UPDATE/DELETE)
                foreach ($queries as $query) {
                    // Structure: [Type, Table, Query]
                    $exportData[] = ['Data', $table, $query];
                }
            }
        }
        
        // Use the modern Maatwebsite Excel 3.1+ API
        $filename = "comparison_result_{$base}_vs_{$target}.xlsx";
        
        // Instantiate the Export class with the prepared data and use the download facade
        return Excel::download(new ComparisonExport($exportData), $filename);
    }


    /**
     * Helper: Gets all schema and data differences between the two connections.
     * @param string $base
     * @param string $target
     * @param string|null $type
     * @return array
     */
    private function getAllDiffs($base, $target, $type)
    {
        // Fetch all tables from the base connection
        $tables = DB::connection($base)->select("SHOW TABLES");
        // Normalize the table list as SHOW TABLES returns object/array structure
        $tables = array_map(fn($t) => array_values((array)$t)[0], $tables);

        $schemaDiff = [];
        $dataDiff   = [];

        foreach ($tables as $table) {
            if ($type === 'schema' || $type === null) {
                // Assumes compareSchemas returns an array of SQL statements
                $sDiff = $this->dbCompareService->compareSchemas($base, $target, [$table]);
                if (!empty($sDiff)) $schemaDiff[$table] = $sDiff;
            }
            if ($type === 'data' || $type === null) {
                // Assumes compareData returns an array of SQL statements (INSERT/UPDATE/DELETE)
                $dDiff = $this->dbCompareService->compareData($base, $target, $table);
                if (!empty($dDiff)) $dataDiff[$table] = $dDiff;
            }
        }

        return [$schemaDiff, $dataDiff];
    }

    /**
     * Helper: paginates the flat array of difference queries.
     * @param array $diffArray
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator|null
     */
    private function paginateCollection($diffArray, $page, $perPage)
    {
        if (empty($diffArray)) return null;
        
        // Flatten the associative array [table => [query1, query2]] into a simple collection of queries
        $allQueries = collect($diffArray)->flatten();
        
        return new LengthAwarePaginator(
            $allQueries->forPage($page, $perPage),
            $allQueries->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
