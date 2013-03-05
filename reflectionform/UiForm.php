<?php

class Yesup_UiForm extends ZendX_JQuery_Form {

    protected $_submit = null;
    protected $_submitLabel = null;
    protected $_labelMinWidth = null;
    protected $_textFieldWidth = null;
    protected $_onloadJavaScript = '';

    public function __construct($options = null) {
        if (is_array($options) && !empty($options['submit_label'])) {
            $this->_submitLabel = $options['submit_label'];
            unset($options['submit_label']);
        } else if (is_string($options)) {
            $this->_submitLabel = $options;
            $options = null;
        }
        $this->addPrefixPath('Yesup_Form_Decorator', 'Yesup/Form/Decorator', 'decorator')
                ->addPrefixPath('Yesup_Form_Element', 'Yesup/Form/Element', 'element')
                ->addElementPrefixPath('Yesup_Form_Decorator', 'Yesup/Form/Decorator', 'decorator')
                ->addDisplayGroupPrefixPath('Yesup_Form_Decorator', 'Yesup/Form/Decorator');
        $this->addHidden('form_timestamp', Yesup_Date::now()->addMinute(15)->dbDateTimeString());
        parent::__construct($options);
    }

    public function setLabelMinWidth($pixel) {
        if ($this->_labelMinWidth) {
            // no need to set if current minimal with bigger than new value
            if ($this->_labelMinWidth > $pixel) {
                return;
            }
        }
        $this->_labelMinWidth = $pixel;
    }

    public function setTextFieldWidth($pixel) {
        $this->_textFieldWidth = $pixel;
    }

    public function attachOnloadJavascript($js) {
        $this->_onloadJavaScript .= $js;
    }

    protected function _getOnloadJavascript() {
        return $this->_onloadJavaScript;
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
            return $content . '<div class="ui-helper-clearfix"></div>';
        } catch (Exception $ex) {
            error_log('Exception while render form: ' . $ex->getMessage());
            error_log('Trace: ' . $ex->getTraceAsString());
            return '';
        }
    }

    protected function _beforeRender() {
        return '';
    }

    protected function _generateCustomStyle() {
        if (!$this->_isWrappedInDL()) {
            return '';
        }

        $baseSelector = $this->_determineBaseSelector();

        $css = '';
        if ($this->_labelMinWidth) {
            $css .= $baseSelector . ' dt {min-width: ' . $this->_labelMinWidth . 'px !important;}' . "\n";
        }
        if ($this->_textFieldWidth) {
            $css .= $baseSelector . ' .textinput {width: ' . $this->_textFieldWidth . 'px !important;}' . "\n";
            $css .= $baseSelector . ' .textdisplay {padding-left: 4px; width: ' . ($this->_textFieldWidth - 4) . 'px !important;}' . "\n";
        }

        return $css;
    }

    protected function _isWrappedInDL() {
        $htmlTag = $this->getDecorator('HtmlTag');
        if (!$htmlTag) {
            return false;
        }
        $options = $htmlTag->getOptions();
        if (empty($options['tag']) || strtolower($options['tag']) != 'dl') {
            return false;
        }
        return true;
    }

    protected function _determineBaseSelector() {
        $id = $this->_detectIdValue();
        if ($id) {
            return '#' . $id;
        }
        $class = $this->_detectClassValue();
        if ($class) {
            return '.' . $class;
        }
        return 'form';
    }

    protected function _detectIdValue() {
        return $this->_detectValueByAttr('id');
    }

    protected function _detectClassValue() {
        return $this->_detectValueByAttr('class');
    }

    protected function _detectValueByAttr($attr) {
        $value = $this->getAttrib($attr);
        if ($value) {
            return $value;
        }
        $htmlTag = $this->getDecorator('HtmlTag');
        if ($htmlTag) {
            $value = $htmlTag->getOption($attr);
            if ($value) {
                return $value;
            }
        }
        return '';
    }

    /**
     * Return inputed data, not include with empty value and the submit button.
     */
    public function getInputData() {
        $result = array();
        foreach ($this->_elements as $element) {
            if ($element instanceof Zend_Form_Element_Submit) {
                // skip submit
                continue;
            }
            $value = $element->getValue();
            if (empty($value)) {
                // skip empty
                continue;
            }
            $name = $element->getName();
            $result[$name] = $value;
        }
        return $result;
    }

    public function hasElement($name) {
        return $this->getElement($name) != null;
    }

    public function addElement($element, $name = null, $options = null) {
        parent::addElement($element, $name, $options);
        if (is_object($element)) {
            if ($element instanceof Zend_Form_Element_Submit) {
                $this->_submit = $element;
            }
        }

        return $this;
    }

    public function appendElement(Zend_Form_Element $new) {
        $elements = $this->getElements();
        $newElements = array();
        foreach ($elements as $id => $element) {
            if ($element instanceof Zend_Form_Element_Submit && $new) {
                $newElements[$new->getName()] = $new;
                $new = null;
            }
            $newElements[$id] = $element;
        }
        $this->setElements($newElements);
        return $this;
    }

    public function addHidden($name, $value) {
        $element = new Yesup_Form_Element_UiHidden($name);
        $element->setValue($value);
        $this->addElement($element);
    }

    public function addSubmit($label) {
        $this->_submit = new Yesup_Form_Element_UiSubmit('submit');
        if ($this->_submitLabel) {
            $this->_submit->setLabel($this->_submitLabel);
        } else {
            $this->_submit->setLabel($label);
        }
        $this->addElement($this->_submit);
    }

    public function getSubmit() {
        return $this->_submit;
    }

    public function hasSubmit() {
        if ($this->_submit) {
            $submitName = $this->getSubmit()->getName();
            if (strtoupper($this->getMethod()) == 'POST') {
                return array_key_exists($submitName, $_POST);
            } else {
                return array_key_exists($submitName, $_GET);
            }
        } else {
            error_log('Form ' . get_class($this) . ' has not submit value, assume has submitted.');
            return true;
        }
    }

    public function isValid($data) {
        if (isset($data['form_timestamp'])) {
            $expiredTimeElement = $data['form_timestamp'];
            $expiredTimeDateObject = new Yesup_Date($expiredTimeElement, Zend_Date::ISO_8601);
            $today = Yesup_Date::now();
            if ($today->compare($expiredTimeDateObject) > 0) {
                $this->getView()->uiErrorBlock()->alert('This form has been open for more than 15 minutes. The data may have changed already.Please refresh the form and re-enter your data.');
                return false;
            }
        }
        return parent::isValid($data);
    }

}
