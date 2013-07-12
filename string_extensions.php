<?php

function strip_quotes($s){
	$s = trim($s);
	return str_replace(["\'","\""],"",$s);
}

?>