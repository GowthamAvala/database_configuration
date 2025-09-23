<?php

namespace App\Http\Controllers;

use App\Services\DatabaseCompareService;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    private $dbCompareService;

    public function __construct(DatabaseCompareService $service)
    {
        $this->dbCompareService = $service;
    }

    public function schemaDiff(Request $request)
    {
        dd('hI');
        $base = $request->get('base');
        $target = $request->get('target');
        $tables = $request->get('tables');  // comma-separated list from frontend

        if ($tables) {
            $tables = explode(',', $tables); // convert to array
        } else {
            $tables = []; // empty = compare all tables
        }

        try {
            $diffs = $this->dbCompareService->compareSchemas($base, $target, $tables);
            return response()->json([
                'success' => true,
                'diff' => $diffs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function dataDiff(Request $request)
    {
        $base = $request->get('base');
        $target = $request->get('target');
        $table = $request->get('table');
        try {
        $diffs = $this->dbCompareService->compareData($base, $target, $table);
        return response()->json($diffs);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
    }
}
