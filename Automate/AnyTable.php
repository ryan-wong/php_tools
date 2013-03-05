<?php

class Yesup_Automate_AnyTable extends Yesup_Automate_ModelTable {

    public function init() {
        $this->getViewHelperNames();
        $form = new Yesup_UiTableForm();
        $form->setColumnsNumber(5);
        $form->setLabelMinWidth(40);
        $form->setTextFieldWidth(40);
        $param = Zend_Controller_Front::getInstance()->getRequest()->getParams();

        $numOfField = Yesup_Automate_Elements::createNumber('number', 1);
        $numOfButton = Yesup_Automate_Elements::createNumber('button', 1);
        $numOfOperation = Yesup_Automate_Elements::createNumber('operation', 1);
        $a = new Yesup_Form_Element_PlainText('a');
        $b = new Yesup_Form_Element_PlainText('b');



//label
        $numOfField->setLabel('# of Table fields:');
        $numOfButton->setLabel('# of Button:');
        $numOfOperation->setLabel('# of Operation:');
//validator
        $numOfField->addValidator(new Zend_Validate_GreaterThan(0));
        $numOfOperation->addValidator(new Zend_Validate_GreaterThan(-1));
        $numOfButton->addValidator(new Zend_Validate_GreaterThan(-1));
//value
        $numOfField->setValue(1);
        $numOfButton->setValue(0);
        $numOfOperation->setValue(0);
        $a->setValue('');
        $b->setValue('');



        $form->addElement($numOfField);
        $form->addElement($numOfButton);
        $form->addElement($numOfOperation);
        $form->addElement($a);
        $form->addElement($b);



        $js = <<<JS
$('#number').change(function(){        
    var number = '';
    var button = $('#button').val();
    var operation = $('#operation').val();        
    window.location = window.location.pathname+"?number="+$(this).val() +  "&button="+button+"&operation="+operation;
        });
$('#button').change(function(){        
    var number = $('#number').val();
    var button = '';
    var operation = $('#operation').val();        
    window.location = window.location.pathname+"?number="+number +  "&button="+$(this).val()+"&operation="+operation;
        });
$('#operation').change(function(){        
    var number = $('#number').val();
    var button =  $('#button').val();
    var operation = '';        
    window.location = window.location.pathname+"?number="+number +  "&button="+button+"&operation="+$(this).val();
        });
  $('#icon').change(function(){
    var val = $(this).val();  
    $('#showicon-element').html('<span class="ui-icon ' + val +'"></span>');
});      
    
JS;
        $this->attachOnloadJavascript($js);
        if (isset($param['number']) && isset($param['button']) && isset($param['operation'])) {
            $numOfField->setValue($param['number']);
            $numOfButton->setValue($param['button']);
            $numOfOperation->setValue($param['operation']);
//button
            for ($i = 0; $i < $param['button']; $i++) {
                $button = Yesup_Automate_Elements::createText("buttonurl$i");
                $buttonName = Yesup_Automate_Elements::createText("buttonname$i", 'Add');
                $select = new Yesup_Form_Element_UiSelect("buttonicon$i");
                $icon = new Yesup_Form_Element_PlainText("buttonshowicon$i");
                $a = new Yesup_Form_Element_PlainText("a$i");

                $select->setMultiOptions($this->icons);

                $button->setLabel('Button Url:');
                $buttonName->setLabel('Name:');
                $select->setLabel('icon:');
                $a->setValue('&nbsp;');

                $select->setValue('');
                $icon->setValue('<span class="ui-icon ui-icon-trash"></span>');
                $form->addElement($button);
                $form->addElement($buttonName);
                $form->addElement($select);
                $form->addElement($icon);
                $form->addElement($a);

                $js1 = <<<JS
                 $("#buttonicon$i").change(function(){
                    var val = $(this).val();  
                    $("#buttonshowicon$i-element").html('<span class="ui-icon ' + val +'"></span>');
                }); 
JS;
                $this->attachOnloadJavascript($js1);
            }

            $headerTitle = new Yesup_Form_Element_PlainText('titleheader');
            $headerTitle->setValue('Column Title:');
            $headerDataPath = new Yesup_Form_Element_PlainText('datapathheader');
            $headerDataPath->setValue('DataPath:');
            $headerHelper = new Yesup_Form_Element_PlainText('helperheader');
            $headerHelper->setValue('Helper:');
            $headerCustom = new Yesup_Form_Element_PlainText('customheader');
            $headerCustom->setValue('Custom:');
            $headerEmptyStr = new Yesup_Form_Element_PlainText('emptyheader');
            $headerEmptyStr->setValue('Empty String:');

            $form->addElement($headerTitle, null, array('style' => 'color:red'));
            $form->addElement($headerDataPath);
            $form->addElement($headerHelper);
            $form->addElement($headerCustom);
            $form->addElement($headerEmptyStr);
            for ($i = 0; $i < $param['number']; $i++) {
                $title = Yesup_Automate_Elements::createText("title$i", "Field");
                $data = Yesup_Automate_Elements::createTextNotRequired("datapath$i", "");
                $help = Yesup_Automate_Elements::createSelect("help$i", $this->_listHelper);
                $custom = Yesup_Automate_Elements::createTextNotRequired("custom$i");
                $empty = Yesup_Automate_Elements::createTextNotRequired("empty$i", '');

                $title->setLabel('');
                $data->setLabel('');
                $help->setLabel('');
                $custom->setLabel('');
                $empty->setLabel('');

                $help->setValue('');
                $form->addElement($title);
                $form->addElement($data);
                $form->addElement($help);
                $form->addElement($custom);
                $form->addElement($empty);
            }

//operation
            for ($i = 0; $i < $param['operation']; $i++) {
                $operation = Yesup_Automate_Elements::createText("operationurl$i");
                $operationName = Yesup_Automate_Elements::createText("operationname$i", 'Edit');
                $select = new Yesup_Form_Element_UiSelect("operationicon$i");
                $icon = new Yesup_Form_Element_PlainText("operationshowicon$i");
                $a = new Yesup_Form_Element_PlainText("aa$i");

                $select->setMultiOptions($this->icons);
                $operationName->setLabel('Name:');
                $operation->setLabel('Operation Url:');
                $select->setLabel('icon:');
                $a->setValue('&nbsp;');

                $select->setValue('');
                $icon->setValue('<span class="ui-icon ui-icon-trash"></span>');
                $form->addElement($operation);
                $form->addElement($operationName);
                $form->addElement($select);
                $form->addElement($icon);
                $form->addElement($a);

                $js1 = <<<JS
                 $("#operationicon$i").change(function(){
                var val = $(this) . val();
                $("#operationshowicon$i-element") . html('<span class="ui-icon ' + val + '"></span>');
                });
JS;
                $this->attachOnloadJavascript($js1);
            }
        }
        $fileNameLabel = new Yesup_Form_Element_PlainText('m');
        $fileName = Yesup_Automate_Elements::createText('filename');
        $fileName->setLabel('');
        $fileNameLabel->setValue('File Name:');
        $form->addElement($fileNameLabel);
        $form->addElement($fileName);
        $a = new Yesup_Form_Element_PlainText("zai");
        $b = new Yesup_Form_Element_PlainText("zbi");
        $c = new Yesup_Form_Element_PlainText("zci");
        $form->addElement($a);
        $form->addElement($b);
        $form->addElement($c);
        $searchFormLabel = new Yesup_Form_Element_PlainText('n');
        $searchFormLabel->setValue('Search Form:');
        $form->addElement($searchFormLabel);
        $searchForm = new Yesup_Form_Element_UiCheckBox('searchform');
        $searchForm->setLabel('');
        $form->addElement($searchForm);
        $b = new Yesup_Form_Element_PlainText("bbcc");
        $b->setValue('&nbsp;');
        $form->addElement($b);
        $cy = new Yesup_Form_Element_PlainText("yzci");
        $form->addElement($cy);
        $cz = new Yesup_Form_Element_PlainText("yzci");
        $form->addElement($cz);
        $cx = new Yesup_Form_Element_PlainText("yxzci");
        $form->addElement($cx);
        $hoverDetailLabel = new Yesup_Form_Element_PlainText('o');
        $hoverDetailLabel->setValue('Hover Detail:');
        $form->addElement($hoverDetailLabel);
        $hoverDetail = new Yesup_Form_Element_UiCheckBox('hoverDetail');
        $hoverDetail->setLabel('');
        $form->addElement($hoverDetail);
        $form->addSubmit('Generate Table');
        $this->_form = $form;
    }

