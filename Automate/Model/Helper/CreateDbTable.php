<?php

class Yesup_Automate_Model_Helper_CreateDbTable extends Yesup_Automate_Model_Helper_CreateAbstract {

    protected $_code = '';
    protected $_name = 'default';

    public function __construct($name = '') {
        $this->_name = $name;
        $this->_code = $this->_methHeader();
    }

    private function _methHeader() {
        $name = ucfirst($this->_name);
        return "<?php\nclass Model_DbTable_$name extends Yesup_Db_Table_Abstract{\n}\n";
    }
    public function serveString() {
        $filename = ucfirst($this->_name) . '.php';
        ob_flush();
        if (ini_get('zlib.output_compression')) {
            $level = 1;
        } else {
            $level = 0;
        }
        while (ob_get_level() > $level) {
            ob_end_clean();
        }
        // required for IE, otherwise Content-Disposition may be ignored
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');
        flush();
        ob_start();
        echo ($this->_code);
        exit;
    }

}