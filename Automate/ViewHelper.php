<?php

class Yesup_Automate_ViewHelper extends Yesup_UiForm {

    public function init() {
        $this->setLabelMinWidth(140);
        $this->setTextFieldWidth(250);
        //Elements
        $folder = new Yesup_Form_Element_UiText('folder');
        $field = new Yesup_Form_Element_UiSelect('field');
        $name = new Yesup_Form_Element_UiText('name');
        //Labels
        $folder->setLabel('Folder Location:');
        $field->setLabel('Parameter Type:');
        $name->setLabel('Function Name:');
        //Required
        $folder->setRequired();
        $name->setRequired();
        //Description
        $folder->setDescription('App, Yesup ....');
        $field->setDescription('parameter you pass into view helper');
        $name->setDescription('view helper function name');
        //Filters
        //Validators
        $options = array(''=> 'any variable');
        foreach (Yesup_Automate_Model_Helper_CreateModel::getAllTable() as $table) {
            $options['Model_'.ucfirst($table)] = 'Model_'.ucfirst($table);
        }
        $folder->setValue('App');
        $field->setMultiOptions($options); 
        $field->setValue('');
        //Add Element
        $this->addElement($folder);
        $this->addElement($field);
        $this->addElement($name);
        $this->addSubmit('Add View Helper');
    }

}
