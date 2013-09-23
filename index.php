<?php
require "string_extensions.php";
require "card_object.php";
require "card_extractors.php";
require "timing.php";
require "db.php";
require "DAL.php";

startTiming();

$card = new card(null);
$name_search = null;

//Get database instance
$db = db();
$metrics = false;

if ($_GET){
	if (isset($_GET["name"])){
		$name_search = strip_quotes($_GET["name"]);
		if (isset($_GET["metrics"])){
			$metrics = true;
		}
	}
	else{
		$card->set_error('No paramaters in name search.');
	}
}
else{
	$card->set_error('No search parameters. Use ?name=');
}

if ($name_search){

	$card = DB_existing_card($name_search, $db);
	if ($card){
		if ($metrics) { $card->set('from_cache', 'true'); }
	}
	else{
		$card = download_card($name_search);
		
		//If we got a real card (no card errors),
		// cache it, and add appropriate metadata
		if ($card->no_error()){
			//Save
			$committed = DB_create_card($card, $db);
			
			if ($metrics){
				$card->set('from_cache', 'false');
				$card->set('into_cache', ($committed ? 'success' : 'failure'));
			}
		}
	}

}
else{
	$card->set_error('No results');
}

$card->set('request_time', stopTiming() . ' seconds');

header('content-Type: application/json');
header("Access-Control-Allow-Origin: *");
echo $card->json();

?>