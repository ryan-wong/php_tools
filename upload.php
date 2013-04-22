<!DOCTYPE HTML>
<html>
    <body>
        <fieldset style="width:300px;">
            <legend>Upload to base 64</legend>
            <form action="upload.php" method="post"
                  enctype="multipart/form-data">
                <label for="file">Filename:</label>
                <input type="file" name="file" id="file"><br>
                <input type="submit" name="submit" value="Submit">
                </fieldset>
            </form>
            <?php
            /*
             * Array
              (
              [file] => Array
              (
              [name] => logo.jpg
              [type] => image/jpeg
              [tmp_name] => /tmp/phpGyzDJ0
              [error] => 0
              [size] => 11268
              )

              )
             */

            function getDataURI($image, $mime = '') {
                return 'data: ' . $mime . ';base64,' . base64_encode($image);
            }

            if ($_FILES) {
                $fileArray = $_FILES['file'];
                $fileLocation = $fileArray['tmp_name'];
                $fileData = file_get_contents($fileLocation);
                $src = getDataURI($fileData, $_FILES['file']['type']);
                echo "<p>Image using base64</p>";
                $imgStr = '<img src="' . $src . '">';
                echo $imgStr;
                echo '<br/>';
                echo "<p>Image using base64 To Copy</p>";
                $copytext = htmlentities($imgStr);
                echo "<textarea cols='100' rows='10' onclick='this.focus();this.select()' readonly='readonly'> $copytext </textarea>";
                if (file_exists($fileLocation)) {
                    unlink($fileLocation);                    
                }
            }
            ?>
    </body>
</html>