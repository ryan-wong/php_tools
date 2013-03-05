<?php

class Yesup_Automate_Model_Helper_CreateController extends Yesup_Automate_Model_Helper_CreateAbstract {

    protected $_code = '';
    protected $_name = 'default';
    protected $_module = '';
    protected $_table = array();

    public function __construct($name = '') {
        $this->_name = $name;
    }

    public function generateController($post) {
        $name = ucfirst($this->_name);
        if (strlen($post['module']) > 0) {
            $this->_module = $post['module'];
        }
        if ($post['resource'] == '1') {
            $this->_code = $this->_methHeaderResource();
        } else {
            $this->_code = $this->_methHeader();
        }

        if ($post['list'] == '1') {
            //$this->serveString('list.phtml', $this->_methListPhtml());
            if ($post['searchform'] == '1') {
                $this->_code .= $this->_methListHeader();
                $this->_code .= $this->_methListSearchForm();
                $this->_code .= $this->_methListBody();
            } else {
                $this->_code .= $this->_methListHeader();
                $this->_code .= $this->_methListBody();
            }
        }
        if ($post['add'] == '1') {
            // $this->serveString('add.phtml', $this->_methAddPhtml());
            $this->_code .= $this->_methAdd();
        }
        if ($post['edit'] == '1') {
            //$this->serveString('edit.phtml', $this->_methEditPhtml());
            $this->_code .= $this->_methEdit();
        }
        $this->_code .= "}\n";
        $this->serveString($name . "Controller.php", $this->_code);
    }

    private function _methHeaderResource() {
        $name = ucfirst($this->_name);
        $module = ucfirst($this->_module);
        $result = "<?php\nclass $module" . "_$name" . "Controller extends App_Controller_Resource{\n";
        $result .= "\tpublic function getDeniedMessage() {\n";
        $result .= "\t\treturn 'You tried to access an unexisted $name.';\n\t}\n";
        $result .= "\tpublic function getDeniedRedirect() {\n";
        $result .= "\t\treturn '/" . $this->_module . "/" . $this->CapitaltodashCapital($this->_name) . "/list';\n\t}\n";
        return $result;
    }

    private function _methHeader() {
        $name = ucfirst($this->_name);
        $module = ucfirst($this->_module);
        return "<?php\nclass $module" . "_$name" . "Controller extends App_Controller_Action{\n";
    }

    private function _methListHeader() {
        $result = "\tpublic function listAction() {\n";
        $result .= "\t\t\$search = array();\n";
        $result .= "\t\t\$order = array();\n";
        return $result;
    }

    private function _methListBody() {
        $name = ucfirst($this->_name);
        $module = ucfirst($this->_module);
        $result = "\t\t\$table = new App_Table_$module" . "$name" . "List(Model_$name::getAll$name(\$search, \$order));\n";
        $result .= "\t\t\$this->view->table = \$table;\n}\n";
        return $result;
    }

    private function _methListSearchForm() {
        $cols = $this->getColumns();
        $result = '';
        foreach ($cols as $col) {
            $ucol = lcfirst(field2name($col));
            $result .= "\t\t\$$ucol = \$this->_getParam('$col');\n";
            $result .= "\t\tif ($$ucol) {\n";
            $result .= "\t\t\$search['$col'] = $$ucol;\n\t}\n";
        }
        return $result;
    }

