<?php

class Yesup_Automate_AnyForm extends Yesup_Automate_ModelForm {

    public function init() {
        $this->getFilterNames();
        $this->getValidatorName();
        $this->setElementOptions();

        $this->setLabelMinWidth(130);
        $this->setTextFieldWidth(200);

        $param = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $numOfField = Yesup_Automate_Elements::createNumber('number', 1);
        $numOfField->setLabel('Number of Form fields you want');

        $js = <<<JS
$('#number').change(function(){        
            window.location = window.location.pathname+"?number="+$(this).val();
        });
JS;
        $this->attachOnloadJavascript($js);
        $this->addElement($numOfField);
        if (isset($param['number']) && is_numeric($param['number'])) {
            $numOfField->setValue($param['number']);
            for ($i = 0; $i < $param['number']; $i++) {
                $name = Yesup_Automate_Elements::createText('name' . "field$i", "field_$i");
                $text = Yesup_Automate_Elements::createText('label' . "field$i", "field$i");
                $element = Yesup_Automate_Elements::createSelect('element' . "field$i", $this->_listElement);
                $require = new Yesup_Form_Element_UiCheckBox('require' . "field$i");
                $filter = Yesup_Automate_Elements::createSelect('filter' . "field$i", $this->_listFilter);
                $validator = Yesup_Automate_Elements::createSelect('validator' . "field$i", $this->_listValidator);
                $description = Yesup_Automate_Elements::createTextArea('description' . "field$i");
                $lineBreaks = new Yesup_Form_Element_PlainText('a' . "field$i");

                //labels
                $name->setLabel('Element name:');
                $text->setLabel('Label:');
                $element->setLabel('Element Type:');
                $require->setLabel('Required ?');
                $filter->setLabel('Filter:');
                $validator->setLabel('Validator:');
                $description->setLabel('Description:');
                //values           
                $element->setValue('new Yesup_Form_Element_UiText');
                $filter->setValue('');
                $validator->setValue('');                
                $lineBreaks->setValue('<hr/><hr/>');
                //add Element
                $this->addElement($name);
                $this->addElement($text);
                $this->addElement($element);
                $this->addElement($require);
                $this->addElement($filter);
                $this->addElement($validator);
                $this->addElement($description);
                $this->addElement($lineBreaks);
            }
            $lineBreak = new Yesup_Form_Element_PlainText('a');
            $filename = Yesup_Automate_Elements::createText('filename');
            $model = Yesup_Automate_Elements::createText('model_name');
            $lineBreak->setValue('<hr/><hr/>');

            $this->addElement($lineBreak);
            $this->addElement($filename);
            $this->addElement($model);
            $this->addSubmit('Generate Form');
        }
    }

}
