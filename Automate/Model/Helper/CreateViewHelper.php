<?php

class Yesup_Automate_Model_Helper_CreateViewHelper extends Yesup_Automate_Model_Helper_CreateAbstract {

    protected $_code = '';
    protected $_name = '';
    protected $_field = '';
    protected $_folder = '';

    public function generateHelper($post) {
        $this->_name = $post['name'];
        $this->_field = $post['field'];
        $this->_folder = $post['folder'];
        $this->_code = $this->_methHeader();
        $this->_code .= $this->_methSetHelper();
        
    }

    private function _methHeader() {
        $folder = ucfirst($this->_folder);
        $name = ucfirst($this->_name);
        $result = "<?php\n\t";
        $result .= "class $folder" . "_View_Helper_$name extends Zend_View_Helper_Abstract {\n";
        return $result;
    }

    private function _methSetHelper() {
        $name = lcfirst($this->_name);
        $field = $this->_field;
        if ($field) {
            $field .= ' $object';
        } else {
            $field = '$object';
        }
        $result = "public function $name($field) {\n return '';\n    }\n}";
        return $result;
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