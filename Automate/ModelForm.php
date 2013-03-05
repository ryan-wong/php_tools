<?php

class Yesup_Automate_ModelForm extends Yesup_UiForm {

    protected $_listFilter = array('' => 'No Filter');
    protected $_listValidator = array('' => 'No Validator');
    protected $_listElement = array('' => 'No Element');

    public function listFilterDir($path) {
        $dir = opendir($path);
        while (false !== ($entry = readdir($dir))) {
            if ($entry != '.' && $entry != '..' && endswith($entry, '.php')) {
                $display = str_replace('.php', '', $entry);
                $entry = 'new Zend_Filter_' . str_replace('.php', '', $entry) . '()';

                $this->_listFilter[$entry] = $display;
            }
        }
    }

    public function listValidatorDir($path) {
        $dir = opendir($path);
        while (false !== ($entry = readdir($dir))) {
            if ($entry != '.' && $entry != '..' && endswith($entry, '.php')) {
                $display = str_replace('.php', '', $entry);
                $entry = 'new Zend_Validate_' . str_replace('.php', '', $entry) . '()';

                $this->_listValidator[$entry] = $display;
            }
        }
    }

    public function listElementDir($path) {
        $dir = opendir($path);
        while (false !== ($entry = readdir($dir))) {
            if ($entry != '.' && $entry != '..' && endswith($entry, '.php')) {
                $display = str_replace('.php', '', $entry);
                $entry = 'new Yesup_Form_Element_' . str_replace('.php', '', $entry);

                $this->_listElement[$entry] = $display;
            }
        }
    }

    public function getFilterNames() {

        $path = 'Zend/Filter';
        $pathname = ROOTDIR . '/library/' . $path;
        $this->listFilterDir($pathname);
        asort($this->_listFilter);
    }

    public function getValidatorName() {
        $path = 'Zend/Validate';
        $pathname = ROOTDIR . '/library/' . $path;
        $this->listValidatorDir($pathname);
        asort($this->_listValidator);
    }

    public function setElementOptions() {
        $path = 'Yesup/Form/Element/';
        $pathname = ROOTDIR . '/library/' . $path;
        $this->listElementDir($pathname);
        asort($this->_listElement);
    }

    public function init() {
        $this->getFilterNames();
        $this->getValidatorName();
        $this->setElementOptions();
        
        $this->setLabelMinWidth(80);
        $this->setTextFieldWidth(200);
        
        $param = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        
        $options = array(''=>'No Table Selected');
        foreach (Yesup_Automate_Model_Helper_CreateModel::getAllTable() as $table) {
            $options[$table] = ucfirst($table);
        }
        $name = Yesup_Automate_Elements::createSelect('name', $options);
        $name->setLabel('Model Name:');
        if (isset($param['name'])) {
            $name->setValue($param['name']);
        }
        $this->addElement($name);

        $js = <<<JS
$('#name').change(function(){        
            window.location = window.location.pathname+"?name="+$(this).val();
        });
JS;
        $this->attachOnloadJavascript($js);
        if (isset($param['name'])) {
            $model = new Yesup_Automate_Model_Helper_CreateForm($param['name']);
            $model->setFilter($this->_listFilter);
            $model->setValidator($this->_listValidator);
            $model->setFormElement($this->_listElement);
            $tables = $model->getTableMeta();            
            foreach ($tables['metadata'] as $key => $entry) {
                $column = $key;
                $show = new Yesup_Form_Element_UiCheckBox('show'.$column);
                $show->setLabel('Show ?');
                $show->setChecked(true);
                $text = Yesup_Automate_Elements::createText('label' . $column, $column);
                $text->setLabel('Label:');
                $type = new Yesup_Form_Element_PlainText('type' . $column);
                $type->setLabel('Type:');
                $type->setValue($entry['DATA_TYPE']);
                $element = Yesup_Automate_Elements::createSelect('element' . $column, $this->_listElement);
                $element->setLabel('Element Type:');
                $element->setValue($model->getDefaultElement($entry['DATA_TYPE']));
                $require = new Yesup_Form_Element_UiCheckBox('require' . $column);
                $require->setLabel('Required ?');
                $require->setChecked(true);
                $filter = Yesup_Automate_Elements::createSelect('filter' . $column, $this->_listFilter);
                $filter->setLabel('Filter:');
                $filter->setValue($model->getDefaultFilter($entry['DATA_TYPE']));
                $validator = Yesup_Automate_Elements::createSelect('validator' . $column, $this->_listValidator);
                $validator->setLabel('Validator:');
                $validator->setValue($model->getDefaultValidator($entry['DATA_TYPE']));
                $description = Yesup_Automate_Elements::createTextArea('description' . $column);
                $description->setLabel('Description:');
                $lineBreak = new Yesup_Form_Element_PlainText('a'.$column);
                $lineBreak->setValue('<hr/><hr/>');
                $this->addElement($show);
                $this->addElement($text);
                $this->addElement($type);
                $this->addElement($element);
                $this->addElement($require);
                $this->addElement($filter);
                $this->addElement($validator);
                $this->addElement($description);
                $this->addElement($lineBreak);
            }
        }
        $module = Yesup_Automate_Elements::createText('module');
        $this->addElement($module);
        $this->addSubmit('Generate Form');
    }

}