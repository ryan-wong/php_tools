<?php

class System_ScheduleController extends App_Controller_Action {

    public function listAction() {
        if (!$this->_getParam('inactive', false)) {
            $excludeInactive = true;
        } else {
            $excludeInactive = false;
        }
        $items = Model_Cron::getPagedCronItems($excludeInactive);

        $table = new App_Table_AdminCron('', $items, null, array('showInactive' => !$excludeInactive));

        $this->view->table = $table;
    }

    public function addAction() {
        $form = new App_Form_AdminCron();

        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $name = $form->getValue('name');
                $module = 'scheduler';
                $controller = 'job';
                $action = $form->getValue('actionname');
                $cron = Model_Cron::createNewCron($name, $module, $controller, $action);
                if ($cron) {
                    Model_LogAdminOperation::addAdminOpLog(null, 'Create Cron. Cron Id: ' . $cron->getId() . ', name: ' . $name . ', page: /' . $module . '/' . $controller . '/' . $action);
                    $this->flashInfo(
                            'Schedule ' . $name . 'has been created.', 'This schedule is in paused status, you might need to finish the configuration and start it.');
                    $items = Model_Cron::getPagedCronItems(true);
                    $pageNum = ceil($items->count() / 10);
                    $this->_redirect('/system/schedule/list/num/10?page=' . $pageNum);
                } else {
                    $this->view->uiErrorBlock()->alert('Failed to create this schedule.');
                }
            }
        }

        $this->view->form = $form;
    }

    /**
     * @param $type Model_Order
     */
    public function configureAction() {
        $cronId = $this->_getParam('id');
        if (!$cronId) {
            $this->flashAlert('Missing cron ID');
            $this->_redirect('/system/schedule/list');
            exit;
        }
        $cron = new Model_Cron($cronId);
        $form = new App_Form_SystemCronConfigure(array('cron' => $cron));
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $name = $form->getValue('name');
                $receivers = $form->getValue('receivers');
                $status = $form->getValue('status');
                $cron->setName($name);
                $cron->setReceivers($receivers);
                $cron->setStatus($status);
                if ($cron->saveChanges()) {
                    $this->flashInfo('Updated Cron Job');
                    $this->_redirect('/system/schedule/list');
                } else {
                    $this->view->uiErrorBlock('Failed to Update cron.');
                }
            }
        }
    }

    public function cronScheduleAction() {
        $cronId = $this->_getParam('id');
        if (!$cronId) {
            $this->flashAlert('Missing cron ID');
            $this->_redirect('/system/schedule/list');
            exit;
        }
        $cron = new Model_Cron($cronId);
        $form = new App_Form_SystemCronSchedule(array('cron' => $cron));
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $minute = $form->getValue('minute');
                $hour = $form->getValue('hour');
                $day = $form->getValue('day');
                $month = $form->getValue('month');
                $weekday = $form->getValue('weekday');
                $cron->setMinute($minute);
                $cron->setHour($hour);
                $cron->setDay($day);
                $cron->setMonth($month);
                $cron->setWeekday($weekday);
                if ($cron->saveChanges()) {
                    $this->flashInfo('Updated cron schedule');
                    $this->_redirect('/system/schedule/list');
                } else {
                    $this->view->uiErrorBlock('Failed to Update cron schedule.');
                }
            }
        }
    }

    public function cronParameterAction() {
        $cronId = $this->_getParam('id');
        if (!$cronId) {
            $this->flashAlert('Missing cron ID');
            $this->_redirect('/system/schedule/list');
            exit;
        }
        $cron = new Model_Cron($cronId);
        $module = $cron->getModule();
        $controller = $cron->getController();
        $filename = ucfirst($controller) . 'Controller';
        $action = $cron->getAction();
        $reflectionArray = array(
            'filename' => ROOTDIR . "/application/modules/$module/controllers/$filename.php",
            'classname' => ucfirst($module) . "_$filename",
            'methodname' => dashToCapital($action) . 'Action',
            'data' => $cron->getParameters()
        );
        //go check the reflectionform for example parameter query
        $form = new Yesup_UiReflectionForm($reflectionArray);
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $parameterList = unserialize($_POST['list']);
                $parameterToSave = array();
                foreach ($parameterList as $key) {
                    //filled in
                    if ($_POST[$key]) {
                        $parameterToSave[$key] = $_POST[$key];
                    }
                }
                $cron->setParameters(serialize($parameterToSave));
                if ($cron->saveChanges()) {
                    $this->flashInfo('Parameters Saved.');
                    $this->_redirect('/system/schedule/list');
                } else {
                    $this->view->uiErrorBlock('Failed to Update Parameters.');
                }
            }
        }
    }

    public function manualRunAction() {
        $cronId = $this->_getParam('id');
        if (!$cronId) {
            $this->flashAlert('Missing cron ID');
            $this->_redirect('/system/schedule/list');
            exit;
        }
        $cron = new Model_Cron($cronId);
        $module = $cron->getModule();
        $controller = $cron->getController();
        $filename = ucfirst($controller) . 'Controller';
        $action = $cron->getAction();
        $reflectionArray = array(
            'filename' => ROOTDIR . "/application/modules/$module/controllers/$filename.php",
            'classname' => ucfirst($module) . "_$filename",
            'methodname' => dashToCapital($action) . 'Action',
            'data' => $cron->getParameters(),
            'cron' => $cron
        );
        //go check the reflectionform for example parameter query
        $form = new App_Form_SystemManualRun($reflectionArray);
        
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $parameterList = unserialize($_POST['list']);
                $parameterToSave = array();
                foreach ($parameterList as $key) {
                    //filled in
                    if ($_POST[$key]) {
                        $parameterToSave[$key] = $_POST[$key];
                    }
                }
                $cron->setParameters(serialize($parameterToSave));
                $cron->setReceiverAdmin($form->getValue('admins'));                                
                try {
                    $cron->run();
                    $this->flashInfo('Report Generated. Check your Message box. <a href="/admin/message/list">click here</a>');
                    $this->_redirect('/system/schedule/list');
                } catch (Exception $ex) {
                    $this->view->uiErrorBlock('Sorry your report was not generated.');
                }
            }
        }
    }

}
