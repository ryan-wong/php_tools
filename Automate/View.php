<?php

class Yesup_Automate_View extends Yesup_UiForm {

    protected $_viewHelper = array(
        'Default' => 'App_View_Helper_',
        'Zend' => 'Zend_View_Helper_',
        'ZendX' => 'ZendX_JQuery_View_Helper_',
        'Yesup' => 'Yesup_View_Helper_',
    );
    protected $_listHelper = array('' => 'No Helper');

    public function listDir($path) {
        $dir = opendir($path);
        while (false !== ($entry = readdir($dir))) {
            if ($entry != '.' && $entry != '..' && endswith($entry, '.php')) {
                $entry = lcfirst(str_replace('.php', '', $entry));
                $method = $this->findParameter($path, $entry);
                if ($method) {
                    $this->_listHelper[$method] = ucfirst($entry);
                }
            }
        }
    }

    public function findParameter($path, $method) {
        $className = $this->_viewHelper['Default'];
        foreach ($this->_viewHelper as $helper => $value) {
            if (strstr($path, $helper)) {
                $className = $value;
            }
        }
        try {
            $methodName = ucfirst($method);
            require_once($path . $methodName . '.php');
            $r = new ReflectionMethod($className . $methodName, $method);
            $params = $r->getParameters();
            $parameters = 'echo $this->' . $method . '( ';
            foreach ($params as $param) {
                $parameters .= '$' . $param->getName() . ',';
            }
            $parameters = substr($parameters, 0, -1);
            $parameters.= ');';
            return $parameters;
        } catch (Exception $ex) {
            return false;
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
        $this->setLabelMinWidth(150);


        $help = Yesup_Automate_Elements::createSelect('help-', $this->_listHelper);
        $help->setLabel('View Helper(s):');
        $help->setValue('');
        $models = array();
        foreach (Yesup_Automate_Model_Helper_CreateModel::getAllTable() as $table) {
            $models['Model_' . ucfirst($table)] = ucfirst($table);
        }
        $model = Yesup_Automate_Elements::createSelect('model', $models);
        $model->setLabel('Model:');
        
        $filename = new Yesup_Form_Element_UiText('filename');
        $text = new Yesup_Form_Element_UiTextarea('text');
        
        $text->setLabel('Text:');
        $text->setOptions(array('cols' => '100', 'rows' => '100'));
        $filename->setLabel('Filename:');
        $filename->setRequired();
        
        $this->addElement($help);
        $this->addElement($model);
        $this->addElement($filename);
        $this->addElement($text);
        $js = <<<JS
      
            $(document).delegate('textarea', 'keydown', function(e) { 
                var keyCode = e.keyCode || e.which; 

                if (keyCode == 9) { 
                    e.preventDefault(); 
                    var start = $(this).get(0).selectionStart;
                    var end = $(this).get(0).selectionEnd;

                    // set textarea value to: text before caret + tab + text after caret
                    $(this).val($(this).val().substring(0, start)
                        + "\t"
                        + $(this).val().substring(end));

                    // put caret at right position again
                    $(this).get(0).selectionStart = 
                        $(this).get(0).selectionEnd = start + 1;
                } 
            });
            $('#help').change(function(){        
            var val = $(this).val();  
            $('#text').val($('#text').val() + "\\n" + val);
        });    
            $('#model').change(function(){        
            var val = $(this).val();  
            $('#text').val($('#text').val() + "\\n" + val);
        });
    
JS;
        $this->attachOnloadJavascript($js);
        $this->addSubmit('Generate View Page');
    }

}
