<?php

class Yesup_Automate_Controller extends Zend_Controller_Action {

    public function init() {
        $front = Zend_Controller_Front::getInstance();
        $param = $front->getRequest()->getParams();
        $bootstrap = $front->getParam("bootstrap");
        $environment = $bootstrap->getEnvironment();
        $module = $param['module'];
        if ($environment != 'development') {
            $this->_redirect("/$module/index/index");
            exit;
        }
    }

    public function indexAction() {
        
    }

    public function dbtableAction() {

        $form = new Yesup_Automate_ModelDbTable();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $model = new Yesup_Automate_Model_Helper_CreateDbTable($form->getValue('name'));
                $model->serveString();
            }
        }
    }

    public function controllerAction() {
        $form = new Yesup_Automate_ModelController();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {
                $model = new Yesup_Automate_Model_Helper_CreateController($form->getValue('name'));
                $model->generateController($_POST);
            }
        }
    }

    public function modelAction() {
        $form = new Yesup_Automate_ModelModel();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $model = new Yesup_Automate_Model_Helper_CreateModel($form->getValue('name'));
                $model->serveString();
            }
        }
    }

    public function listAction() {
        $form = new Yesup_Automate_ModelTable();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $name = $this->_getParam('name');
                if ($name) {
                    $createTable = new Yesup_Automate_Model_Helper_CreateTable($name);
                    $createTable->generateTable($_POST);
                    $createTable->serveString();
                }
            }
        }
    }

    public function formAction() {
        $form = new Yesup_Automate_ModelForm();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {

                $name = $this->_getParam('name');
                if ($name) {
                    $generateForm = new Yesup_Automate_Model_Helper_CreateForm($name);
                    $generateForm->generateForm($_POST);
                    $generateForm->serveString();
                }
            }
        }
    }

    public function viewListAction() {
        $model = new Yesup_Automate_Model_Helper_CreateController('');
        $option = $this->_getParam('list');
        if ($option) {
            if ($option == '1') {
                $model->serveString('list.phtml', $model->_methListPhtml());
            }
            if ($option == '2') {
                $model->serveString('add.phtml', $model->_methAddPhtml());
            }
            if ($option == '3') {
                $model->serveString('edit.phtml', $model->_methEditPhtml());
            }
        }
    }

    public function anyFormAction() {
        $form = new Yesup_Automate_AnyForm();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {                
                  $generateForm = new Yesup_Automate_Model_Helper_CreateAnyForm();
                    $generateForm->generateForm($_POST);
                    $generateForm->serveString();
            }
        }
    }
 public function anyTableAction() {
        $form = new Yesup_Automate_AnyTable();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValid($_POST)) {          
                //var_dump($_POST);
                  $generateTable = new Yesup_Automate_Model_Helper_CreateAnyTable();
                    $generateTable->generateTable($_POST);
                    $generateTable->serveString();
            }
        }
    }
}

