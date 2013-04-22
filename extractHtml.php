<?php

$startTag = "<a";
$endTag = "</a>";
$url = "http://ryan.local/tools/alink.html";
$html = file_get_contents($url);
var_dump(getTags($html, $startTag, $endTag));
function getTags($html, $startTag, $endTag) {
            $startPos = strpos($html, $startTag);
            $endPos = strpos($html, $endTag);
            $tagList = array();
            while ($startPos && $endPos) {
                $alink = substr($html, $startPos - strlen($startTag), $endPos - $startPos + strlen($endTag) + strlen($startTag));
                $tagList[] = $alink;
                $html = substr($html, $endPos + strlen($endTag) + strlen($startTag));
                $startPos = strpos($html, $startTag);
                $endPos = strpos($html, $endTag);
            }
            return $tagList;
        }
?>
