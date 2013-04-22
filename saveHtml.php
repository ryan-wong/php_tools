<?php
if(isset($_POST) && $_POST['saveText']){    
    $filename = 'test.html';
    header("Content-type: application/force-download");
    header("Content-Disposition: attachment; filename=$filename;size=" . strlen($_POST['saveText']));
    echo $_POST['saveText'];
    exit;
}
?>
