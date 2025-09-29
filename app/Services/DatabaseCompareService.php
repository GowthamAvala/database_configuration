<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseCompareService
{
    public function compareSchemas($baseConnection, $targetConnection, $tables = [])
    {
        $baseDb   = config("database.connections.$baseConnection.database");
        $targetDb = config("database.connections.$targetConnection.database");

        // Columns info
        $columnsQuery = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT
<<<<<<< Updated upstream
                        FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ?";
        // Foreign keys
        $fkQuery = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL";
=======
                    FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ?";
        // Foreign keys
        $fkQuery = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL";
>>>>>>> Stashed changes

        if (!empty($tables)) {
            $tableList = "'" . implode("','", $tables) . "'";
            $columnsQuery .= " AND TABLE_NAME IN ($tableList)";
            $fkQuery .= " AND TABLE_NAME IN ($tableList)";
        }

<<<<<<< Updated upstream
        $baseColumns   = DB::connection($baseConnection)->select($columnsQuery, [$baseDb]);
        $targetColumns = DB::connection($targetConnection)->select($columnsQuery, [$targetDb]);

        $baseFKs   = DB::connection($baseConnection)->select($fkQuery, [$baseDb]);
        $targetFKs = DB::connection($targetConnection)->select($fkQuery, [$targetDb]);

        // Map columns
        $baseMap = []; $targetMap = [];
        foreach ($baseColumns as $col) {
            $default = strtoupper((string) $col->COLUMN_DEFAULT) === 'NULL' ? null : $col->COLUMN_DEFAULT;
            $baseMap[$col->TABLE_NAME][$col->COLUMN_NAME] = [
                'type'     => $col->COLUMN_TYPE,
                'nullable' => $col->IS_NULLABLE,
                'default'  => $default,
                'key'      => $col->COLUMN_KEY,
                'extra'    => $col->EXTRA,
                'comment'  => $col->COLUMN_COMMENT
            ];
        }
        foreach ($targetColumns as $col) {
            $default = strtoupper((string) $col->COLUMN_DEFAULT) === 'NULL' ? null : $col->COLUMN_DEFAULT;
            $targetMap[$col->TABLE_NAME][$col->COLUMN_NAME] = [
                'type'     => $col->COLUMN_TYPE,
                'nullable' => $col->IS_NULLABLE,
                'default'  => $default,
                'key'      => $col->COLUMN_KEY,
                'extra'    => $col->EXTRA,
                'comment'  => $col->COLUMN_COMMENT
            ];
        }

        // Map foreign keys
        $baseFKMap = [];
        foreach ($baseFKs as $fk) {
            $baseFKMap[$fk->TABLE_NAME][] = [
                'column'            => $fk->COLUMN_NAME,
                'referenced_table'  => $fk->REFERENCED_TABLE_NAME,
                'referenced_column' => $fk->REFERENCED_COLUMN_NAME,
                'constraint_name'   => $fk->CONSTRAINT_NAME,
            ];
        }
=======
        try {
            $baseColumns   = DB::connection($baseConnection)->select($columnsQuery, [$baseDb]);
            $targetColumns = DB::connection($targetConnection)->select($columnsQuery, [$targetDb]);

            $baseFKs   = DB::connection($baseConnection)->select($fkQuery, [$baseDb]);
            $targetFKs = DB::connection($targetConnection)->select($fkQuery, [$targetDb]);
        } catch (\Exception $e) {
            Log::error(" Schema query failed", [
                'base'   => $baseConnection,
                'query' => $columnsQuery,
                'error' => $e->getMessage()
            ]);
            return ["-- ERROR: Schema query failed: Please check DB connections." . $e->getMessage()];
        }
        try {
            // Map columns
            $baseMap = [];
            $targetMap = [];
            foreach ($baseColumns as $col) {
                $default = strtoupper((string) $col->COLUMN_DEFAULT) === 'NULL' ? null : $col->COLUMN_DEFAULT;
                $baseMap[$col->TABLE_NAME][$col->COLUMN_NAME] = [
                    'type'     => $col->COLUMN_TYPE,
                    'nullable' => $col->IS_NULLABLE,
                    'default'  => $default,
                    'key'      => $col->COLUMN_KEY,
                    'extra'    => $col->EXTRA,
                    'comment'  => $col->COLUMN_COMMENT
                ];
            }
            foreach ($targetColumns as $col) {
                $default = strtoupper((string) $col->COLUMN_DEFAULT) === 'NULL' ? null : $col->COLUMN_DEFAULT;
                $targetMap[$col->TABLE_NAME][$col->COLUMN_NAME] = [
                    'type'     => $col->COLUMN_TYPE,
                    'nullable' => $col->IS_NULLABLE,
                    'default'  => $default,
                    'key'      => $col->COLUMN_KEY,
                    'extra'    => $col->EXTRA,
                    'comment'  => $col->COLUMN_COMMENT
                ];
            }

            // Map foreign keys
            $baseFKMap = [];
            foreach ($baseFKs as $fk) {
                $baseFKMap[$fk->TABLE_NAME][] = [
                    'column'            => $fk->COLUMN_NAME,
                    'referenced_table'  => $fk->REFERENCED_TABLE_NAME,
                    'referenced_column' => $fk->REFERENCED_COLUMN_NAME,
                    'constraint_name'   => $fk->CONSTRAINT_NAME,
                ];
            }
>>>>>>> Stashed changes

        $diffQueries = [];

<<<<<<< Updated upstream
        foreach ($baseMap as $table => $columns) {
            // TABLE MISSING → CREATE TABLE
            if (!isset($targetMap[$table])) {
                $cols = []; $primaryKeys = [];
                foreach ($columns as $colName => $details) {
                    $isPrimary = $details['key'] === 'PRI';
                    if ($isPrimary) $primaryKeys[] = "`$colName`";

                    // Determine default safely
=======
            foreach ($baseMap as $table => $columns) {
                // TABLE MISSING → CREATE TABLE
                if (!isset($targetMap[$table])) {
                    $cols = [];
                    $primaryKeys = [];
                    foreach ($columns as $colName => $details) {
                        $isPrimary = $details['key'] === 'PRI';
                        if ($isPrimary) $primaryKeys[] = "`$colName`";

                        // Default values
                        $defaultSQL = '';
                        if (!$isPrimary) {
                            if ($details['default'] !== null) {
                                $defaultSQL = "DEFAULT " . $details['default'];
                            } elseif ($details['nullable'] === 'YES') {
                                $defaultSQL = "DEFAULT NULL";
                            }
                        }

                        $cols[] = "`$colName` {$details['type']}" .
                            ($details['nullable'] === 'NO' ? ' NOT NULL' : ' NULL') .
                            ($defaultSQL ? " $defaultSQL" : '') .
                            (!empty($details['extra']) ? " {$details['extra']}" : '') .
                            (!empty($details['comment']) ? " COMMENT '" . addslashes($details['comment']) . "'" : '');
                    }

                    if (!empty($primaryKeys)) {
                        $cols[] = "PRIMARY KEY (" . implode(',', $primaryKeys) . ")";
                    }

                    if (!empty($baseFKMap[$table])) {
                        foreach ($baseFKMap[$table] as $fk) {
                            $cols[] = "CONSTRAINT `{$fk['constraint_name']}` FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['referenced_table']}`(`{$fk['referenced_column']}`)";
                        }
                    }

                    $diffQueries[] = "CREATE TABLE `$table` (" . implode(', ', $cols) . ");";
                    continue;
                }

                // Compare columns → ADD/MODIFY
                foreach ($columns as $colName => $details) {
                    $isPrimary  = $details['key'] === 'PRI';
>>>>>>> Stashed changes
                    $defaultSQL = '';
                    if (!$isPrimary) {
                        if ($details['default'] !== null) {
                            $defaultSQL = "DEFAULT " . $details['default'];
                        } elseif ($details['nullable'] === 'YES') {
                            $defaultSQL = "DEFAULT NULL";
<<<<<<< Updated upstream
=======
                        }
                    }

                    if (!isset($targetMap[$table][$colName])) {
                        // ADD COLUMN
                        $diffQueries[] = "ALTER TABLE `$table` ADD `$colName` {$details['type']}" .
                            ($details['nullable'] === 'NO' ? ' NOT NULL' : ' NULL') .
                            ($defaultSQL ? " $defaultSQL" : '') .
                            (!empty($details['comment']) ? " COMMENT '" . addslashes($details['comment']) . "'" : '') . ";";
                    } else {
                        $targetCol = $targetMap[$table][$colName];
                        if (
                            $details['type'] !== $targetCol['type'] ||
                            $details['nullable'] !== $targetCol['nullable'] ||
                            ($details['default'] ?? null) != ($targetCol['default'] ?? null) ||
                            ($details['comment'] ?? '') != ($targetCol['comment'] ?? '')
                        ) {
                            $alterParts = [
                                $details['type'],
                                $details['nullable'] === 'NO' ? 'NOT NULL' : 'NULL',
                                $defaultSQL,
                                !empty($details['comment']) ? "COMMENT '" . addslashes($details['comment']) . "'" : ''
                            ];
                            $diffQueries[] = "ALTER TABLE `$table` MODIFY COLUMN `$colName` " . implode(' ', array_filter($alterParts)) . ";";
>>>>>>> Stashed changes
                        }
                    }

                    $cols[] = "`$colName` {$details['type']}" .
                              ($details['nullable'] === 'NO' ? ' NOT NULL' : ' NULL') .
                              ($defaultSQL ? " $defaultSQL" : '') .
                              (!empty($details['extra']) ? " {$details['extra']}" : '') .
                              (!empty($details['comment']) ? " COMMENT '" . addslashes($details['comment']) . "'" : '');
                }

<<<<<<< Updated upstream
                if (!empty($primaryKeys)) {
                    $cols[] = "PRIMARY KEY (" . implode(',', $primaryKeys) . ")";
                }

                if (!empty($baseFKMap[$table])) {
                    foreach ($baseFKMap[$table] as $fk) {
                        $cols[] = "CONSTRAINT `{$fk['constraint_name']}` FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['referenced_table']}`(`{$fk['referenced_column']}`)";
                    }
                }

                $diffQueries[] = "CREATE TABLE `$table` (" . implode(', ', $cols) . ");";
                continue;
            }

            // Compare columns → ADD/MODIFY
            foreach ($columns as $colName => $details) {
                $isPrimary  = $details['key'] === 'PRI';
                $defaultSQL = '';
                if (!$isPrimary) {
                    if ($details['default'] !== null) {
                        $defaultSQL = "DEFAULT " . $details['default'];
                    } elseif ($details['nullable'] === 'YES') {
                        $defaultSQL = "DEFAULT NULL";
                    }
                }

                if (!isset($targetMap[$table][$colName])) {
                    // ADD COLUMN
                    $diffQueries[] = "ALTER TABLE `$table` ADD `$colName` {$details['type']}" .
                                     ($details['nullable'] === 'NO' ? ' NOT NULL' : ' NULL') .
                                     ($defaultSQL ? " $defaultSQL" : '') .
                                     (!empty($details['comment']) ? " COMMENT '" . addslashes($details['comment']) . "'" : '') . ";";
                } else {
                    $targetCol = $targetMap[$table][$colName];
                    if (
                        $details['type'] !== $targetCol['type'] ||
                        $details['nullable'] !== $targetCol['nullable'] ||
                        ($details['default'] ?? null) != ($targetCol['default'] ?? null) ||
                        ($details['comment'] ?? '') != ($targetCol['comment'] ?? '')
                    ) {
                        $alterParts = [
                            $details['type'],
                            $details['nullable'] === 'NO' ? 'NOT NULL' : 'NULL',
                            $defaultSQL,
                            !empty($details['comment']) ? "COMMENT '" . addslashes($details['comment']) . "'" : ''
                        ];
                        $diffQueries[] = "ALTER TABLE `$table` MODIFY COLUMN `$colName` " . implode(' ', array_filter($alterParts)) . ";";
=======
                // DROP extra columns in target
                if (isset($targetMap[$table])) {
                    foreach ($targetMap[$table] as $colName => $details) {
                        if (!isset($baseMap[$table][$colName])) {
                            $diffQueries[] = "ALTER TABLE `$table` DROP COLUMN `$colName`;";
                        }
>>>>>>> Stashed changes
                    }
                }
            }

<<<<<<< Updated upstream
            // DROP extra columns in target
            if (isset($baseMap[$table]) && isset($targetMap[$table])) {
            foreach ($targetMap[$table] as $colName => $details) {
                if (!isset($baseMap[$table][$colName])) {
                    $diffQueries[] = "ALTER TABLE `$table` DROP COLUMN `$colName`;";
=======
            // DROP extra tables in target
            foreach ($targetMap as $table => $columns) {
                if (!isset($baseMap[$table])) {
                    Log::warning(" Dropping extra table", ['table' => $table]);

                    //  Debugging here instead of before
                    Log::info("Comparing tables for DROP", [
                        'baseMapTables'   => array_keys($baseMap),
                        'targetMapTables' => array_keys($targetMap),
                        'dropping'        => $table,
                    ]);

                    $diffQueries[] = "DROP TABLE `$table`;";
>>>>>>> Stashed changes
                }
            }
        }

<<<<<<< Updated upstream
        }

        // DROP extra tables in target
        foreach ($targetMap as $table => $columns) {
            if (!isset($baseMap[$table])) {
                $diffQueries[] = "DROP TABLE `$table`;";
            }
        }

        return $diffQueries;
    }


    public function compareData($baseConnection, $targetConnection, $table, $keyColumn = 'id', $ignoreColumns = ['created_at','updated_at'])
    {
        $queries = [];

        // Process data in chunks to avoid memory overload
        DB::connection($baseConnection)->table($table)->orderBy($keyColumn)->chunk(5000, function ($baseChunk) use ($targetConnection, $table, $keyColumn, $ignoreColumns, &$queries) {
            $baseData = collect($baseChunk)->keyBy($keyColumn);

            // Fetch corresponding rows from target
            $targetData = collect(
                DB::connection($targetConnection)
                    ->table($table)
                    ->whereIn($keyColumn, $baseData->keys())
                    ->get()
            )->keyBy($keyColumn);

            foreach ($baseData as $key => $row) {
                $rowArray = (array) $row;
=======
            // Log the queries for debugging
            Log::info("Schema differences generated", ['queries' => $diffQueries]);

            return $diffQueries;
        } catch (\Exception $e) {
            Log::error(" Schema comparison failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', $e->getMessage());
            return back(); 
        }
    }


    public function compareData($baseConnection, $targetConnection, $table, $keyColumn = 'id', $ignoreColumns = ['created_at', 'updated_at'])
    {
        $queries = [];

        // 1. Get table lists
        $baseTables = array_map(fn($t) => array_values((array)$t)[0], 
        DB::connection($baseConnection)->select("SHOW TABLES"));

        $targetTables = array_map(fn($t) => array_values((array)$t)[0], 
        DB::connection($targetConnection)->select("SHOW TABLES"));

        // 2. Check existence cases before doing comparison
        if (!in_array($table, $baseTables) && in_array($table, $targetTables)) {
            return [" Table `$table` exists in Target database but is missing in Base database."];
        }

        if (!in_array($table, $targetTables) && in_array($table, $baseTables)) {
            return [" Table `$table` exists in Base database but is missing in Target database."];
        }

        if (!in_array($table, $baseTables) && !in_array($table, $targetTables)) {
            return [" Table `$table` is not present in either Base or Target database."];
        }

        try {
            $baseData = collect(DB::connection($baseConnection)->table($table)->get())->keyBy($keyColumn);
            $targetData = collect(DB::connection($targetConnection)->table($table)->get())->keyBy($keyColumn);

            foreach ($baseData as $key => $row) {
                $rowArray = (array)$row;
>>>>>>> Stashed changes
                if (!isset($targetData[$key])) {
                    $columns = implode('`,`', array_keys($rowArray));
                    $values  = implode("','", array_map(fn($v) => addslashes($v), $rowArray));
                    $queries[] = "INSERT INTO `$table` (`$columns`) VALUES ('$values');";
                } else {
<<<<<<< Updated upstream
                    $targetRowArray = (array) $targetData[$key];
=======
                    $targetRowArray = (array)$targetData[$key];
>>>>>>> Stashed changes
                    $set = [];
                    foreach ($rowArray as $col => $val) {
                        if (in_array($col, $ignoreColumns)) continue;
                        if (!array_key_exists($col, $targetRowArray) || (string)$val !== (string)$targetRowArray[$col]) {
                            $set[] = "`$col`='" . addslashes($val) . "'";
                        }
                    }
                    if (!empty($set)) {
                        $queries[] = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE $keyColumn = $key;";
                    }
                }
            }
<<<<<<< Updated upstream
        });

        // Handle extra rows in target (not in base)
        DB::connection($targetConnection)->table($table)->orderBy($keyColumn)->chunk(5000, function ($targetChunk) use ($baseConnection, $table, $keyColumn, &$queries) {
            $targetData = collect($targetChunk)->keyBy($keyColumn);

            $baseData = collect(
                DB::connection($baseConnection)
                    ->table($table)
                    ->whereIn($keyColumn, $targetData->keys())
                    ->get()
            )->keyBy($keyColumn);
=======
>>>>>>> Stashed changes

            foreach ($targetData as $key => $row) {
                if (!isset($baseData[$key])) {
                    $queries[] = "DELETE FROM `$table` WHERE $keyColumn = $key;";
                }
            }
<<<<<<< Updated upstream
        });
=======
        } catch (\Exception $e) {
            Log::warning("Data comparison failed for table $table: " . $e->getMessage());
            session()->flash('error', $e->getMessage());
            return back(); 
        }
>>>>>>> Stashed changes

        return $queries;
    }

    public function getAllTables($connection)
    {
<<<<<<< Updated upstream
        $tables = DB::connection($connection)->select("SHOW TABLES");
        return array_map(fn($t) => array_values((array) $t)[0], $tables);
=======
        try {
            $tables = DB::connection($connection)->select("SHOW TABLES");
            return array_map(fn($t) => array_values((array)$t)[0], $tables);
        } catch (\Exception $e) {
            Log::error("Failed to fetch tables from $connection: " . $e->getMessage());
            session()->flash('error', $e->getMessage());
            return back(); 
        }
>>>>>>> Stashed changes
    }
}
