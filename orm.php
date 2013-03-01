<?php

include_once 'connection.php';

class ORM {

    protected $_data = array();
    protected $_timeStamp = '';
    protected $_con;
    protected $_table = '';
    protected $_primaryKey = 'id';

    public function __construct($id) {
        $this->_con = Connection::getConnection();
        $query = "SELECT * FROM  `".$this->_table."` WHERE `".$this->_primaryKey."`=$id";
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
    }

    public function getData() {
        return $this->_data;
    }

}

?>
