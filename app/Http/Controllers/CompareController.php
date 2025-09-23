<?php

namespace App\Http\Controllers;

use App\Services\DatabaseCompareService;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    private $compareService;

    public function __construct(DatabaseCompareService $service)
    {
        $this->compareService = $service;
    }

    public function schemaDiff(Request $request)
    {
        $base = $request->get('base');
        $target = $request->get('target');
        $diffs = $this->compareService->compareSchemas($base, $target);
        return response()->json($diffs);
    }

    public function dataDiff(Request $request)
    {
        $base = $request->get('base');
        $target = $request->get('target');
        $table = $request->get('table');
        $diffs = $this->compareService->compareData($base, $target, $table);
        return response()->json($diffs);
    }
}
