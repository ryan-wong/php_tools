<?php

class Yesup_Automate_ModelModel extends Yesup_UiForm {

    public function init() {
        $this->setLabelMinWidth(100);
        $this->setTextFieldWidth(100);
        $param = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        
        $options = array();
        foreach (Yesup_Automate_Model_Helper_CreateModel::getAllTable() as $table) {
            $options[$table] = ucfirst($table);
        }
        $name = Yesup_Automate_Elements::createSelect('name', $options);
        $name->setLabel('Model Name:');
        $this->addElement($name);        
        $this->addSubmit('Generate Model');
    }

}