<?php
function str_in_str($needle, $haystack) {
  return (strpos($haystack, $needle) !== false);
}

function strip_quotes($s){
	$s = trim($s);
	return str_replace(["\'","\""],"",$s);
}

?>