<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DatabaseCompareService
{
    public function compareSchemas($baseConnection, $targetConnection)
    {
        $baseTables = DB::connection($baseConnection)
            ->select("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = DATABASE()");

        $targetTables = DB::connection($targetConnection)
            ->select("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = DATABASE()");

        $baseMap = $this->mapColumns($baseTables);
        $targetMap = $this->mapColumns($targetTables);

        $diffQueries = [];

        foreach ($baseMap as $table => $columns) {
            if (!isset($targetMap[$table])) {
                $diffQueries[] = "CREATE TABLE `$table` (...)";
                continue;
            }

            foreach ($columns as $col => $type) {
                if (!isset($targetMap[$table][$col])) {
                    $diffQueries[] = "ALTER TABLE `$table` ADD `$col` $type;";
                } elseif ($targetMap[$table][$col] !== $type) {
                    $diffQueries[] = "ALTER TABLE `$table` MODIFY `$col` $type;";
                }
            }
        }

        return $diffQueries;
    }

    private function mapColumns($rows)
    {
        $map = [];
        foreach ($rows as $row) {
            $map[$row->TABLE_NAME][$row->COLUMN_NAME] = $row->COLUMN_TYPE;
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
