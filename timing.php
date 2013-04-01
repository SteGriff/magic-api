<?php
	function startTiming(){
		$GLOBALS['start'] = microtime(true);
	}
	function stopTiming(){
		$duration = (microtime(true) - $GLOBALS['start']);
		return $duration;
	}
?>