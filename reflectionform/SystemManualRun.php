<?php

class App_Form_SystemManualRun extends Yesup_UiReflectionForm {

    protected $_cron = null;
    protected $_admins = array();

    public function setCron($cron) {
        $this->_cron = $cron;
    }
    
    public function init() {
        $this->setLabelMinWidth(200);

        parent::init();
        //Elements
        $receivers = new Yesup_Form_Element_UiText('receivers');
        $reportTitle = new Yesup_Form_Element_PlainText('report-title');
        $parameterTitle = new Yesup_Form_Element_PlainText('parameter-title');
        $spacing = new Yesup_Form_Element_PlainText('space');
        //Labels
        $receivers->setLabel('Admin ID:');
        //Required
        //Description
        $receivers->setDescription('empty means no report sent. use comma to seperate admins');
        //Filters
        //Validators
        //Set Value
        if ($this->_cron) {
            $receivers->setValue($this->_cron->getReceivers());
        }
        $spacing->setValue('<br/>');
        $reportTitle->setLabel('Report Sent To:');        
        $reportTitle->getDecorator('Label')->setOptions(array('tag' => 'dt', 'class' => 'bold'));
        $reportTitle->setValue("<br/><br/>");
        $parameterTitle->setLabel('Cron Job Parameter(s):');
        $parameterTitle->getDecorator('Label')->setOptions(array('tag' => 'dt', 'class' => 'bold'));
        $parameterTitle->setValue("<br/><br/>");
        //Add Element
        $parameterTitle->setOrder(0);
        $this->addElement($parameterTitle);
        $this->addElement($spacing);
        $this->addElement($reportTitle);        
        $this->addLastElement($receivers);
    }

    public function getValue($name) {
        if ($name == 'admins') {
            return $this->_admins;
        }
        return parent::getValue($name);
    }

    public function isValid($data) {
        $result = parent::isValid($data);
        if ($result) {
            $adminStr = $this->getValue('receivers');
            $adminIds = explode(',', $adminStr);
            if ($adminStr) {
                foreach ($adminIds as $adminId) {
                    try {
                        $admin = new Model_Admin($adminId);
                        if ($admin->isActive()) {
                            $this->_admins[] = $admin;
                        }
                    } catch (Exception $ex) {
                        $result = false;
                        $this->receivers->setErrors(array('Sorry The one or more admin ID you entered is invalid.'));
                        break;
                    }
                }
            }
        }
        return $result;
    }

}
