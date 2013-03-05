<?php

class Yesup_Automate_ModelTable extends Yesup_UiForm {

    protected $_form = '';
    protected $_listHelper = array('' => 'No Helper');

    public function listDir($path) {
        $dir = opendir($path);
        while (false !== ($entry = readdir($dir))) {
            if ($entry != '.' && $entry != '..' && endswith($entry, '.php')) {
                $entry = lcfirst(str_replace('.php', '', $entry));
                $this->_listHelper[$entry] = ucfirst($entry);
            }
        }
    }

    public function getViewHelperNames() {
        $paths = $this->getView()->getHelperPaths();
        $pathNotuse = array();
        foreach ($paths as $value) {
            foreach ($value as $path) {
                if (is_dir($path)) {
                    $this->listDir($path);
                } else {
                    if (!in_array($path, $pathNotuse)) {
                        $pathname = ROOTDIR . '/library/' . $path;

                        if (is_dir($pathname)) {
                            $this->listDir($pathname);
                        }
                    }
                }
            }
        }
        asort($this->_listHelper);
    }

    public function init() {
        $this->getViewHelperNames();
        $this->setLabelMinWidth(80);
        $this->setTextFieldWidth(80);
        $param = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        //test
        $options = array('No Table Selected');
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
            $model = new Yesup_Automate_Model_Helper_CreateModel($param['name']);
            $form = new Yesup_UiTableForm();
            $form->setColumnsNumber(8);
            $form->setTextFieldWidth(80);
            $headerShow = new Yesup_Form_Element_PlainText('a');
            $headerShow->setValue('Show:');
            $headerField = new Yesup_Form_Element_PlainText('b');
            $headerField->setValue('Field:');
            $headerTitle = new Yesup_Form_Element_PlainText('h');
            $headerTitle->setValue('Column Title:');
            $headerDataPath = new Yesup_Form_Element_PlainText('c');
            $headerDataPath->setValue('DataPath:');
            $headerHelper = new Yesup_Form_Element_PlainText('d');
            $headerHelper->setValue('Helper:');
            $headerYesup = new Yesup_Form_Element_PlainText('e');
            $headerYesup->setValue('Custom:');
            $headerEmptyStr = new Yesup_Form_Element_PlainText('f');
            $headerEmptyStr->setValue('Empty String:');
            $headerMapping = new Yesup_Form_Element_PlainText('g');
            $headerMapping->setValue('Mapping:');
            $form->addElement($headerShow);
            $form->addElement($headerField);
            $form->addElement($headerTitle);
            $form->addElement($headerDataPath);
            $form->addElement($headerHelper);
            $form->addElement($headerYesup);
            $form->addElement($headerEmptyStr);
            $form->addElement($headerMapping);
            foreach ($model->getColumns() as $column) {
                $show = new Yesup_Form_Element_UiCheckBox('show' . $column);
                $form->addElement($show);
                $name = Yesup_Automate_Elements::createHidden('fname-' . $column, $column);
                $name->setLabel('');
                $form->addElement($name);
                $title = Yesup_Automate_Elements::createText('title' . $column, underscoreToSpace(($column)));
                $title->setLabel('');
                $form->addElement($title);
                $data = Yesup_Automate_Elements::createTextNotRequired('datapath-' . $column, lcfirst(field2name($column)));
                $data->setLabel('');
                $form->addElement($data);
                $help = Yesup_Automate_Elements::createSelect('help-' . $column, $this->_listHelper);
                $help->setLabel('');
                $help->setValue('');
                $form->addElement($help);
                $custom = Yesup_Automate_Elements::createTextNotRequired('custom-' . $column);
                $custom->setLabel('');
                $form->addElement($custom);
                $empty = Yesup_Automate_Elements::createTextNotRequired('empty-' . $column, '');
                $empty->setLabel('');
                $form->addElement($empty);
                $mapping = new Yesup_Form_Element_UiCheckBox('mapping' . $column);
                $form->addElement($mapping);
            }
            $moduleLabel = new Yesup_Form_Element_PlainText('m');
            $moduleLabel->setValue('Module:');
            $form->addElement($moduleLabel);
            $module = Yesup_Automate_Elements::createText('module');
            $module->setLabel('');
            $form->addElement($module);
            $searchFormLabel = new Yesup_Form_Element_PlainText('n');
            $searchFormLabel->setValue('Search Form:');
            $form->addElement($searchFormLabel);
            $searchForm = new Yesup_Form_Element_UiCheckBox('searchform');
            $searchForm->setLabel('');
            $form->addElement($searchForm);
            $hoverDetailLabel = new Yesup_Form_Element_PlainText('o');
            $hoverDetailLabel->setValue('Hover Detail:');
            $form->addElement($hoverDetailLabel);
            $hoverDetail = new Yesup_Form_Element_UiCheckBox('hoverDetail');
            $hoverDetail->setLabel('');
            $form->addElement($hoverDetail);

            $addButtonLabel = new Yesup_Form_Element_PlainText('p');
            $addButtonLabel->setValue('Add Button:');
            $form->addElement($addButtonLabel);
            $addButton = new Yesup_Form_Element_UiCheckBox('addbutton');
            $addButton->setLabel('');
            $form->addElement($addButton);
            $editButtonLabel = new Yesup_Form_Element_PlainText('q');
            $editButtonLabel->setValue('Edit Button:');
            $form->addElement($editButtonLabel);
            $editButton = new Yesup_Form_Element_UiCheckBox('editbutton');
            $editButton->setLabel('');
            $form->addElement($editButton);
            $this->_form = $form;
            $form->addSubmit('Generate Table');
        } else {
            
        }
    }

    public function render(Zend_View_Interface $view = null) {
        if ($view === null) {
            $view = $this->getView();
        }

        try {
            $onloadJs = $this->_getOnloadJavascript();
            if ($onloadJs) {
                $view->jQuery()->addOnload($onloadJs);
            }

            $customStyle = $this->_generateCustomStyle();
            if ($customStyle) {
                $view->headStyle()->appendStyle($customStyle);
            }

            $content = $this->_beforeRender();
            $content .= parent::render($view);
            $content .= $this->_form;
            return $content . '<div class="ui-helper-clearfix"></div>';
        } catch (Exception $ex) {
            error_log('Exception while render form: ' . $ex->getMessage());
            error_log('Trace: ' . $ex->getTraceAsString());
            return '';
        }
    }

}
