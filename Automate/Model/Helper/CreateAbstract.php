<?php

abstract class Yesup_Automate_Model_Helper_CreateAbstract {

    protected $_name = '';

    public function __construct($name = '') {
        $this->_name = $name;
    }

    public function _getDbTableClassName($className) {
        return 'Model_DbTable_' . ucfirst($className);
    }

    public static function getAllTable() {
        $front = Zend_Controller_Front::getInstance();
        return $front->getParam("bootstrap")->getPluginResource('multidb')->getDb()->listTables();
    }

    public function getColumns() {
        $name = ucfirst($this->_name);
        $tableClassName = $this->_getDbTableClassName($name);
        $table = new $tableClassName();
        $info = $table->info();
        return $info['cols'];
    }

    public function __toString() {
        return $this->_code;
    }

    public function getTableMeta() {
        $name = ucfirst($this->_name);
        $tableClassName = $this->_getDbTableClassName($name);
        $table = new $tableClassName();
        $info = $table->info();
        return $info;
    }

    protected function _helperMapping($enum) {
        $enumStr = substr($enum, strpos($enum, '(') + 1);
        $enumStr = substr($enumStr, 0, strlen($enumStr) - 1);
        $option = "array(\n\t";
        foreach (explode(',', $enumStr) as $row) {
            //current form of $row  is 'x' so remove quote
            $rowNoQuote = str_replace('\'', '', $row);
            $option .= "'$rowNoQuote' => '" . field2name($rowNoQuote) . "',\n\t";
        }
        return substr($option, 0, strlen($option) - 1) . "\t)";
    }

    public function CapitaltodashCapital($field) {
        return str_replace(
                        array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'), array('-a', '-b', '-c', '-d', '-e', '-f', '-g', '-h', '-i', '-j', '-k', '-l', '-m', '-n', '-o', '-p', '-q', '-r', '-s', '-t', '-u', '-v', '-w', '-x', '-y', '-z'), lcfirst($field)
        );
    }

}
