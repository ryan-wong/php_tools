<?php

class Yesup_Automate_Model_Helper_CreateView extends Yesup_Automate_Model_Helper_CreateAbstract {

    protected $_code = '';
    protected $_filename = '';

    public function generateView($post) {
        $this->_filename = $post['filename'] . '.phtml';
        $this->_code = "<?php\n" . $post['text'];
    }

    public function serveString() {
        $filename = lcfirst($this->_filename);
        ob_flush();
        if (ini_get('zlib.output_compression')) {
            $level = 1;
        } else {
            $level = 0;
        }
        while (ob_get_level() > $level) {
            ob_end_clean();
        }
        // required for IE, otherwise Content-Disposition may be ignored
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');
        flush();
        ob_start();
        echo ($this->_code);
        exit;
    }

}