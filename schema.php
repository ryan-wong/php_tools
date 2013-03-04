<?php

class DatabaseSchema {

    protected $_databaseName = '';
    protected $_tableNames = array();
    protected $_tables = array();
    protected $_con = null;
    protected $_schema = '';

    public function __construct($settings) {
        $this->_databaseName = $settings[3];
        $this->_con = new mysqli($settings[0], $settings[1], $settings[2], $settings[3]);
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
    }

    public function getTableNames() {
        if (!$this->_tableNames) {
            $databaseName = $this->_databaseName;
            $query = "SELECT table_name FROM information_schema.tables  WHERE table_schema = '$databaseName' ";
            $res = $this->_con->query($query);
            $res->data_seek(0);
            while ($row = $res->fetch_assoc()) {
                $this->_tableNames[] = $row['table_name'];
            }
        }
        return $this->_tableNames;
    }

    public function getTables() {
        if (!$this->_tables) {
            $databaseName = $this->_databaseName;
            foreach ($this->getTableNames() as $table) {
                $query = "select * from information_schema.columns where table_name='$table' and TABLE_SCHEMA = '$databaseName' ";
                $res = $this->_con->query($query);
                $res->data_seek(0);
                while ($row = $res->fetch_assoc()) {
                    $tableArray = array();
                    $fields = array_keys($row);
                    foreach ($fields as $field) {
                        $tableArray[$field] = $row[$field];
                    }
                    $this->_tables[$table][$row['COLUMN_NAME']] = $tableArray;
                }
            }
        }
        return $this->_tables;
    }

    public function my_array_diff_assoc($a, $a2) {
        $diff = array();
        foreach ($a as $table) {
            if (!in_array($table, $a2)) {
                $diff[] = $table;
            }
        }
        foreach ($a2 as $table) {
            if (!in_array($table, $a)) {
                $diff[] = $table;
            }
        }
        return $diff;
    }

    public function compareDatabase(DatabaseSchema $schema2) {
        $tableDifferences = $this->my_array_diff_assoc($this->getTableNames(), $schema2->getTableNames());
        $schema1Str = '';
        $schema2Str = '';
        foreach ($tableDifferences as $diff) {
            if (in_array($diff, $this->getTableNames())) {
                $schema1Str .= DatabaseSchema::createTable($this->getTables()[$diff], $diff);
            } else {
                $schema2Str.= DatabaseSchema::createTable($schema2->getTables()[$diff], $diff);
            }
        }
        $schema1Table = $this->getTables();
        $schema2Table = $schema2->getTables();
        $ignoreField = array('TABLE_SCHEMA', 'PRIVILEGES', 'ORDINAL_POSITION', 'TABLE_CATALOG');
        foreach ($schema1Table as $tableName => $tableArray) {
            foreach ($tableArray as $field => $fieldArray) {
                foreach ($fieldArray as $attribute => $value) {
                    if ($value != $schema2Table[$tableName][$field][$attribute] && !in_array($attribute, $ignoreField)) {
                        $schema2Str .= DatabaseSchema::alterTable($fieldArray, $tableName);
                        break;
                    }
                }
            }
        }
        $schemaStr = "Schema 1:" . $schema1Str . "\n-------------------------------------------------------------------------------------------------------\nSchema 2: \n" . $schema2Str;
        $this->_schema = $schemaStr;
        echo str_replace("\n", '<br/>', $schemaStr);
    }

    public function forceSqlDownload() {
        $schemaStr = $this->_schema;
        $fileName = "schema.sql";
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=$fileName;size=" . strlen($schemaStr));
        echo $schemaStr;
        exit;
    }

