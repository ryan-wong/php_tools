<?php

class Yesup_Automate_Model_Helper_CreateModel extends Yesup_Automate_Model_Helper_CreateAbstract {

    protected $_code = '';
    protected $_name = 'default';

    public function __construct($name = '') {
        $this->_name = $name;
        $this->_code = $this->getMethods('_meth');
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

    private function _methHeader() {
        $name = ucfirst($this->_name);
        $tableClassName = $this->_getDbTableClassName($name);
        $table = new $tableClassName();
        $info = $table->info();
        $info = $info['cols'];
        $result = "<?php\nclass Model_$name extends Yesup_Orm_Cached{\n";
        foreach ($info as $col) {
            if (strpos($col, '_id')) {
                $objectName = str_replace('_id', '', $col);
                $result .= "\tprotected \$_$objectName = false;\n";
            }
        }
        return $result;
    }

    private function _methSearch() {
        $name = ucfirst($this->_name);
        $code = "public static function getAll$name(\$search, \$order) {\n";
        $code .= "\treturn Model_$name::_search(\$search, \$order);\n}\n";
        return $code;
    }

    private function _methPage() {
        $name = ucfirst($this->_name);
        $code = "public static function getAllPaged$name(\$search, \$order) {\n";
        $code .= "\treturn Model_$name::_pagedSearch(\$search, \$order);\n}\n";
        return $code;
    }

    private function _methForeign() {
        $name = ucfirst($this->_name);
        $tableClassName = $this->_getDbTableClassName($name);
        $table = new $tableClassName();
        $info = $table->info();
        $info = $info['cols'];
        $code = '';
        foreach ($info as $key => $col) {
            if (strpos($col, '_id')) {
                
            } else {
                unset($info[$key]);
            }
        }

        foreach ($info as $foreign) {
            $code .= $this->_helperForeign($foreign);
        }
        return $code;
    }

    private function _methAdd() {

        $name = ucfirst($this->_name);
        $lname = lcfirst($this->_name);
        $tableClassName = $this->_getDbTableClassName($name);
        $table = new $tableClassName();
        $info = $table->info();
        $quote = $this->_helperParameter($info['cols']);
        $data = "\n" . $this->_helperArray($info['cols']);
        $name = ucfirst($this->_name);
        $code = "public static function add$name$quote {\n";
        $code .= "\t\$data = array($data\n\t);\n";
        $code .= "\t$$lname = Model_$name::_addInstance(\$data);\n";
        $code .= "\tif ($$lname) {\n";
        $code .= "\t\treturn $$lname;";
        $code .= "\n\t} else {\n";
        $code .= "\t\treturn null;";
        $code .= "\n\t}\n}";
        return $code;
    }

    private function _methEnd() {
        return "\n}\n";
    }

    private function _helperParameter($col = array()) {
        unset($col[0]);
        foreach ($col as $key => $row) {
            $col[$key] = '$' . lcfirst(field2name($row));
        }
        return '(' . implode(',', $col) . ')';
    }

    private function _helperArray($col = array()) {
        unset($col[0]);
        foreach ($col as $key => $row) {

            $col[$key] = "\t\t'$row'" . "=>" . "$" . lcfirst(field2name($row)) . ",";
        }
        return implode("\n", $col);
    }

    private function _helperForeign($foreign = '') {
        $foreign = str_replace('_id', '', $foreign);
        $name = ucfirst(name2field($foreign));
        $foreign = str_replace('_id', '', $foreign);
        $code = "public function get$name() {\n";
        $code .= "\tif (\$this->_$foreign === false) {\n";
        $code .= "\t\t$$foreign = new Model_$name(\$this->get$name" . "Id());\n";
        $code .= "\t\t\$this->_$foreign = $$foreign;\n\t}\n\t";
        $code .= "return \$this->_$foreign;\n}\n";
        return $code;
    }

    public function getMethods($startwith = '_') {
        $not = array('__construct', '__toString', 'getMethods');
        $class_methods = get_class_methods(get_class());
        $result = '';
        foreach ($class_methods as $key => $method) {
            if (str_start_with($method, $startwith) && !in_array($method, $not)) {
                $result .= $this->$method();
            }
        }
        return $result;
    }

}
