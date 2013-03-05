<?php

class Yesup_Automate_Elements {

    public static function createText($name = 'text1', $value = '', $description = '') {
        $text = new Yesup_Form_Element_UiText($name);
        $text->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $text->addFilter('StringTrim');
        $text->setDescription($description);
        $text->setRequired();
        $text->setValue($value);
        return $text;
    }

    public static function createTextNotRequired($name = 'text1', $value = '', $description = '') {
        $text = new Yesup_Form_Element_UiText($name);
        $text->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $text->addFilter('StringTrim');
        $text->setDescription($description);
        $text->setValue($value);
        return $text;
    }

    public static function createFloat($name = 'float1', $value = '0', $description = '') {
        $float = new Yesup_Form_Element_UiText($name);
        $float->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $float->setDescription($description);
        $float->addValidator('Float');
        $float->addValidator(new Yesup_Validate_NotLessThan(0));
        $float->setRequired();
        $float->setValue($value);
        return $float;
    }

    public static function createFloatNotRequired($name = 'float1', $value = '0', $description = '') {
        $float = new Yesup_Form_Element_UiText($name);
        $float->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $float->setDescription($description);
        $float->addValidator('Float');
        $float->addValidator(new Yesup_Validate_NotLessThan(0));
        $float->setValue($value);
        return $float;
    }

    public static function createInt($name = 'int1', $value = '0', $description = '') {
        $int = new Yesup_Form_Element_UiText($name);
        $int->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $int->setDescription($description);
        // $int->addFilter('Digits');
        $int->addValidator(new Zend_Validate_Int());
        $int->addValidator(new Yesup_Validate_NotLessThan(0));
        $int->setRequired();
        $int->setValue($value);
        return $int;
    }

    public static function createIntNotRequired($name = 'int1', $value = '0', $description = '') {
        $int = new Yesup_Form_Element_UiText($name);
        $int->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $int->setDescription($description);
        $int->addFilter('Digits');
        $int->addValidator('Int');
        $int->addValidator(new Yesup_Validate_NotLessThan(0));
        $int->setValue($value);
        return $int;
    }

    public static function createNumber($name = 'number1', $value = '0', $description = '') {
        $number = new Yesup_Form_Element_UiNumber($name);
        $number->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $number->setRequired();
        $number->setDescription($description);
        $number->addFilter('Digits');
        $number->addValidator(new Zend_Validate_GreaterThan(-0.0001));
        $number->setValue($value);
        return $number;
    }

    public static function createHidden($name = 'hidden1', $value = '', $description = '') {
        $hidden = new Yesup_Form_Element_UiXhtml($name);
        $hidden->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $hidden->setDescription($description);
        $hidden->setText($value);
        return $hidden;
    }

    public static function createDate($name = 'Date1', $value = null) {
        $datepicker = new Yesup_Form_Element_UiDatePicker($name);
        $datepicker->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $datepicker->setRequired();
        $datepicker->addValidator(new Zend_Validate_Date(array('format' => 'yyyy-MM-dd')));
        if ($value) {
            $date = new Zend_Date($value, Zend_Date::ISO_8601);
            $datepicker->setValue($date->toString('yyyy-MM-dd'));
        } else {
            $datepicker->setValue(Zend_Date::now()->toString('yyyy-MM-dd'));
        }
        return $datepicker;
    }

    public static function createDateNotRequired($name = 'Date1', $value = null) {
        $datepicker = new Yesup_Form_Element_UiDatePicker($name);
        $datepicker->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');        
        $datepicker->addValidator(new Zend_Validate_Date(array('format' => 'yyyy-MM-dd')));
        if ($value) {
            $date = new Zend_Date($value, Zend_Date::ISO_8601);
            $datepicker->setValue($date->toString('yyyy-MM-dd'));
        } else {
            $datepicker->setValue(Zend_Date::now()->toString('yyyy-MM-dd'));
        }
        return $datepicker;
    }

    public static function createSelect($name = 'Select1', $options = array(), $value = null) {
        $select = new Yesup_Form_Element_UiSelect($name);
        $select->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        if ($value) {
            $select->setValue($value);
        }
        $select->setMultiOptions($options);
        return $select;
    }

    public static function createTextArea($name = 'textarea1', $value = '', $description = '') {
        $textarea = new Yesup_Form_Element_UiTextarea($name);
        $textarea->setLabel(Yesup_Automate_Elements::underscoreToSpace($name) . ' : ');
        $textarea->setDescription($description);
        $textarea->addFilter('StringTrim');
        $textarea->setValue($value);
        return $textarea;
    }

    public static function underscoreToSpace($field) {
        return str_replace(
                        array('_a', '_b', '_c', '_d', '_e', '_f', '_g', '_h', '_i', '_j', '_k', '_l', '_m', '_n', '_o', '_p', '_q', '_r', '_s', '_t', '_u', '_v', '_w', '_x', '_y', '_z'), array(' A', ' B', ' C', ' D', ' E', ' F', ' G', ' H', ' I', ' J', ' K', ' L', ' M', ' N', ' O', ' P', ' Q', ' R', ' S', ' T', ' U', ' V', ' W', ' X', ' Y', ' Z'), ucfirst($field)
        );
    }

}