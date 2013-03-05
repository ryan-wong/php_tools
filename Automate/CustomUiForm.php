<?php

/**
 * This class help you generate an automatic form based on the database structure.
 * The order form the database determines the ordering of the form.
 * To use this Form send in an array('model' => string or model object).
 * If String create an add form for you. If model, create a filled in form for
 * you. If you don't want all the fields you can subclass Yesup_Ui_Form.
 * in the init() function, type parent::init(); and using this class method
 * modify the elements in the table;
 */
class Yesup_Automate_CustomUiForm extends Yesup_UiForm {

    const MODE_CREATE = 'create';
    const MODE_EDIT = 'edit';

    private $_tableMapping = array(
        'schema' => 'TABLE_SCHEMA',
        'table' => 'TABLE_NAME',
        'column' => 'COLUMN_NAME',
        'ordinal' => 'ORDINAL_POSITION',
        'default' => 'COLUMN_DEFAULT',
        'null' => 'IS_NULLABLE',
        'type' => 'DATA_TYPE',
        'max_length' => 'CHARACTER_MAXIMUM_LENGTH',
        'octet_length' => 'CHARACTER_OCTET_LENGTH',
        'precision' => 'NUMERIC_PRECISION',
        'scale' => 'NUMERIC_SCALE',
        'character_set_name' => 'CHARACTER_SET_NAME',
        'collation' => 'COLLATION_NAME',
        'column_type' => 'COLUMN_TYPE',
        'key_type' => 'COLUMN_KEY',
        'extra' => 'EXTRA',
        'privileges' => 'PRIVILEGES',
        'comment' => 'COLUMN_COMMENT'
    );
    protected $_model = null;
    public $_elementList = array();

    public function __construct($options = null) {
        if(is_array($options) && isset($options['model'])) {
            $this->_model = $options['model'];
        }

        parent::__construct($options);
    }

    public function setModel($model) {
        $this->_model = $model;
    }

    public function getFormMode() {
        if (is_object($this->_model)) {
            return Yesup_Automate_UiForm::MODE_EDIT;
        } else {
            return Yesup_Automate_UiForm::MODE_CREATE;
        }
    }

    /**
     *
     * @param Model_Object $model 
     * @param boolean $exist True if model record exist
     * @return array of Zend_Form_Element
     */
    public function createFormList($model) {
        if (is_string($model)) {
            $tablename = 'Model_DbTable_' . ucfirst($model);
            $table = new $tablename();
            $schema = $table->info();
            $exist = false;
        } else {
            //Database Table Schema
            $schema = $model->getSchema();
            $exist = true;
        }
        //hold list of Zend_Form_Elements
        $formElements = array();

        foreach ($schema['metadata'] as $entry) {
            //@var $entry array of $_tableMapping
            $columnName = $entry[$this->_tableMapping['column']];
            //don't put id in a form
            if ($columnName == 'id') {
                continue;
            }

            $method = 'get' . field2name($columnName);
            $type = $entry[$this->_tableMapping['type']];
            // According to type of Data Column, make the correct
            // Zend_Form_Element and append to list
            switch ($type) {
                case 'int':
                    if ($exist) {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createInt($columnName, $model->$method());
                    } else {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createInt($columnName);
                    }
                    break;
                case 'varchar':
                    if ($exist) {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createText($columnName, $model->$method());
                        
                    } else {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createText($columnName);
                    }
                    break;
                case 'text':
                    if ($exist) {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createTextArea($columnName, $model->$method());
                    } else {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createTextArea($columnName);
                    }break;
                case 'float':
                    if ($exist) {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createFloat($columnName, round($model->$method(), 2));
                    } else {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createFloat($columnName);
                    }
                    break;
                //skipped this becuase time stamp values don't bcome form elements
                case 'timestamp':
                    if ($exist) {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createDate($columnName, $model->$method());
                    } else {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createDate($columnName);
                    }
                case 'date':
                    if ($exist) {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createDate($columnName, $model->$method());
                    } else {
                        $formElements[$columnName] =
                                Yesup_Automate_Elements::createDate($columnName);
                    }
                    break;
                default:
                    if (str_start_with($type, 'enum')) {
                        //enum has form of
                        //enum('x','y','z') want to parse this string
                        $enumStr = substr($type, strpos($type, '(') + 1);
                        $enumStr = substr($enumStr, 0, strlen($enumStr) - 1);
                        $option = array();
                        foreach (explode(',', $enumStr) as $row) {
                            //current form of $row  is 'x' so remove quote
                            $rowNoQuote = str_replace('\'', '', $row);
                            $option[$rowNoQuote] = field2name($rowNoQuote);
                        }
                        if ($exist) {
                            $formElements[$columnName] =
                                    Yesup_Automate_Elements::createSelect($columnName, $option, $model->$method());
                        } else {
                            $formElements[$columnName] =
                                    Yesup_Automate_Elements::createSelect($columnName, $option);
                        }
                    }
            }
        }
        //add Submit to form elements
        $formElements['submit'] = new Yesup_Form_Element_UiSubmit('submit');
        
        if ($exist) {
            $tablename = get_class($this->_model);
            $modelname = substr($tablename, strpos($tablename, '_') + 1);
            $submitlabel = 'Update ' . $modelname;
            $formElements['submit']->setLabel($submitlabel);
        } else {
            $formElements['submit']->setLabel('Add ' . ucfirst($model));
        }
        return $formElements;
    }

    /**
     * When inheriting this class, just put parent::init(); followed by any 
     * modifications you want ot the elements
     */
    public function init() {
        $this->setLabelMinWidth(140);
        $this->setTextFieldWidth(250);
        if (is_string($this->_model)) {
            $this->_elementList = $this->createFormList($this->_model);
        } else {
            $this->_elementList = $this->createFormList($this->_model);
        }
    }

    public function getElementByName($name) {
        if (array_key_exists($name, $this->_elementList)) {
            return $this->_elementList[$name];
        }
        return null;
    }

    public function setElementByName($name, $element) {
        if (is_string($name) && ($element instanceof Zend_Form_Element)) {
            $this->_elementList[$name] = $element;
            return true;
        }
        return false;
    }

    public function getElementNames() {
        $names = array();
        foreach ($this->_elementList as $element) {
            /* @var $element Zend_Form_Element */
            $names[] = $element->getName();
        }
        return $names;
    }

    public function &__call($func, $params) {
        if (str_start_with($func, 'element')) {
            $element = name2field(substr($func, 7));
            if (array_key_exists($element, $this->_elementList)) {
                return $this->_elementList[$element];
            }
        } else {
            throw new Yesup_Orm_Exception_Function('Call to undefine method: ' . $func);
        }
    }

    public function createForm($elements = array()) {
        foreach ($elements as $elementName) {
            if (array_key_exists($elementName, $this->_elementList)) {                
                $this->addElement($this->_elementList[$elementName]);
            }
        }
        $this->addElement($this->_elementList['submit']);
    }

}