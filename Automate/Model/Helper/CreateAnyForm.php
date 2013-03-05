<?php

class Yesup_Automate_Model_Helper_CreateAnyForm extends Yesup_Automate_Model_Helper_CreateAbstract {

    protected $_code = '';
    protected $_name = '';

    public function generateForm($post) {
        $this->_name = $post['filename'];
        $this->_code = $this->_methHeader($post['filename']);
        $this->_code .= $this->_methSetModel($post['model_name']);
        $this->_code .= $this->_methInit($post);
        $this->_code .= $this->_methEnd();
    }

    private function _methHeader($filename = '') {
        $filename = ucfirst($filename);
        $result = "<?php\n\t";
        $result .= "class App_Form_$filename extends Yesup_UiForm {\n";
        return $result;
    }

    private function _methSetModel($model) {
        $ucModel = ucfirst($model);
        $result = "\tprotected \$_$model=null;\n\t";
        $result .= "public function set$ucModel($$model) {\n\t";
        $result .= "\t\$this->_$model= $$model;\n\t}\n";
        return $result;
    }

    private function _methInit($post) {
        $model = $post['model_name'];
        $element = "\t//Elements\n";
        $label = "\t//Labels\n";
        $filter = "\t//Filters\n";
        $validator = "\t//Validators\n";
        $setValue = "\t//Set Value\n\tif(\$this->_$model){\n";
        $description = "\t//Description\n";
        $required = "\t//Required\n";
        $selectOptions = '';
        $addElement = "\t//Add Element\n";
        $result = "\tpublic function init(){\n";
        $result .= "\t\$this->setLabelMinWidth(140);\n";
        $result .= "\t\$this->setTextFieldWidth(250);\n";
        for ($i = 0; $i < $post['number']; $i++) {
            $name = $post['name' . "field$i"];
            $elementVar = $post['element' . "field$i"];
            $labelVar = $post['label' . "field$i"];
            $requireVar = $post['require' . "field$i"];
            $filterVar = $post['filter' . "field$i"];
            $validatorVar = $post['validator' . "field$i"];
            $descriptionVar = $post['description' . "field$i"];
            $element .= "\t$" . lcfirst(field2name($name)) . " = " . $elementVar . "('$name');\n";
            if ($elementVar == 'new Yesup_Form_Element_UiSelect') {
                $selectOptions .= "\t$" . lcfirst(field2name($name)) . "->setMultiOptions(array(''=>''\n);\n";
            }
            $label .= "\t$" . lcfirst(field2name($name)) . "->setLabel('" . $labelVar . ":');\n";
            if ($requireVar == '1') {
                $required .= "\t$" . lcfirst(field2name($name)) . "->setRequired();\n";
            }
            if (strlen($filterVar) > 0) {
                $filter .= "\t$" . lcfirst(field2name($name)) . "->addFilter(" . $filterVar . ");\n";
            }
            if (strlen($validatorVar) > 0) {
                $validator .= "\t$" . lcfirst(field2name($name)) . "->addValidator(" . $validatorVar . ");\n";
            }
            if (strlen($descriptionVar) > 0) {
                $description .= "\t$" . lcfirst(field2name($name)) . "->setDescription('" . $descriptionVar . "');\n";
            }
            if ($elementVar == 'new Yesup_Form_Element_UiXhtml') {
                $setValue .= "\t\t$" . lcfirst(field2name($name)) . "->setText(\$this->_$model" . "->get" . field2name($name) . "());\n";
            } else {
                $setValue .= "\t\t$" . lcfirst(field2name($name)) . "->setValue(\$this->_$model" . "->get" . field2name($name) . "());\n";
            }
            $addElement .= "\t\$this->addElement($" . lcfirst(field2name($name)) . ");\n";
        }
        $setValue .="\t}\n";
        $addElement .= "\t\$this->addSubmit('Add $model');\n";
        $result .= $element . $label . $required . $description . $filter . $validator . $selectOptions . $setValue . $addElement;

        $result .= "\t}\n";
        return $result;
    }

    private function _methEnd() {
        return "}\n";
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