    private function _methAdd() {
        $name = ucfirst($this->_name);
        $module = ucfirst($this->_module);
        $result = '';
        $result .= "\tpublic function addAction(){\n";
        $result .= "\t\t\$form = new App_Form_$module" . "$name();\n";
        $result .= "\t\t\$this->view->form = \$form;\n";
        $result .= "\t\tif(\$this->_request->isPost()){\n";
        $result .= "\t\t\t\tif(\$form->isValid(\$_POST)){\n";
        $quote = '';
        foreach ($this->getColumns() as $col) {
            if ($col != 'id') {
                $ucol = lcfirst(field2name($col));
                $quote .= '$' . $ucol . ',';
                $result .= "\t\t\t\t\t$$ucol = \$form->getValue('$col');\n";
            }
        }
        $quote = substr($quote, 0, -1);
        $result .= "\t\t\t\t\t$$name = Model_$name::add$name($quote);\n";
        $result .= "\t\t\t\t\tif($$name){\n";
        $result .= "\t\t\t\t\t\t\$this->flashInfo('Added $name');\n";
        $result .= "\t\t\t\t\t\t\$this->_redirect('/" . $this->_module . "/" . $this->CapitaltodashCapital($this->_name) . "/list');\n";
        $result .= "\t\t\t\t\t}else{\n";
        $result .= "\t\t\t\t\t\t\$this->view->uiErrorBlock('Failed to add $name.');\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t}\n";
        return $result;
    }

    private function _methEdit() {
        $name = lcfirst($this->_name);
        $uname = ucfirst($this->_name);
        $module = ucfirst($this->_module);
        $result = '';
        $result .= "\tpublic function editAction(){\n";
        $result .= "\t\t$$name" . "Id = \$this->_getParam('id');\n";
        $result .= "\t\tif (!$$name" . "Id) {\n";
        $result .= "\t\t\t\$this->flashAlert('Missing $name ID');\n";
        $result .= "\t\t\t\$this->_redirect('/" . $this->_module . "/" . $this->CapitaltodashCapital($this->_name) . "/list');\n";
        $result .= "\t\t\texit;\n\t\t\t}\n";
        $result .= "\t\t$$name =  new Model_$uname ($$name" . "Id" . ");\n";
        $result .= "\t\t\$form = new App_Form_$module" . "$uname (array('" . $this->_name . "' =>$$name));\n";
        $result .= "\t\t\$this->view->form = \$form;\n";
        $result .= "\t\tif(\$this->_request->isPost()){\n";
        $result .= "\t\t\tif(\$form->isValid(\$_POST)){\n";
        $getter = '';
        $setter = '';
        foreach ($this->getColumns() as $col) {
            if ($col != 'id') {
                $ucol = lcfirst(field2name($col));
                $ccol = field2name($col);
                $getter .= "\t\t\t\t$$ucol = \$form->getValue('$col');\n";
                $setter .= "\t\t\t\t$$name" . "->set$ccol($$ucol);\n";
            }
        }
        $result .= $getter;
        $result .= $setter;
        $result .= "\t\t\t\tif($$name" . "->saveChanges()" . "){\n";
        $result .= "\t\t\t\t\t\$this->flashInfo('Updated $name');\n";
        $result .= "\t\t\t\t\t\$this->_redirect('/" . $this->_module . "/" . $this->CapitaltodashCapital($this->_name) . "/list');\n";
        $result .= "\t\t\t\t}else{\n";
        $result .= "\t\t\t\t\t\$this->view->uiErrorBlock('Failed to Update $name.');\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t}\n";
        return $result;
    }

    public function _methListPhtml() {
        $name = ucfirst($this->_name);
        $result = "<?php \n";
        $result .= "echo \$this->previousPageLink('');\n";
        $result .= "\$this->table->setTitle('$name');\n";
        $result .= "echo \$this->table;\n";
        return $result;
    }

    public function _methAddPhtml() {
        $name = ucfirst($this->_name);
        $result = "<?php \n";
        $result .= "echo \$this->previousPageLink('');\n";
        $result .= "\$this->uiBlockWrapper()->setTitle('Add $name');\n";
        $result .= "echo \$this->form;\n";
        return $result;
    }

    public function _methEditPhtml() {
        $name = ucfirst($this->_name);
        $result = "<?php \n";
        $result .= "echo \$this->previousPageLink('');\n";
        $result .= "\$this->uiBlockWrapper()->setTitle('Edit $name');\n";
        $result .= "echo \$this->form;\n";
        return $result;
    }

    public function serveString($filename = 'php.php', $str) {
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
        echo ($str);
        exit;
    }

   

}