    public $icons = array(
        '' => 'No Icon',
        "ui-icon-carat-1-n" => "carat-1-n",
        "ui-icon-carat-1-ne" => "carat-1-ne",
        "ui-icon-carat-1-e" => "carat-1-e",
        "ui-icon-carat-1-se" => "carat-1-se",
        "ui-icon-carat-1-s" => "carat-1-s",
        "ui-icon-carat-1-sw" => "carat-1-sw",
        "ui-icon-carat-1-w" => "carat-1-w",
        "ui-icon-carat-1-nw" => "carat-1-nw",
        "ui-icon-carat-2-n-s" => "carat-2-n-s",
        "ui-icon-carat-2-e-w" => "carat-2-e-w",
        "ui-icon-triangle-1-n" => "triangle-1-n",
        "ui-icon-triangle-1-ne" => "triangle-1-ne",
        "ui-icon-triangle-1-e" => "triangle-1-e",
        "ui-icon-triangle-1-se" => "triangle-1-se",
        "ui-icon-triangle-1-s" => "triangle-1-s",
        "ui-icon-triangle-1-sw" => "triangle-1-sw",
        "ui-icon-triangle-1-w" => "triangle-1-w",
        "ui-icon-triangle-1-nw" => "triangle-1-nw",
        "ui-icon-triangle-2-n-s" => "triangle-2-n-s",
        "ui-icon-triangle-2-e-w" => "triangle-2-e-w",
        "ui-icon-arrow-1-n" => "arrow-1-n",
        "ui-icon-arrow-1-ne" => "arrow-1-ne",
        "ui-icon-arrow-1-e" => "arrow-1-e",
        "ui-icon-arrow-1-se" => "arrow-1-se",
        "ui-icon-arrow-1-s" => "arrow-1-s",
        "ui-icon-arrow-1-sw" => "arrow-1-sw",
        "ui-icon-arrow-1-w" => "arrow-1-w",
        "ui-icon-arrow-1-nw" => "arrow-1-nw",
        "ui-icon-arrow-2-n-s" => "arrow-2-n-s",
        "ui-icon-arrow-2-ne-sw" => "arrow-2-ne-sw",
        "ui-icon-arrow-2-e-w" => "arrow-2-e-w",
        "ui-icon-arrow-2-se-nw" => "arrow-2-se-nw",
        "ui-icon-arrowstop-1-n" => "arrowstop-1-n",
        "ui-icon-arrowstop-1-e" => "arrowstop-1-e",
        "ui-icon-arrowstop-1-s" => "arrowstop-1-s",
        "ui-icon-arrowstop-1-w" => "arrowstop-1-w",
        "ui-icon-arrowthick-1-n" => "arrowthick-1-n",
        "ui-icon-arrowthick-1-ne" => "arrowthick-1-ne",
        "ui-icon-arrowthick-1-e" => "arrowthick-1-e",
        "ui-icon-arrowthick-1-se" => "arrowthick-1-se",
        "ui-icon-arrowthick-1-s" => "arrowthick-1-s",
        "ui-icon-arrowthick-1-sw" => "arrowthick-1-sw",
        "ui-icon-arrowthick-1-w" => "arrowthick-1-w",
        "ui-icon-arrowthick-1-nw" => "arrowthick-1-nw",
        "ui-icon-arrowthick-2-n-s" => "arrowthick-2-n-s",
        "ui-icon-arrowthick-2-ne-sw" => "arrowthick-2-ne-sw",
        "ui-icon-arrowthick-2-e-w" => "arrowthick-2-e-w",
        "ui-icon-arrowthick-2-se-nw" => "arrowthick-2-se-nw",
        "ui-icon-arrowthickstop-1-n" => "arrowthickstop-1-n",
        "ui-icon-arrowthickstop-1-e" => "arrowthickstop-1-e",
        "ui-icon-arrowthickstop-1-s" => "arrowthickstop-1-s",
        "ui-icon-arrowthickstop-1-w" => "arrowthickstop-1-w",
        "ui-icon-arrowreturnthick-1-w" => "arrowreturnthick-1-w",
        "ui-icon-arrowreturnthick-1-n" => "arrowreturnthick-1-n",
        "ui-icon-arrowreturnthick-1-e" => "arrowreturnthick-1-e",
        "ui-icon-arrowreturnthick-1-s" => "arrowreturnthick-1-s",
        "ui-icon-arrowreturn-1-w" => "arrowreturn-1-w",
        "ui-icon-arrowreturn-1-n" => "arrowreturn-1-n",
        "ui-icon-arrowreturn-1-e" => "arrowreturn-1-e",
        "ui-icon-arrowreturn-1-s" => "arrowreturn-1-s",
        "ui-icon-arrowrefresh-1-w" => "arrowrefresh-1-w",
        "ui-icon-arrowrefresh-1-n" => "arrowrefresh-1-n",
        "ui-icon-arrowrefresh-1-e" => "arrowrefresh-1-e",
        "ui-icon-arrowrefresh-1-s" => "arrowrefresh-1-s",
        "ui-icon-arrow-4" => "arrow-4",
        "ui-icon-arrow-4-diag" => "arrow-4-diag",
        "ui-icon-extlink" => "extlink",
        "ui-icon-newwin" => "newwin",
        "ui-icon-refresh" => "refresh",
        "ui-icon-shuffle" => "shuffle",
        "ui-icon-transfer-e-w" => "transfer-e-w",
        "ui-icon-transferthick-e-w" => "transferthick-e-w",
        "ui-icon-folder-collapsed" => "folder-collapsed",
        "ui-icon-folder-open" => "folder-open",
        "ui-icon-document" => "document",
        "ui-icon-document-b" => "document-b",
        "ui-icon-note" => "note",
        "ui-icon-mail-closed" => "mail-closed",
        "ui-icon-mail-open" => "mail-open",
        "ui-icon-suitcase" => "suitcase",
        "ui-icon-comment" => "comment",
        "ui-icon-person" => "person",
        "ui-icon-print" => "print",
        "ui-icon-trash" => "trash",
        "ui-icon-locked" => "locked",
        "ui-icon-unlocked" => "unlocked",
        "ui-icon-bookmark" => "bookmark",
        "ui-icon-tag" => "tag",
        "ui-icon-home" => "home",
        "ui-icon-flag" => "flag",
        "ui-icon-calendar" => "calendar",
        "ui-icon-cart" => "cart",
        "ui-icon-pencil" => "pencil",
        "ui-icon-clock" => "clock",
        "ui-icon-disk" => "disk",
        "ui-icon-calculator" => "calculator",
        "ui-icon-zoomin" => "zoomin",
        "ui-icon-zoomout" => "zoomout",
        "ui-icon-search" => "search",
        "ui-icon-wrench" => "wrench",
        "ui-icon-gear" => "gear",
        "ui-icon-heart" => "heart",
        "ui-icon-star" => "star",
        "ui-icon-link" => "link",
        "ui-icon-cancel" => "cancel",
        "ui-icon-plus" => "plus",
        "ui-icon-plusthick" => "plusthick",
        "ui-icon-minus" => "minus",
        "ui-icon-minusthick" => "minusthick",
        "ui-icon-close" => "close",
        "ui-icon-closethick" => "closethick",
        "ui-icon-key" => "key",
        "ui-icon-lightbulb" => "lightbulb",
        "ui-icon-scissors" => "scissors",
        "ui-icon-clipboard" => "clipboard",
        "ui-icon-copy" => "copy",
        "ui-icon-contact" => "contact",
        "ui-icon-image" => "image",
        "ui-icon-video" => "video",
        "ui-icon-script" => "script",
        "ui-icon-alert" => "alert",
        "ui-icon-info" => "info",
        "ui-icon-notice" => "notice",
        "ui-icon-help" => "help",
        "ui-icon-check" => "check",
        "ui-icon-bullet" => "bullet",
        "ui-icon-radio-off" => "radio-off",
        "ui-icon-radio-on" => "radio-on",
        "ui-icon-pin-w" => "pin-w",
        "ui-icon-pin-s" => "pin-s",
        "ui-icon-play" => "play",
        "ui-icon-pause" => "pause",
        "ui-icon-seek-next" => "seek-next",
        "ui-icon-seek-prev" => "seek-prev",
        "ui-icon-seek-end" => "seek-end",
        "ui-icon-seek-start" => "seek-start",
        "ui-icon-seek-first" => "seek-first",
        "ui-icon-stop" => "stop",
        "ui-icon-eject" => "eject",
        "ui-icon-volume-off" => "volume-off",
        "ui-icon-volume-on" => "volume-on",
        "ui-icon-power" => "power",
        "ui-icon-signal-diag" => "signal-diag",
        "ui-icon-signal" => "signal",
        "ui-icon-battery-0" => "battery-0",
        "ui-icon-battery-1" => "battery-1",
        "ui-icon-battery-2" => "battery-2",
        "ui-icon-battery-3" => "battery-3",
        "ui-icon-circle-plus" => "circle-plus",
        "ui-icon-circle-minus" => "circle-minus",
        "ui-icon-circle-close" => "circle-close",
        "ui-icon-circle-triangle-e" => "circle-triangle-e",
        "ui-icon-circle-triangle-s" => "circle-triangle-s",
        "ui-icon-circle-triangle-w" => "circle-triangle-w",
        "ui-icon-circle-triangle-n" => "circle-triangle-n",
        "ui-icon-circle-arrow-e" => "circle-arrow-e",
        "ui-icon-circle-arrow-s" => "circle-arrow-s",
        "ui-icon-circle-arrow-w" => "circle-arrow-w",
        "ui-icon-circle-arrow-n" => "circle-arrow-n",
        "ui-icon-circle-zoomin" => "circle-zoomin",
        "ui-icon-circle-zoomout" => "circle-zoomout",
        "ui-icon-circle-check" => "circle-check",
        "ui-icon-circlesmall-plus" => "circlesmall-plus",
        "ui-icon-circlesmall-minus" => "circlesmall-minus",
        "ui-icon-circlesmall-close" => "circlesmall-close",
        "ui-icon-squaresmall-plus" => "squaresmall-plus",
        "ui-icon-squaresmall-minus" => "squaresmall-minus",
        "ui-icon-squaresmall-close" => "squaresmall-close",
        "ui-icon-grip-dotted-vertical" => "grip-dotted-vertical",
        "ui-icon-grip-dotted-horizontal" => "grip-dotted-horizontal",
        "ui-icon-grip-solid-vertical" => "grip-solid-vertical",
        "ui-icon-grip-solid-horizontal" => "grip-solid-horizontal",
        "ui-icon-gripsmall-diagonal-se" => "gripsmall-diagonal-se",
        "ui-icon-grip-diagonal-se" => "grip-diagonal-se",
    );

}