    public static function alterTable($column, $table) {
        $primaryKey = '';
        $uniqueKey = '';
        $columnName = $column['COLUMN_NAME'];
        $columnType = $column['COLUMN_TYPE'];
        $columnIsNull = ($column['IS_NULLABLE'] == 'NO') ? 'NOT NULL' : 'NULL';
        $columnExtra = ($column['EXTRA'] == 'auto_increment') ? 'AUTO_INCREMENT' : '';
        $columnDefault = '';
        if ($column['COLUMN_DEFAULT']) {
            if ($column['COLUMN_DEFAULT'] == 'CURRENT_TIMESTAMP') {
                $columnDefault = "DEFAULT " . $column['COLUMN_DEFAULT'];
            } else {
                $columnDefault = "DEFAULT '" . $column['COLUMN_DEFAULT'] . "'";
            }
        }
        $columnComment = '';
        $columnCharacter = '';
        if ($column['COLLATION_NAME']) {
            $columnCharacter = "CHARACTER SET " . $column['CHARACTER_SET_NAME'] . " COLLATE " . $column['COLLATION_NAME'];
        }
        if ($column['COLUMN_COMMENT']) {
            $columnComment = "COMMENT '" . $column['COLUMN_COMMENT'] . "'";
        }
        if ($column['COLUMN_KEY'] == 'PRI') {
            $primaryKey = "ALTER TABLE `$table` DROP PRIMARY KEY, ADD PRIMARY KEY(`$columnName`);\n";
        }
        if ($column['COLUMN_KEY'] == 'UNI') {
            $uniqueKey = "ALTER TABLE `$table` ADD UNIQUE( `$columnName`);\n";
        }
        $alterStr = trim("ALTER TABLE `$table` CHANGE `$columnName` `$columnName` $columnType $columnCharacter $columnIsNull $columnDefault $columnExtra  $columnComment") . ";\n";
        if ($primaryKey) {
            $alterStr .= $primaryKey;
        }
        if ($uniqueKey) {
            $alterStr.= $uniqueKey;
        }
        return $alterStr;
    }

    public static function createTable($tableArray, $table) {
        $tableStr = "CREATE TABLE IF NOT EXISTS `$table` (\n ";
        $primaryKey = '';
        $uniqueKeys = array();
        $hasAutoIncrement = false;
        foreach ($tableArray as $column) {
            $columnName = $column['COLUMN_NAME'];
            $columnType = $column['COLUMN_TYPE'];
            $columnIsNull = ($column['IS_NULLABLE'] == 'NO') ? 'NOT NULL' : 'DEFAULT NULL';
            $columnExtra = ($column['EXTRA'] == 'auto_increment') ? 'AUTO_INCREMENT' : '';
            $columnDefault = '';
            if ($column['COLUMN_DEFAULT']) {
                if ($column['COLUMN_DEFAULT'] == 'CURRENT_TIMESTAMP') {
                    $columnDefault = "DEFAULT " . $column['COLUMN_DEFAULT'];
                } else {
                    $columnDefault = "DEFAULT '" . $column['COLUMN_DEFAULT'] . "'";
                }
            }
            $columnComment = '';
            $columnCharacter = '';
            if ($columnExtra) {
                $hasAutoIncrement = true;
            }
            if ($column['COLLATION_NAME']) {
                $columnCharacter = "CHARACTER SET " . $column['CHARACTER_SET_NAME'] . " COLLATE " . $column['COLLATION_NAME'];
            }
            if ($column['COLUMN_COMMENT']) {
                $columnComment = "COMMENT '" . $column['COLUMN_COMMENT'] . "'";
            }
            if ($column['COLUMN_KEY'] == 'PRI') {
                $primaryKey = "PRIMARY KEY (`$columnName`)";
            }
            if ($column['COLUMN_KEY'] == 'UNI') {
                $uniqueKeys[] = "UNIQUE KEY `$columnName` (`$columnName`)";
            }
            $columnStr = trim("`$columnName` $columnType $columnCharacter $columnIsNull $columnDefault $columnExtra  $columnComment") . ",\n";
            $tableStr .= $columnStr;
        }
        $tableStr .= $primaryKey;
        if ($uniqueKeys) {
            $tableStr .= ",\n";
        }
        $tableStr .= implode(",\n", $uniqueKeys);
        $tableStr .="\n)ENGINE=MyISAM DEFAULT CHARSET=utf8 ";
        if ($hasAutoIncrement) {
            $tableStr .= "AUTO_INCREMENT=1 ;\n";
        } else {
            $tableStr .= ";\n";
        }
        return $tableStr;
    }

}

$settings = array('localhost', 'root', '123456', 'development');
$settings2 = array('localhost', 'root', '123456', 'second_schema');
$db = new DatabaseSchema($settings);
$db2 = new DatabaseSchema($settings2);
$db->compareDatabase($db2);
?>
