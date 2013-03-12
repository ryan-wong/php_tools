<?php

include_once 'connection.php';

class ORM {

    protected $_data = array();
    protected $_timestamp = '';
    protected $_table = '';
    protected $_primaryKey = 'id';

    public function __construct($id, $data = array()) {    	
        if (empty($data)) {
            $con = Connection::getConnection();
            $query = "SELECT * FROM  `" . $this->_table . "` WHERE `" . $this->_primaryKey . "`=$id";
            $res = $con->query($query);
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
        $con = Connection::getConnection();
        $stmt = $con->prepare($query);
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
        $databaseName = Connection::getDatabaseName();
        $className = get_called_class();
        $table = lcfirst(get_called_class());
        $primaryKey = 'id';
        $keyValue = array($primaryKey => 'NULL');
        foreach (array_keys($data) as $key) {
            if (is_numeric($data[$key])) {
                $value = $data[$key];
            } else {
                $value = "'" . $data[$key] . "'";
            }
            $keyValue["`$key`"] = $value;
        }
        $fields = implode(',', array_keys($keyValue));
        $questions = array();
        for ($i = 0; $i < count($keyValue); $i++) {
            $questions[] = '?';
        }
        $questionStr = implode(',', $questions);
        $values = implode(',', array_values($keyValue));
        $query = "INSERT INTO `$databaseName`.`$table`($fields) VALUES ($values);";
        $con = Connection::getConnection();
        $stmt = $con->prepare($query);

        if (!($stmt->execute())) {
            return false;
        }
        $id = $con->insert_id;
        $stmt->close();
        $object = new $className($id);
        $object->touch();
        return $object;
    }

    public function touch() {
        if ($this->_timestamp) {
            $this->save();
        }
    }

    public static function search($criteria, $orderBy = null, $limit = null) {
        $databaseName = Connection::getDatabaseName();
        $className = get_called_class();
        $table = lcfirst(get_called_class());
        $con = Connection::getConnection();
        $query = "SELECT * FROM `$databaseName`.`$table`";
        $where = array();
        foreach ($criteria as $field => $match) {
            $match = str_replace("'", "", $match);
            if (!is_numeric($match) && !is_array($match)) {
                $match = "'$match'";
            }
            if (is_numeric($field)) {
                $where[] = "`id` = $match";
            } elseif (strpos($field, '?') === false) {
                if (is_array($match)) {
                    $orConds = array();
                    foreach ($match as $value) {
                        $orConds[] = "`$field` = $value";
                    }
                    $where[] = '( ' . implode(' OR ', $orConds) . ' ) ';
                } else {
                    $where[] = "`$field` = $match";
                }
            } else {
                $where[] = str_replace('?', $match, $field);
            }
        }
        $whereStr = implode(" AND ", $where);
        if($where){
        	$whereStr = " WHERE ".$whereStr;
        }
        $orderByStr = '';
        $limitStr = '';
        if ($orderBy) {
            if (is_array($orderBy)) {
                $orderByStr = 'ORDER BY ' . implode(' , ', $orderBy);
            } else {
                $orderByStr = 'ORDER BY ' . $orderBy;
            }
        }
        if ($limit) {
            $limitStr = "LIMIT $limit";
        }
        $query = " $query $whereStr $orderByStr $limitStr";
        $query = "SELECT * FROM `userDetail` WHERE 1";
        $result = array();
        $res = $con->query($query);
        $res->data_seek(0);
        while ($row = $res->fetch_assoc()) {
            if ($row) {
                $data = array();
                $fields = array_keys($row);
                foreach ($fields as $field) {
                    $data[$field] = $row[$field];
                }
                $result[] = new $className(null, $data);
            }
        }
        return $result;
    }

}

?>
