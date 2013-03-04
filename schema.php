<?php

class DatabaseSchema {

    protected $_databaseName = '';
    protected $_tableNames = array();
    protected $_tables = array();
    protected $_con = null;

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
                    $this->_tables[$table][] = $tableArray;
                }
            }
        }
        return $this->_tables;
    }

    public function compareDatabase(DatabaseSchema $schema2) {
        $tableDifferences = array_diff_assoc($this->getTableNames(), $schema2->getTableNames());
		$schema1Str = '';
		$schema2Str = '';
        foreach ($tableDifferences as $diff) {
            if (in_array($diff, $this->getTableNames())) {
                //echo "Table $diff is in Database Schema 1<br/>";
                $schema1Str .= DatabaseSchema::createTable($this->getTables()[$diff],$diff);
            } else {
                //echo "Table $diff is in Database Schema 2<br/>";
                $schema2Str.= DatabaseSchema::createTable($schema2->getTables()[$diff],$diff);
            }
        }
      $schemaStr = $schema1Str."\n-------------------\n".$schema2Str;
      $fileName = "schema.sql";  
      header("Content-type: application/force-download");
      header("Content-Disposition: attachment; filename=$fileName;size=" . strlen($schemaStr));
      echo $schemaStr;
      exit;
    }

    public static function createTable($tableArray,$table) {
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
                    $columnDefault = "DEFAULT '" . $column['COLUMN_DEFAULT']."'";
                }
            }
            $columnComment = '';
            $columnCharacter = '';
            if ($columnExtra) {
                $hasAutoIncrement = true;
            }
            if ($column['COLLATION_NAME']) {
                $columnCharacter = "CHARACTER SET ".$column['CHARACTER_SET_NAME']." COLLATE " . $column['COLLATION_NAME'];
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
            $columnStr = trim("`$columnName` $columnType $columnCharacter $columnIsNull $columnDefault $columnExtra  $columnComment").",\n";
            $tableStr .= $columnStr;
        }
        $tableStr .= $primaryKey;
        if($uniqueKeys){
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
//        CREATE TABLE IF NOT EXISTS `testsss` (
//  `id` int(12) NOT NULL AUTO_INCREMENT COMMENT 'sdfsdfsd',
//  `erw` varchar(123) CHARACTER SET big5 COLLATE big5_bin DEFAULT NULL COMMENT 'sxxxxxxxdfsdffdfdd',
//  `sffsdf` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'cxcxvxv',
//  PRIMARY KEY (`id`),
//  UNIQUE KEY `erw` (`erw`)
//) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='testttttttt' AUTO_INCREMENT=1 ;
// 'TABLE_CATALOG' => string 'def' (length=3)
//          'TABLE_SCHEMA' => string 'development' (length=11)
//          'TABLE_NAME' => string 'match' (length=5)
//          'COLUMN_NAME' => string 'id' (length=2)
//          'ORDINAL_POSITION' => string '1' (length=1)
//          'COLUMN_DEFAULT' => null
//          'IS_NULLABLE' => string 'NO' (length=2)
//          'DATA_TYPE' => string 'int' (length=3)
//          'CHARACTER_MAXIMUM_LENGTH' => null
//          'CHARACTER_OCTET_LENGTH' => null
//          'NUMERIC_PRECISION' => string '10' (length=2)
//          'NUMERIC_SCALE' => string '0' (length=1)
//          'CHARACTER_SET_NAME' => null
//          'COLLATION_NAME' => null
//          'COLUMN_TYPE' => string 'int(11)' (length=7)
//          'COLUMN_KEY' => string 'PRI' (length=3)
//          'EXTRA' => string 'auto_increment' (length=14)
//          'PRIVILEGES' => string 'select,insert,update,references' (length=31)
//          'COLUMN_COMMENT' => string '' (length=0)
    }

}

$settings = array('localhost', 'root', '123456', 'development');
$settings2 = array('localhost', 'root', '123456', 'second_schema');
$db = new DatabaseSchema($settings);
$db2 = new DatabaseSchema($settings2);
$db->compareDatabase($db2);
?>
