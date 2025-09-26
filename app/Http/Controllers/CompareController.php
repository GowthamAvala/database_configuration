<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Services\DatabaseCompareService;
use Illuminate\Http\Request;

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
            $tables = DB::connection($base)->select("SHOW TABLES");
            $tables = array_map(fn($t) => array_values((array)$t)[0], $tables);

            $schemaDiff = [];
            $dataDiff   = [];

            foreach ($tables as $table) {
                if ($type === 'schema' || $type === null) {
                    $sDiff = $this->dbCompareService->compareSchemas($base, $target, [$table]);
                    if (!empty($sDiff)) {
                        $schemaDiff[$table] = $sDiff;
                    }
                }

                if ($type === 'data' || $type === null) {
                    $dDiff = $this->dbCompareService->compareData($base, $target, $table);
                    if (!empty($dDiff)) {
                        $dataDiff[$table] = $dDiff;
                    }
                }
            }

            return view('result', [
                'base'       => $base,
                'target'     => $target,
                'type'       => $type,  
                'schemaDiff' => $schemaDiff,
                'dataDiff'   => $dataDiff
            ]);

        } catch (\Exception $e) {
            return back()->with('error', '⚠️ Failed to fetch comparison: ' . $e->getMessage());
        }
    }

}
