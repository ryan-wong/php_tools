<?php

class Yesup_Automate_ModelDbTable extends Yesup_UiForm {

    public function init() {
        $this->setLabelMinWidth(80);
        $this->setTextFieldWidth(200);
        $options = array('' => 'No Table Selected');
        foreach (Yesup_Automate_Model_Helper_CreateModel::getAllTable() as $table) {
            $options[$table] = ucfirst($table);
        }
        $name = Yesup_Automate_Elements::createSelect('name', $options);
        $name->setLabel('Model Name:');        
        $this->addElement($name);
        $this->addSubmit('Generate DBTable Class');
    }

}