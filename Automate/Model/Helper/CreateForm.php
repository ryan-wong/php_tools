<?php

class Yesup_Automate_Model_Helper_CreateForm extends Yesup_Automate_Model_Helper_CreateAbstract {

    protected $_code = '';
    protected $_name = 'default';
    protected $_filter = false;
    protected $_validator = false;
    protected $_elements = false;
    protected $_module = '';

    public function __construct($name = '') {
        $this->_name = $name;
    }

    public function generateForm($post) {
        $this->_module = $post['module'];
        $this->_code = $this->_methHeader();
        $this->_code .= $this->_methSetModel();
        $this->_code .= $this->_methInit($post);
        $this->_code .= $this->_methEnd();
    }

    private function _methHeader() {
        $name = ucfirst($this->_name);
        $module = ucfirst($this->_module);
        $result = "<?php\n\t";
        $result .= "class App_Form_$module" . "$name extends Yesup_UiForm {\n";
        return $result;
    }

    private function _methSetModel() {
        $model = $this->_name;
        $ucModel = ucfirst($this->_name);
        $result = "\tprotected \$_$model=null;\n\t";
        $result .= "public function set$ucModel($$model) {\n\t";
        $result .= "\t\$this->_$model= $$model;\n\t}\n";
        return $result;
    }

    private function _methInit($post) {
        $model = $this->_name;
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
        foreach ($this->getColumns() as $column) {
            if ($post['show' . $column] == '1') {
                $element .= "\t$" . lcfirst(field2name($column)) . " = " . $post['element' . $column] . "('$column');\n";
                if($post['element'.$column] == 'new Yesup_Form_Element_UiSelect'){
                    $table = $this->getTableMeta();                    
                    $enumStr = $table['metadata'][$column]['DATA_TYPE'];
                    $selectOptions .= "\t$" . lcfirst(field2name($column)) ."->setMultiOptions(".$this->_helperMapping($enumStr).");\n";
                }
                $label .= "\t$" . lcfirst(field2name($column)) . "->setLabel('" . ucfirst(underscoreToSpace($post['label' . $column])) . ":');\n";
                if ($post['require' . $column] == '1') {
                    $required .= "\t$" . lcfirst(field2name($column)) . "->setRequired();\n";
                }
                if (strlen($post['filter' . $column]) > 0) {
                    $filter .= "\t$" . lcfirst(field2name($column)) . "->addFilter(" . $post['filter' . $column] . ");\n";
                }
                if (strlen($post['validator' . $column]) > 0) {
                    $validator .= "\t$" . lcfirst(field2name($column)) . "->addValidator(" . $post['validator' . $column] . ");\n";
                }
                if (strlen($post['description' . $column]) > 0) {
                    $description .= "\t$" . lcfirst(field2name($column)) . "->setDescription('" . $post['description' . $column] . "');\n";
                }
                if ($post['element' . $column] == 'Yesup_Form_Element_UiXhtml') {
                    $setValue .= "\t\t$" . lcfirst(field2name($column)) . "->setText(\$this->_$model" . "->get" . field2name($column) . "());\n";
                } else {
                    $setValue .= "\t\t$" . lcfirst(field2name($column)) . "->setValue(\$this->_$model" . "->get" . field2name($column) . "());\n";
                }
                $addElement .= "\t\$this->addElement($" . lcfirst(field2name($column)) . ");\n";
            }
        }
        
        $setValue .="\t}\n";
        $addElement .= "\t\$this->addSubmit('Add $model');\n";
        $result .= $element . $label . $required . $description . $filter . $validator .$selectOptions. $setValue . $addElement;

        $result .= "\t}\n";
        return $result;
    }

    private function _methEnd() {
        return "}\n";
    }

    public function serveString() {
        $filename = ucfirst($this->_module) . ucfirst($this->_name) . '.php';
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

    public function setFilter($filter) {
        $this->_filter = $filter;
    }

    public function setValidator($validator) {
        $this->_validator = $validator;
    }

    public function setFormElement($element) {
        $this->_elements = $element;
    }

    public function getDefaultElement($type) {
        switch ($type) {
            case 'int':
                $element = array_search('UiText', $this->_elements);
                return ($element) ? $element : '';
                break;
            case 'float':
                $element = array_search('UiText', $this->_elements);
                return ($element) ? $element : '';
                break;
            case 'decimal':
                $element = array_search('UiText', $this->_elements);
                return ($element) ? $element : '';
                break;
            case 'varchar':
                $element = array_search('UiText', $this->_elements);
                return ($element) ? $element : '';
                break;
            case 'text':
                $element = array_search('UiTextarea', $this->_elements);
                return ($element) ? $element : '';
                break;
            case 'date':
                $element = array_search('UiDatePicker', $this->_elements);
                return ($element) ? $element : '';
                break;
            case 'timestamp':
                $element = array_search('UiDatePicker', $this->_elements);
                return ($element) ? $element : '';
                break;
            default :
                if (str_start_with($type, 'enum')) {
                    $element = array_search('UiSelect', $this->_elements);
                    return ($element) ? $element : '';
                }
                return '';
                break;
        }
    }

    public function getDefaultFilter($type) {
        switch ($type) {
            case 'int':
                $validator = array_search('Digits', $this->_filter);
                return ($validator) ? $validator : '';
                break;
            case 'varchar':
                $validator = array_search('StringTrim', $this->_filter);
                return ($validator) ? $validator : '';
                break;
            case 'text':
                $validator = array_search('StringTrim', $this->_filter);
                return ($validator) ? $validator : '';
                break;
            default :
                return '';
                break;
        }
    }

    public function getDefaultValidator($type) {
        switch ($type) {
            case 'int':
                $validator = array_search('Int', $this->_validator);
                return ($validator) ? $validator : '';
                break;
            case 'float':
                $validator = array_search('GreaterThan', $this->_validator);
                return ($validator) ? $validator : '';
                break;
            case 'varchar':
                $validator = array_search('Alnum', $this->_validator);
                return ($validator) ? $validator : '';
                break;
            default :
                return '';
                break;
        }
    }

}