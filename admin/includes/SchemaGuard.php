<?php

if (!function_exists('guardIdentifier')) {
    function guardIdentifier($identifier)
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new InvalidArgumentException('Invalid database identifier.');
        }

        return '`' . $identifier . '`';
    }
}

if (!function_exists('ensureTableAutoIncrement')) {
    function ensureTableAutoIncrement($db, $tableName, $idColumn = 'id')
    {
        $table = guardIdentifier($tableName);
        $column = guardIdentifier($idColumn);

        $columnStmt = $db->query("SHOW COLUMNS FROM {$table} LIKE " . $db->quote($idColumn));
        $idColumnInfo = $columnStmt ? $columnStmt->fetch(PDO::FETCH_ASSOC) : null;
        if (!$idColumnInfo) {
            return;
        }

        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        $skipAutoIncrementUpdate = false;
        try {
            $zeroCount = (int) $db->query("SELECT COUNT(*) FROM {$table} WHERE {$column} IS NULL OR {$column} = 0")->fetchColumn();
            if ($zeroCount > 0) {
                $nextId = (int) $db->query("SELECT COALESCE(MAX({$column}), 0) + 1 FROM {$table} WHERE {$column} > 0")->fetchColumn();
                $db->exec("SET @next_autofix_id = " . ($nextId - 1));
                $db->exec("UPDATE {$table} SET {$column} = (@next_autofix_id := @next_autofix_id + 1) WHERE {$column} IS NULL OR {$column} = 0");
            }

            $extra = isset($idColumnInfo['Extra']) ? $idColumnInfo['Extra'] : '';
            if (stripos($extra, 'auto_increment') === false) {
                if (tableHasOtherAutoIncrementColumn($db, $table, $idColumn)) {
                    $skipAutoIncrementUpdate = true;
                } else {
                    ensureTableColumnHasKey($db, $table, $column);

                    $columnType = isset($idColumnInfo['Type']) ? $idColumnInfo['Type'] : 'INT';
                    $db->exec("ALTER TABLE {$table} MODIFY {$column} {$columnType} NOT NULL AUTO_INCREMENT");
                }
            }

            if (!$skipAutoIncrementUpdate) {
                $nextAutoIncrement = (int) $db->query("SELECT COALESCE(MAX({$column}), 0) + 1 FROM {$table}")->fetchColumn();
                $db->exec("ALTER TABLE {$table} AUTO_INCREMENT = " . max(1, $nextAutoIncrement));
            }
        } catch (Exception $exception) {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            throw $exception;
        }
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
}

if (!function_exists('ensureTableColumnHasKey')) {
    function ensureTableColumnHasKey($db, $table, $column)
    {
        $indexStmt = $db->query("SHOW INDEX FROM {$table}");
        $indexes = $indexStmt ? $indexStmt->fetchAll(PDO::FETCH_ASSOC) : array();

        $hasLeadingColumnIndex = false;
        $hasPrimaryKey = false;
        foreach ($indexes as $index) {
            $keyName = isset($index['Key_name']) ? $index['Key_name'] : '';
            $columnName = isset($index['Column_name']) ? $index['Column_name'] : '';
            $sequence = isset($index['Seq_in_index']) ? (int) $index['Seq_in_index'] : 0;

            if ($keyName === 'PRIMARY') {
                $hasPrimaryKey = true;
            }

            if ($columnName === trim($column, '`') && $sequence === 1) {
                $hasLeadingColumnIndex = true;
            }
        }

        if ($hasLeadingColumnIndex) {
            return;
        }

        if (!$hasPrimaryKey && !tableColumnHasDuplicateValues($db, $table, $column)) {
            $db->exec("ALTER TABLE {$table} ADD PRIMARY KEY ({$column})");
            return;
        }

        $indexName = guardIdentifier('idx_' . trim($column, '`') . '_auto_increment');
        $db->exec("ALTER TABLE {$table} ADD INDEX {$indexName} ({$column})");
    }
}

if (!function_exists('tableHasOtherAutoIncrementColumn')) {
    function tableHasOtherAutoIncrementColumn($db, $table, $idColumn)
    {
        $columnStmt = $db->query("SHOW COLUMNS FROM {$table}");
        $columns = $columnStmt ? $columnStmt->fetchAll(PDO::FETCH_ASSOC) : array();
        foreach ($columns as $columnInfo) {
            $field = isset($columnInfo['Field']) ? $columnInfo['Field'] : '';
            $extra = isset($columnInfo['Extra']) ? $columnInfo['Extra'] : '';

            if ($field !== $idColumn && stripos($extra, 'auto_increment') !== false) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('tableColumnHasDuplicateValues')) {
    function tableColumnHasDuplicateValues($db, $table, $column)
    {
        $sql = "SELECT COUNT(*) FROM (SELECT {$column} FROM {$table} GROUP BY {$column} HAVING COUNT(*) > 1 LIMIT 1) duplicate_ids";
        return (int) $db->query($sql)->fetchColumn() > 0;
    }
}

?>
