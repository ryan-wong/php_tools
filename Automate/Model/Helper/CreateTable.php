<?php

class Yesup_Automate_Model_Helper_CreateTable extends Yesup_Automate_Model_Helper_CreateAbstract {

    protected $_code = '';
    protected $_name = 'default';
    protected $_module = '';
    protected $_custom = '';
    protected $_searchForm = '';
    protected $_table = null;
    protected $_hover = '';

    public function generateTable($post) {
        $this->_table = $this->getTableMeta();
        $this->_module = $post['module'];
        $this->_code = $this->_methHeader();
        $this->_code .= $this->_methInit($post);
        $this->_code .= $this->_custom;
        if ($post['searchform'] == '1') {
            $this->_code .= $this->_methSearch();
        }
        if ($post['hoverDetail'] == '1') {
            $this->_code .= $this->_methHover();
        }
        $this->_code .= $this->_methEnd();
    }

    private function _methHeader() {
        $module = ucfirst($this->_module);
        $name = ucfirst($this->_name);
        $header = "<?php\n";
        $header .= "class App_Table_$module" . "$name" . "List extends Yesup_Table_UiTable {\n";
        $header .= "\tprotected \$_emptyHtml = 'No $name found';\n";
        return $header;
    }

    private function _methInit($post) {
         $name = ucfirst($this->_name);
        $init = "public function init(){\n\t";
        if ($post['searchform'] == '1') {
            $init .= "\$form = \$this->_createSearchForm();\n";
            $init .= "\t\$this->setSearchForm(\$form);\n\t";
        }
        $row = '';
        $hovered = false;
        if($post['addbutton'] == '1'){
            $init .= "\t\$this->addButton('/".$this->_module."/".$this->CapitaltodashCapital($this->_name)."/add','Add $name', array('icon'=>'circle-plus'));\n";
        }
        foreach ($this->getColumns() as $column) {
            $f2ncol = field2name($column);
            $u2scol = underscoreToSpace($column);
            if ($post['show' . $column] == '1') {
                $option = array('type' => 'text');
                $row = "\$this->addField('" . $post['datapath' . $column] . "',";
                if (strlen($post['title' . $column]) > 0) {
                    $option['headerLabel'] = $post['title' . $column];
                }
                if (strlen($post['help' . $column]) > 0) {
                    $option['helper'] = $post['help' . $column];
                    $this->_hover .= "\t\$detail[] = '$u2scol: '.\$rowObject->get$f2ncol" . "();\n";
                    $hovered = true;
                }if (strlen($post['custom' . $column]) > 0) {
                    $option['custom'] = $post['custom' . $column];
                    $this->_custom .= $this->_helperCustom($post['custom' . $column]);
                    $this->_hover .= "\t\$detail[] = '$u2scol: '.\$this->" . $post['custom' . $column] . "(\$rowObject);\n";
                    $hovered = true;
                }
                if (strlen($post['empty' . $column]) > 0) {
                    $option['empty'] = $post['empty' . $column];
                }
                if ($post['mapping' . $column] == '1') {

                    $enumStr = $this->_table['metadata'][$column]['DATA_TYPE'];
                    if (str_start_with($enumStr, 'enum')) {
                        $option['mapping'] = $this->_helperMapping($enumStr);
                        $this->_hover .= "\t\$detail[] = '$u2scol: '.\$rowObject->get$f2ncol" . "();\n";
                        $hovered = true;
                    }
                }
                $enumStr = $this->_table['metadata'][$column]['DATA_TYPE'];
                if (str_start_with($enumStr, 'enum')) {
                    $array = $this->_helperMapping($enumStr);
                    $this->_searchForm .= "\t$$column = Yesup_Automate_Elements::createSelect('$column',$array);\n";
                    $this->_searchForm .= "\t$$column" . "->setLabel('" . ucfirst(underscoreToSpace($column)) . ": ');\n";
                    $this->_searchForm .= "\t\$form->addElement($$column);\n\n";
                } else {
                    $this->_searchForm .= "\t$$column = Yesup_Automate_Elements::createTextNotRequired('$column');\n";
                    $this->_searchForm .= "\t$$column" . "->setLabel('" . ucfirst(underscoreToSpace($column)) . ": ');\n";
                    $this->_searchForm .= "\t\$form->addElement($$column);\n\n";
                }

                $row .= $this->_helperArray($option) . "\t";
                $init .= $row;
                
            }
            if (!$hovered) {
                $this->_hover .= "\t\$detail[] = '$u2scol: '.\$rowObject->get$f2ncol" . "();\n";
            }
            $hovered = false;
        }
         if($post['editbutton'] == '1'){
            $init .= "\t\$this->addOperation('/".$this->_module."/".$this->CapitaltodashCapital($this->_name)."/edit','Edit', array('icon' =>'pencil'));\n";
        }
        $init .= "\n}\n";
        return $init;
    }

    protected function _methSearch() {
        $result = "public function _createSearchForm() {\n";
        $result .= "\t\$form = new Yesup_UiTableForm();\n";
        $result .= "\t\$form->setColumnsNumber(3);\n";
        $result .= "\t\$form->setLabelMinWidth(130);\n\n";
        $result .= $this->_searchForm;
        $result .= "\t\$submit = new Yesup_Form_Element_UiSubmit('submit');\n";
        $result .= "\t\$submit->setLabel('Search');\n";
        $result .= "\t\$form->addElement(\$submit);\n";
        $result .= "\treturn \$form;\n}\n";
        return $result;
    }

    protected function _methHover() {
        $name = ucfirst($this->_name);
        $result = "protected function _getRowAttribs(\$rowObject, \$class) {\n";
        $result .= "\t\$detail = array();\n";
        $result .= $this->_hover;
        $result .= "\t\$class .= ' tips';\n";
        $result .= "\t\$class = trim(\$class);\n";
        $result .= "\t\$classString = parent::_getRowAttribs(\$rowObject, \$class);\n";
        $result .= "\t\$classString .= ' title=\"$name detail | ' . implode(' | ', \$detail) . '\"';\n";
        $result .= "\treturn \$classString;\n}\n";
        $result .= "protected function _afterRender() {\n";
        $result .= "\t\$this->getView()->clueTip()->enable('tr.tips');\n";
        $result .= "\treturn parent::_afterRender();\n}\n";    
        return $result;
    }

    protected function _methEnd() {
        return "}\n";
    }

    public function serveString() {
        $filename = ucfirst($this->_module).ucfirst($this->_name) . 'List.php';
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
        header('Content-Disposition: attachment;filename = "' . $filename . '"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');
        flush();
        ob_start();
        echo ($this->_code);
        exit;
    }

    protected function _helperArray($col = array()) {
        $str = 'array(';
        foreach ($col as $key => $row) {
            if (!str_start_with($row, 'array')) {
                $str .= "'$key' => '$row',";
            } else {
                $str .= "'$key' => $row,";
            }
        }
        return substr($str, 0, strlen($str) - 1) . "));\n";
    }

    protected function _helperCustom($custom) {
        $str = "public function $custom(\$row){\n\treturn '';\n}\n";
        return $str;
    }

}