<?php
/**
 * HOW to use:
 * 1. extend this template class or use this class
 * 2. fill in the replace text and wrap all replacement you want in <?R R?>
 * 3. for any code you want the template to execute wrap in <?E E?>
 * 4. in every execute code, make sure to save the result of the execute code in a \$result variable
 * which will replace the text in the execute code
 * 5. fill in the replaceArray with default values you want if no replace is pass in
 */
class Template {

    protected $_replaceText = <<<TEXT
class <?Rclass_nameR?>{
       protected \$_test = null;
        public function render(){
       <?E
       \$i = 34 * 56;
       \$result = \$i . 'TEST';
        E?>
       }
}
TEXT;

	protected $_replaceArray = array(
		'class_name'=>"className"
	);
    public function replace($replaceArray=array()) {
    	if(!$replaceArray){
    		$replaceArray = $this->_replaceArray;
    	}
        $replaceText = $this->_replaceText;
        foreach ($replaceArray as $key => $value) {
            $replacingText = "<?R" . trim($key) . "R?>";
            $replaceText = str_replace($replacingText, $value, $replaceText);
        }
        $this->_replaceText = $replaceText;
    }

    public function execute() {
        $startPattern = "<?E";
        $endPattern = "E?>";
        $executeText = $this->_replaceText;
        $result = '';
        while (strpos($executeText, $startPattern) && strpos($executeText, $endPattern)) {
            $startPos = strpos($executeText, $startPattern);
            $endPos = strpos($executeText, $endPattern);
            $query = substr($executeText, $startPos + 3, $endPos - $startPos-3);
            eval($query);
            if ($result) {
                $executeText = str_replace(substr($executeText, $startPos, $endPos - $startPos+3), $result, $executeText);
            } else {
                $this->_replaceText = $executeText;
                return 0;
            }
            $result = '';
        }
        $this->_replaceText = $executeText;
        return 1;
    }  
    public function renderText(){
        echo "<pre>".print_r($this->_replaceText,true)."</pre>";
    }
    public function download($filename){
     header("Content-type: application/force-download");
    header("Content-Disposition: attachment; filename=$filename;size=" . strlen($this->_replaceText));
    echo $this->_replaceText;
    exit;
    }
//Basic matching
//preg_match("/PHP/", "PHP")       # Match for an unbound literal
//preg_match("/^PHP/", "PHP")      # Match literal at start of string
//preg_match("/PHP$/", "PHP")      # Match literal at end of string
//preg_match("/^PHP$/", "PHP")     # Match for exact string content
//preg_match("/^$/", "")           # Match empty string
//Using different regex delimiters
//preg_match("/PHP/", "PHP")                # / as commonly used delimiter
//preg_match("@PHP@", "PHP")                # @ as delimiter
//preg_match("!PHP!", "PHP")                # ! as delimiter
//Changing the delimiter becomes useful in some cases
//preg_match("/http:\/\//", "http://");     # match http:// protocol prefix with / delimiter
//preg_match("#http://#",   "http://")      # match http:// protocol prefix with # delimiter
//Case sensitivity
//preg_match("/PHP/", "PHP")                # case sensitive string matching
//preg_match("/php/i", "PHP")               # case in-sensitive string matching
//Matching with wildcards
//preg_match("/P.P/",     "PHP")            # match a single character
//preg_match("/P.*P/",    "PHP")            # match multipe characters
//preg_match("/P[A-Z]P/", "PHP")            # match from character range A-Z
//preg_match("/[PH]*/",   "PHP")            # match from character set P and H
//preg_match("/P\wP/",    "PHP")            # match one word character
//preg_match("/\bPHP\b/", "regex in PHP")   # match the word "PHP", but not "PHP" as larger string
//Using quantifiers
//preg_match("/[PH]{3}/",   "PHP")          # match exactly 3 characters from set [PH]
//preg_match("/[PH]{3,3}/", "PHP")          # match exactly 3 characters from set [PH]
//preg_match("/[PH]{,3}/",  "PHP")          # match at most 3 characters from set [PH]
//preg_match("/[PH]{3,}/",  "PHP")          # match at least 3 characters from set [PH]
}
//$t = new template();
//$t->replace();
//$t->execute();
////$t->renderText();
//$t->download('test.txt');
?>

