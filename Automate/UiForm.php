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
class Yesup_Automate_UiForm extends Yesup_UiForm {
    
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
    public function createForm($model) {
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
            $this->_elementList = $this->createForm($this->_model);
            $submitlabel = 'Add ' . ucfirst($this->_model);
        } else {
            $this->_elementList = $this->createForm($this->_model);
            $tablename = get_class($this->_model);
            $modelname = substr($tablename,strpos($tablename, '_')+1);
            $submitlabel = 'Edit ' . $modelname;
        }
        if ($this->_elementList) {
            foreach ($this->_elementList as $element) {
                $this->addElement($element);
            }
            $this->addSubmit($submitlabel);
        }
    }

    /**
     * Given an form element, add it to the form at that particular order.
     * @param Zend_Form $element
     * @param int $order 
     */
    public function addElementWithOrder($element, $order = 0) {
        $element->setOrder($order);
        $this->addElement($element);
    }

    /** You get a list of the form elements which you can modify and then 
     * setFieldList() to save the changes. The structure of the array is:
     * [fieldname] = Zend Form Object.fieldname is the field's name from 
     * the database
     * @return Array Zend_Form_Element
     */
    public function getFieldList() {
        return $this->getElements();
    }

    /** Remove element by the field name
     *
     * @param String $name 
     */
    public function removeFieldByName($name) {
        $this->removeElement($name);
    }

    /*     * Remove elements by the field name
     *
     * @param Array $names 
     */

    public function removeFieldsByName($names = array()) {
        foreach ($names as $name) {
            $this->removeElement($name);
        }
    }

    /** Given list of Zend Elements, you can set the form 
     *
     * @param Array $list 
     */
    public function setFieldList($list) {
        $this->setElements($list);
    }

    
    /**
     *
     * @param string $name
     * @param Mixed $value 
     */
    public function setFieldHidden($name, $value = '') {
        $element = $this->getElement($name);
        $element->helper = 'uiFormXhtml';
        $element->setRequired(false);
        if (strlen($value) > 0) {
            $element->setValue($value);
        }
    }

    /**
     *
     * @param string $name
     * @param array $option 
     */
    public function setFieldSelectOption($name, $option = array()) {
        $element = $this->getElement($name);
        $element->setMultiOptions($option);
    }

    /**
     *
     * @param string $name
     * @param Mixed $value Int or String or float
     */
    public function setFieldValue($name, $value = '') {
        $element = $this->getElement($name);
        $element->setValue($value);
    }

    /**
     *
     * @param string $name
     * @param string $value 
     */
    public function setFieldLabel($name, $value = 'Label1') {
        $element = $this->getElement($name);
        $element->setLabel($value . " :");
    }

    /**
     *
     * @param string $name
     * @param string $value 
     */
    public function setFieldDescription($name, $value = '') {
        $element = $this->getElement($name);
        $element->setDescription($value);
    }

    /**
     *
     * @param string $name 
     */
    public function setNotRequired($name) {
        $element = $this->getElement($name);
        $element->setRequired(false);
    }

    public function addId() {
        if (!is_string($this->_model)) {
            $Id = Yesup_Automate_Elements::createHidden(ucfirst($this->_model) . '_Id', $this->_model->getId());
            $this->addElementWithOrder($Id, 0);
        }
    }

    public function fieldWanted($fields = array()) {
        $list = $this->getFieldList();
        $fields[] = 'submit';
        foreach ($list as $key => $value) {
            if (!in_array($key, $fields)) {
                $this->removeElement($key);
            }
        }
    }

}