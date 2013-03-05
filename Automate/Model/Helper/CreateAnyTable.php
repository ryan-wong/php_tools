<?php

class Yesup_Automate_Model_Helper_CreateAnyTable extends Yesup_Automate_Model_Helper_CreateTable {

    public function generateTable($post) {
        $this->_name = $post['filename'];
        $this->_code = $this->_methodHeader($post['filename']);
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

    protected function _methodHeader($filename) {
        $filename = ucfirst($filename);
        $header = "<?php\n";
        $header .= "class App_Table_$filename extends Yesup_Table_UiTable {\n";
        $header .= "\tprotected \$_emptyHtml = 'Not found';\n";
        return $header;
    }

    private function _methInit($post) {
        $init = "public function init(){\n\t";
        if ($post['searchform'] == '1') {
            $init .= "\$form = \$this->_createSearchForm();\n";
            $init .= "\t\$this->setSearchForm(\$form);\n\t";
        }
        $row = '';
        $hovered = false;
        $buttons = $post['button'];
        $operations = $post['operation'];
        //append buttons
        if (is_numeric($buttons) && $buttons > 0) {
            for ($i = 0; $i < $buttons; $i++) {
                if (strlen($post["buttonurl$i"]) > 0) {
                    $url = $post["buttonurl$i"];
                    $name = $post["buttonname$i"];
                    $icon = $post["buttonicon$i"];
                    if (strlen($icon) > 0) {
                        $icon = substr($icon, 8);
                        $init .= "\t\$this->addButton('$url','$name', array('icon'=>'$icon'));\n";
                    } else {
                        $init .= "\t\$this->addButton('$url','$name');\n";
                    }
                }
            }
        }
        //body part
        for ($i = 0; $i < $post['number']; $i++) {
            $option = array('type' => 'text');
            $data = $post["datapath$i"];
            $title = $post["title$i"];
            $row = "\$this->addField('" . $data . "',";
            //title
            if (strlen($title) > 0) {
                $option['headerLabel'] = $title;
            }
            //helper
            if (strlen($post["help$i"]) > 0) {
                $option['helper'] = $post["help$i"];
                $udata = field2name($data);
                $this->_hover .= "\t\$detail[] = '$title : '.\$rowObject->get$udata();\n";
                $hovered = true;
                //custom
            }if (strlen($post["custom$i"]) > 0) {
                $option['custom'] = $post["custom$i"];
                $this->_custom .= $this->_helperCustom($post["custom$i"]);
                $this->_hover .= "\t\$detail[] = '$title : '.\$this->" . $post["custom$i"] . "(\$rowObject);\n";
                $hovered = true;
            }
            if (strlen($post["empty$i"]) > 0) {
                $option['empty'] = $post["empty$i"];
            }
            $this->_searchForm .= "\t$$data = Yesup_Automate_Elements::createTextNotRequired('$data');\n";
            $this->_searchForm .= "\t$$data" . "->setLabel('" . ucfirst(underscoreToSpace($title)) . ": ');\n";
            $this->_searchForm .= "\t\$form->addElement($$data);\n\n";
            $row .= $this->_helperArray($option) . "\t";
            $init .= $row;
        }

        //append operation
        if (is_numeric($operations) && $operations > 0) {
            for ($i = 0; $i < $operations; $i++) {
                if (strlen($post["operationurl$i"]) > 0) {
                    $url = $post["operationurl$i"];
                    $name = $post["operationname$i"];
                    $icon = $post["operationicon$i"];
                    if (strlen($icon) > 0) {
                        $icon = substr($icon, 8);
                        $init .= "\t\$this->addOperation('$url','$name', array('icon'=>'$icon'));\n";
                    } else {
                        $init .= "\t\$this->addOperation('$url','$name');\n";
                    }
                }
            }
        }
        $init .= "\n}\n";
        return $init;
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
        header('Content-Disposition: attachment;filename = "' . $filename . '"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');
        flush();
        ob_start();
        echo ($this->_code);
        exit;
    }

    protected function _methHover() {
        $result = "protected function _getRowAttribs(\$rowObject, \$class) {\n";
        $result .= "\t\$detail = array();\n";
        $result .= $this->_hover;
        $result .= "\t\$class .= ' tips';\n";
        $result .= "\t\$class = trim(\$class);\n";
        $result .= "\t\$classString = parent::_getRowAttribs(\$rowObject, \$class);\n";
        $result .= "\t\$classString .= ' title=\"detail | ' . implode(' | ', \$detail) . '\"';\n";
        $result .= "\treturn \$classString;\n}\n";
        $result .= "protected function _afterRender() {\n";
        $result .= "\t\$this->getView()->clueTip()->enable('tr.tips');\n";
        $result .= "\treturn parent::_afterRender();\n}\n";
        return $result;
    }

}

