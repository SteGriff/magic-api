<?php

require "string_extensions.php";
require "card_extractors.php";
require "timing.php";
require "db.php";
require "DAL.php";

startTiming();

$cardObject = null;
$name_search = null;
$cardObject["error"] = null;

//Get database instance
$db = db();
$metrics = false;

if ($_GET){
	if ( isset($_GET["name"]) ){
		$name_search = strip_quotes($_GET["name"]);
		if ( isset($_GET["metrics"]) ){
			$metrics = true;
		}
	}
	else{
		$cardObject["error"] = "No paramaters in name search.";
	}
}
else{
	$cardObject["error"] = "No search parameters. Use ?name=";
}

if ($name_search){

	$cardObject = DB_card_exists($name_search, $db);
	if ( $cardObject ){
		if ($metrics) { $cardObject["from_cache"] = "true"; }
	}
	else{
		$cardObject = download_card($name_search);
		
		//If we got a real card, cache it and mark it.
		if ( !isset($cardObject["error"]) ){
		
			//Save
			$committed = DB_create_card($cardObject, $db);
			
			if ($metrics){
				$cardObject["from_cache"] = "false";
				$cardObject["into_cache"] = $committed ? "success" : "failure";
			}
		}
	}

}
elseif (!$cardObject["error"]){
	$cardObject["error"] = "No results";
}

$cardObject["request_time"] = stopTiming() . " seconds";

header('content-Type: application/json');
echo json_encode($cardObject);

?>