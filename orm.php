<?php

include_once 'connection.php';

class ORM {

    protected $_data = array();
    protected $_timeStamp = '';
    protected $_con;
    protected $_table = '';
    protected $_primaryKey = 'id';

    public function __construct($id, $data = array()) {
        if (!$data) {
            $this->_con = Connection::getConnection();
            $query = "SELECT * FROM  `" . $this->_table . "` WHERE `" . $this->_primaryKey . "`=$id";
            $res = $this->_con->query($query);
            $res->data_seek(0);
            $row = $res->fetch_assoc();
            if ($row) {
                $fields = array_keys($row);
                foreach ($fields as $field) {
                    $this->_data[$field] = $row[$field];
                }
            } else {
                throw new Exception('Cannot find entity ' . $id);
            }
        } else {
            $this->_data = $data;
        }
    }

    public function __toString() {
        return '<pre>' . print_r($this->_data, true) . '</pre>';
    }

    public function save() {
        if ($this->_timestamp) {
            date_default_timezone_set("America/Toronto");
            $dateObject = new DateTime('NOW');
            $dateStr = $dateObject->format("Y-m-d H:i:s");
            if (is_array($this->_timestamp)) {
                foreach ($this->_timestamp as $timestamp) {
                    $this->_data[$timestamp] = $dateStr;
                }
            } else {
                $this->_data[$timestamp] = $dateStr;
            }
        }
        $databaseName = Connection::getDatabaseName();
        $table = $this->_table;
        $primaryKey = $this->_primaryKey;
        $id = $this->_data[$this->_primaryKey];
        $keyValue = array();
        foreach (array_keys($this->_data) as $key) {
            if (is_numeric($this->_data[$key])) {
                $value = $this->_data[$key];
            } else {
                $value = "'" . $this->_data[$key] . "'";
            }
            $keyValue[] = " `$key` = $value ";
        }
        $dataStr = implode(',', $keyValue);
        $query = "UPDATE  `$databaseName`.`$table` SET $dataStr WHERE `$table`.`$primaryKey` = $id";
        $stmt = $this->_con->prepare($query);
        call_user_func_array(array($stmt, 'bindparams'), array_values($this->_data));
        IF (!($stmt->execute())) {
            return false;
        }
        $stmt->close();
        return true;
    }

    public function __call($name, $arguments) {
        $field = lcfirst($this->name2field($name));
        if (array_key_exists($field, $this->_data)) {
            if ($arguments) {
                $this->_data[$field] = $arguments[0];
            }
            return $this->_data[$field];
        }
    }

    private function name2field($name) {
        return str_replace(
                        array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'), array('_a', '_b', '_c', '_d', '_e', '_f', '_g', '_h', '_i', '_j', '_k', '_l', '_m', '_n', '_o', '_p', '_q', '_r', '_s', '_t', '_u', '_v', '_w', '_x', '_y', '_z'), lcfirst($name)
        );
    }

    public static function add($data) {
        
    }

}

?>