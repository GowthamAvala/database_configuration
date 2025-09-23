<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DatabaseCompareService
{
   public function compareSchemas($baseConnection, $targetConnection, $tables = [])
{
    // Get actual database names from config
    $baseDb = config("database.connections.$baseConnection.database");
    $targetDb = config("database.connections.$targetConnection.database");

    // Base query for INFORMATION_SCHEMA
    $baseQuery = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE 
                  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA = ?";

    $targetQuery = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = ?";

    // If specific tables are provided, filter them
    if (!empty($tables)) {
        $tableList = "'" . implode("','", $tables) . "'";
        $baseQuery .= " AND TABLE_NAME IN ($tableList)";
        $targetQuery .= " AND TABLE_NAME IN ($tableList)";
    }

    // Fetch columns from both databases
    $baseTables = DB::connection($baseConnection)->select($baseQuery, [$baseDb]);
    $targetTables = DB::connection($targetConnection)->select($targetQuery, [$targetDb]);

    // Map tables to columns for easier comparison
    $baseMap = $this->mapColumns($baseTables);
    $targetMap = $this->mapColumns($targetTables);

    $diffQueries = [];

    // Compare base -> target
    foreach ($baseMap as $table => $columns) {
        // Table missing in target
        if (!isset($targetMap[$table])) {
            $diffQueries[] = "CREATE TABLE `$table` (...)"; // you can enhance with full create statement if needed
            continue;
        }

        // Compare columns
        foreach ($columns as $col => $type) {
            if (!isset($targetMap[$table][$col])) {
                $diffQueries[] = "ALTER TABLE `$table` ADD `$col` $type;";
            } elseif ($targetMap[$table][$col] !== $type) {
                $diffQueries[] = "ALTER TABLE `$table` MODIFY `$col` $type;";
            }
        }
    }

    // Optional: Detect columns in target not in base (reverse diff)
    foreach ($targetMap as $table => $columns) {
        if (!isset($baseMap[$table])) continue;
        foreach ($columns as $col => $type) {
            if (!isset($baseMap[$table][$col])) {
                $diffQueries[] = "-- Column `$col` exists in target `$table` but not in base";
            }
        }
    }

    return $diffQueries;
}

/**
 * Helper to map columns: table => [column => type]
 */
private function mapColumns($columns)
{
    $map = [];
    foreach ($columns as $col) {
        $map[$col->TABLE_NAME][$col->COLUMN_NAME] = $col->COLUMN_TYPE;
    }
    return $map;
}


    public function compareData($baseConnection, $targetConnection, $table)
    {
        $baseData = collect(DB::connection($baseConnection)->table($table)->get())->keyBy('id');
        $targetData = collect(DB::connection($targetConnection)->table($table)->get())->keyBy('id');

        $queries = [];

        foreach ($baseData as $id => $row) {
            if (!isset($targetData[$id])) {
                $queries[] = "INSERT INTO `$table` VALUES (" . implode(',', (array) $row) . ");";
            } elseif ((array) $row != (array) $targetData[$id]) {
                $set = [];
                foreach ((array) $row as $col => $val) {
                    if ($val != $targetData[$id]->$col) {
                        $set[] = "`$col` = '" . addslashes($val) . "'";
                    }
                }
                if ($set) {
                    $queries[] = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE id = $id;";
                }
            }
        }

        foreach ($targetData as $id => $row) {
            if (!isset($baseData[$id])) {
                $queries[] = "DELETE FROM `$table` WHERE id = $id;";
            }
        }

        return $queries;
    }
}
