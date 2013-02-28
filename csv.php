<?php
$csv = 0;
if(isset($_GET['csv']) && !empty($_GET['csv'])){
$csv = 1;
}
$csvArray = array(
array('fcol','scol','tcol','fcol'),
array(1,2,3,4),
array('ccol','bcol','acol','fcol'),

);
$filename = "test";
if ($csv) {
    $csvStr = '';
    foreach ($csvArray as $fields) {
    	$count = count($fields);
    	$i = 0;
    	foreach ($fields as $field){     	
	        $csvStr .= $field;
	        if($i < $count - 1){
				$csvStr.= ',' ;
	        } else{
	        	$csvStr .= "\n";
	        }
	        $i++;
        }
    }
    header("Content-type: application/force-download");
    header("Content-Disposition: attachment; filename=$filename" . date("Y-m-d") . ".csv;size=" . strlen($csvStr));
    echo $csvStr;
    exit;
}
?>
