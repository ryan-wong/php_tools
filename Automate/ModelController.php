<?php

class Yesup_Automate_ModelController extends Yesup_UiForm {

    public function init() {
        $this->setLabelMinWidth(180);
        $this->setTextFieldWidth(200);
        $options = array('' => 'No Table Selected');
        foreach (Yesup_Automate_Model_Helper_CreateModel::getAllTable() as $table) {
            $options[$table] = ucfirst($table);
        }
        $name = Yesup_Automate_Elements::createSelect('name', $options);
        $name->setLabel('Model Name:');
        $this->addElement($name);

        $hr = new Yesup_Form_Element_PlainText('a');
        $hr->setValue('<hr/>');
        $this->addElement($hr);
        $add = new Yesup_Form_Element_UiCheckBox('add');
        $add->setLabel('Add Action:');
        $edit = new Yesup_Form_Element_UiCheckBox('edit');
        $edit->setLabel('Edit Action:');
        $list = new Yesup_Form_Element_UiCheckBox('list');
        $list->setLabel('List Action:');
        $searchForm = new Yesup_Form_Element_UiCheckBox('searchform');
        $searchForm->setLabel('Search Form in List:');
        $hr2 = new Yesup_Form_Element_PlainText('b');
        $hr2->setValue('<hr/>');


        $module = Yesup_Automate_Elements::createText('module');
        $resource = new Yesup_Form_Element_UiCheckBox('resource');
        $resource->setLabel('Set Controller to Resource:');

        $this->addElement($add);
        $this->addElement($edit);
        $this->addElement($list);
        $this->addElement($searchForm);
        $this->addElement($hr2);
        $this->addElement($module);
        $this->addElement($resource);
        $this->addSubmit('Generate Controller');
    }

}