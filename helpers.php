<?php
	function startTiming(){
		$GLOBALS['start'] = microtime(true);
	}
	function stopTiming(){
		$duration = (microtime(true) - $GLOBALS['start']);
		return $duration;
	}
	
	function strip_quotes($s){
		$s = trim($s);
		return str_replace(["\'","\""],"",$s);
	}
	
	function caching($status){
		global $metrics, $card;
		if ($metrics) { $card->set('caching', $status); }
	}
?>