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

            // Base query for INFORMATION_SCHEMA with all necessary details
            $baseQuery = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
                        FROM INFORMATION_SCHEMA.COLUMNS 
                        WHERE TABLE_SCHEMA = ?";

            $targetQuery = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
                            FROM INFORMATION_SCHEMA.COLUMNS 
                            WHERE TABLE_SCHEMA = ?";

            // Filter specific tables if provided
            if (!empty($tables)) {
                $tableList = "'" . implode("','", $tables) . "'";
                $baseQuery .= " AND TABLE_NAME IN ($tableList)";
                $targetQuery .= " AND TABLE_NAME IN ($tableList)";
            }

            
            // Fetch columns from both databases
            $baseTables = DB::connection($baseConnection)->select($baseQuery, [$baseDb]);
            $targetTables = DB::connection($targetConnection)->select($targetQuery, [$targetDb]);

            // Map tables to columns with details
            $baseMap = [];
            foreach ($baseTables as $col) {
                $baseMap[$col->TABLE_NAME][$col->COLUMN_NAME] = [
                    'type' => $col->COLUMN_TYPE,
                    'nullable' => $col->IS_NULLABLE,
                    'default' => $col->COLUMN_DEFAULT,
                    'comment' => $col->COLUMN_COMMENT,
                ];
            }

            $targetMap = [];
            foreach ($targetTables as $col) {
                $targetMap[$col->TABLE_NAME][$col->COLUMN_NAME] = [
                    'type' => $col->COLUMN_TYPE,
                    'nullable' => $col->IS_NULLABLE,
                    'default' => $col->COLUMN_DEFAULT,
                    'comment' => $col->COLUMN_COMMENT,
                ];
            }

            $diffQueries = [];

            // Compare base -> target
            foreach ($baseMap as $table => $columns) {
                // Table missing in target
                if (!isset($targetMap[$table])) {
                    $cols = [];
                    foreach ($columns as $colName => $details) {
                        $cols[] = "`$colName` {$details['type']}" .
                                ($details['nullable'] === 'NO' ? ' NOT NULL' : ' NULL') .
                                ($details['default'] !== null ? " DEFAULT '{$details['default']}'" : '') .
                                (!empty($details['comment']) ? " COMMENT '{$details['comment']}'" : '');
                    }
                    $diffQueries[] = "CREATE TABLE `$table` (" . implode(', ', $cols) . ");";
                    continue;
                }

                // Compare columns
                foreach ($columns as $colName => $details) {
                    if (!isset($targetMap[$table][$colName])) {
                        // Column missing in target → ADD
                        $diffQueries[] = "ALTER TABLE `$table` ADD `$colName` {$details['type']}" .
                                        ($details['nullable'] === 'NO' ? ' NOT NULL' : ' NULL') .
                                        ($details['default'] !== null ? " DEFAULT '{$details['default']}'" : '') .
                                        (!empty($details['comment']) ? " COMMENT '{$details['comment']}'" : '') . ";";
                    } else {
                        // Column exists in both → MODIFY
                        $targetCol = $targetMap[$table][$colName];

                        // Always build full definition
                        $alterParts = [
                            $details['type'], // column type always included
                            $details['nullable'] === 'NO' ? 'NOT NULL' : 'NULL',
                            "DEFAULT " . ($details['default'] !== null ? "{$details['default']}" : "NULL"),
                            "COMMENT '" . (!empty($details['comment']) ? $details['comment'] : '') . "'"
                        ];

                        // Only add query if any difference exists
                        if (
                            $details['type'] !== $targetCol['type'] ||
                            $details['nullable'] !== $targetCol['nullable'] ||
                            ($details['default'] ?? null) != ($targetCol['default'] ?? null) ||
                            ($details['comment'] ?? '') != ($targetCol['comment'] ?? '')
                        ) {
                            $diffQueries[] = "ALTER TABLE `$table` MODIFY COLUMN `$colName` " . implode(' ', $alterParts) . ";";
                        }
                    }
                }


                // Detect columns in target but not in base → drop
                foreach ($targetMap[$table] as $colName => $details) {
                    if (!isset($columns[$colName])) {
                        $diffQueries[] = "ALTER TABLE `$table` DROP COLUMN `$colName`;";
                    }
                }
            }

            // Detect tables in target not in base → drop table
            foreach ($targetMap as $table => $columns) {
                if (!isset($baseMap[$table])) {
                    $diffQueries[] = "DROP TABLE `$table`;";
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
    public function compareData($baseConnection, $targetConnection, $table, $keyColumn = 'id', $ignoreColumns = ['created_at', 'updated_at'])
    {
        $baseData = collect(DB::connection($baseConnection)->table($table)->get())->keyBy($keyColumn);
        $targetData = collect(DB::connection($targetConnection)->table($table)->get())->keyBy($keyColumn);

        $queries = [];

        foreach ($baseData as $key => $row) {
            $rowArray = (array) $row;
            if (!isset($targetData[$key])) {
                $columns = implode('`,`', array_keys($rowArray));
                $values  = implode("','", array_map(fn($v) => addslashes($v), $rowArray));
                $queries[] = "INSERT INTO `$table` (`$columns`) VALUES ('$values');";
            } else {
                $targetRowArray = (array) $targetData[$key];
                $set = [];
                foreach ($rowArray as $col => $val) {
                    if (in_array($col,$ignoreColumns)) continue;
                    if (!array_key_exists($col,$targetRowArray) || (string)$val !== (string)$targetRowArray[$col]) {
                        $set[] = "`$col`='".addslashes($val)."'";
                    }
                }
                if (!empty($set)) {
                    $queries[] = "UPDATE `$table` SET ".implode(', ',$set)." WHERE $keyColumn = $key;";
                }
            }
        }

        
        foreach ($targetData as $key => $row) {
            if (!isset($baseData[$key])) {
                $queries[] = "DELETE FROM `$table` WHERE $keyColumn = $key;";
            }
        }

        return $queries;
    }

    
    public function getAllTables($connection)
    {
        $tables = DB::connection($connection)->select("SHOW TABLES");
        return array_map(fn($t) => array_values((array)$t)[0], $tables);
    }
}
