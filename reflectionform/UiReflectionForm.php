<?php

class Yesup_UiReflectionForm extends Yesup_UiForm {

    protected $_filename = '';
    protected $_classname = '';
    protected $_methodname = '';
    protected $_data = array();

    public function setFilename($fileName) {
        $this->_filename = $fileName;
    }

    public function setClassname($className) {
        $this->_classname = $className;
    }

    public function setMethodname($methodName) {
        $this->_methodname = $methodName;
    }

    public function setData($data) {
        $data = unserialize($data);
        $this->_data = $data;
    }

    /**
     * Example parameter  query expected in controller action
     *      
     * @param integer $testint $config = array('label'=>'Int','min' => 0,'max' => 70,'description'=>'blah','default'=>54);
     * @param float $testfloat $config = array('label'=>'float','min' => 12,'max' => 80,'description'=>'glah','default'=>54.43);
     * @param string $teststring $config = array('label'=>'string','description'=>'zlah','default'=>'ddddd');
     * @param date $testdate $config = array('label'=>'date','description'=>'qlah','default'=>'2012-12-22');
     * @param array $testarray $config = array('label'=>'array','description'=>'vlah','default'=>'1', 'selection'=>array('e'=>'f','1'=>'g'));
     */
    public function init() {
        $this->setLabelMinWidth(140);
        $this->setTextFieldWidth(250);

        require_once($this->_filename);
        $r = new Zend_Reflection_Method($this->_classname, $this->_methodname);
        $docblock = $r->getDocblock();
        $parameter = array();
        foreach ($docblock->getTags('param') as $tag) {
            $type = $tag->getType();
            $name = $tag->getVariableName();
            $desc = $tag->getDescription();
            $parameter[] = substr($name, 1);
            switch (strtolower($type)) {
                case 'integer':
                    $element = $this->_makeIntElement($name, $desc);
                    $this->addElement($element);
                    break;
                case 'float':
                    $element = $this->_makeFloatElement($name, $desc);
                    $this->addElement($element);
                    break;
                case 'string':
                    $element = $this->_makeStringElement($name, $desc);
                    $this->addElement($element);
                    break;
                case 'date':
                    $element = $this->_makeDateElement($name, $desc);
                    $this->addElement($element);
                    break;
                case 'array':
                    $element = $this->_makeArrayElement($name, $desc);
                    $this->addElement($element);
                    break;
                default:
                    break;
            }
        }
        if (!$parameter) {
            throw new Exception('No Parameters given');
        }
        $hidden = new Yesup_Form_Element_UiHidden('list');
        $hidden->setValue(serialize($parameter));
        $this->addElement($hidden);
        $this->addSubmit('Submit');
        $this->setElementData();
    }

    private function _makeIntElement($name, $desc) {
        try {
            eval($desc);
        } catch (Exception $ex) {
            throw new Exception('Description for Integer not valid.$config options : label,default,description,min,max');
        }
        $element = new Yesup_Form_Element_UiText($name);
        $element = $this->_makeBasicElement($element, $config);
        if (isset($config['min'])) {
            $element->addValidator(new Yesup_Validate_NotLessThan($config['min']));
        }
        if (isset($config['max'])) {
            $element->addValidator(new Zend_Validate_LessThan($config['max']));
        }
        $element->addFilter(new Zend_Filter_Digits());
        return $element;
    }

    private function _makeBasicElement($element, $config) {
        if (isset($config['label'])) {
            $element->setLabel($config['label']);
        }
        if (isset($config['description'])) {
            $element->setDescription($config['description']);
        }
        if (isset($config['default'])) {
            $element->setValue($config['default']);
        }
        return $element;
    }

    private function _makeStringElement($name, $desc) {
        try {
            eval($desc);
        } catch (Exception $ex) {
            throw new Exception('Description for String not valid.$config options : label,default,description');
        }
        $element = new Yesup_Form_Element_UiText($name);
        $element = $this->_makeBasicElement($element, $config);
        $element->addFilter(new Zend_Filter_StringTrim());
        return $element;
    }

    private function _makeFloatElement($name, $desc) {
        try {
            eval($desc);
        } catch (Exception $ex) {
            throw new Exception('Description for Float not valid. $config options : label,default,description, min, max');
        }
        $element = new Yesup_Form_Element_UiText($name);
        $element = $this->_makeBasicElement($element, $config);
        if (isset($config['min'])) {
            $element->addValidator(new Yesup_Validate_NotLessThan($config['min']));
        }
        if (isset($config['max'])) {
            $element->addValidator(new Zend_Validate_LessThan($config['max']));
        }
        $element->addValidator('Float');
        return $element;
    }

    private function _makeDateElement($name, $desc) {
        try {
            eval($desc);
        } catch (Exception $ex) {
            throw new Exception('Description for Date not valid. $config options : label,default,description');
        }
        $element = new Yesup_Form_Element_UiDatePicker($name);
        $element = $this->_makeBasicElement($element, $config);
        if(!$element->getValue()){
            $element->setValue(Yesup_Date::now()->dbDateString());         
        }
        return $element;
    }

    private function _makeArrayElement($name, $desc) {
        try {
            eval($desc);
        } catch (Exception $ex) {
            throw new Exception('Description for Date not valid. $config options : label,default,description,selection');
        }
        $element = new Yesup_Form_Element_UiSelect($name);
        $element = $this->_makeBasicElement($element, $config);
        if (isset($config['selection'])) {
            $element->setMultiOptions($config['selection']);
        }
        return $element;
    }

    protected function setElementData() {
        if ($this->_data) {
            foreach ($this->_data as $key => $value) {
                $element = $this->getElement($key);
                $element->setValue($value);
            }
        }
    }

    public function addLastElement($element) {
        $this->removeElement('submit');
        $this->addElement($element);
        $this->addSubmit('submit');
    }

}

?>
