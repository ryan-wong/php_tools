<?php

class DatabaseSchema {

    protected $_databaseName = '';
    protected $_tableNames = array();
    protected $_tables = array();
    protected $_con = null;

    public function __construct($databaseName, $settings) {
        $this->_databaseName = $databaseName;
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

    public static function compareDatabase(DatabaseSchema $schema2) {
        $tableDifferences = array_diff_assoc($this->getTableNames(), $schema2->getTableNames());
        foreach ($tableDifferences as $diff) {
            if (in_array($diff, $this->getTableNames())) {
                echo "Table $diff is in Database Schema 1";
                echo DatabaseSchema::createTable($this->getTables()[$diff]);
            } else {
                echo "Table $diff is in Database Schema 2";
                echo DatabaseSchema::createTable($schema2->getTables()[$diff]);
            }
        }
    }

    public static function createTable($table) {
        
    }

}

$settings = array('localhost', 'root', '123456', 'development');
$db = new DatabaseSchema('development',$settings);
var_dump($db->getTableNames());
var_dump($db->getTables());
?>
