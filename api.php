<?php
require "card_object.php";
require "card_extractors.php";
require "helpers.php";
require "db.php";
require "DAL.php";

startTiming();

$card = new card(null);
$name_search = null;

//Get database instance
$db = db();
$metrics = false;

if ($_GET){
	if (isset($_GET['name'])){
		$name_search = strip_quotes($_GET['name']);
		$metrics = isset($_GET['metrics']);
	}
	else{
		$card->set_error('No parameters in name search.');
		DB_add_to_log('api_bad_req', 'param not set', 0, $db);
	}
}
else{
	$card->set_error('No search parameters. Use ?name=');
	DB_add_to_log('api_bad_req', 'no GET', 0, $db);
}

if ($name_search){

	//Check if this search has historically found a card on Gatherer:
	$mapped_id = DB_mapped_id($name_search, $db);
	
	if ($mapped_id){
		//Get the mapped card and set some metrics
		// (it's returned at the end of execution)
		$card = DB_card_with_id($mapped_id, $db);
		
		if ($card){
			caching('found in map');
			DB_add_to_log('api_found', $name_search, $db);
		}
		else{
			//Probably some kind of data mismatch
			$card->set_error("Data Error; tell @SteGriff the card map gave a bad ID $mapped_id for $name_search");
			DB_add_to_log('data_error', "card map bad id $mapped_id for $name_search", 0, $db);
		}
	}
	else{
		//No map entry
		//Download the card
		$card = download_card($name_search);
		
		//Check if card's real name (not the user search) is in the database
		$existing_card = DB_card_with_name($card->get('name'), $db);
		if ($existing_card){
			//Add a map record with existing card id, and user search
			$mapped = DB_add_to_map($name_search, $existing_card->get('ID'), $db);
			if ($mapped){
				caching('added to map');
				DB_add_to_log('api_map', $name_search, $db);
			}
			else{
				caching('mapping failed');
				DB_add_to_log('data_error', 'cache or map failed', $db);
			}
		}
		else{
			//Card doesn't exist - store it if we got a real card (no errors)
			if ($card->no_error()){
				//Save and map
				$new_card_id = DB_create_card($card, $db);
				$mapped = DB_add_to_map($name_search, $new_card_id, $db);
				
				if ($new_card_id && $mapped){
					caching('added to cache and map');
					DB_add_to_log('api_add_and_map', $name_search, $db);
				}
				else{
					caching('cache or map failed');
					DB_add_to_log('data_error', 'cache or map failed', $db);
				}
			}
			else{
				DB_add_to_log('api_not_found', $name_search, $db);
			}
			//Errors will be reported in card response if there are any, so no else{} here
		}
	}

}
else{
	$card->set_error('No results');
	DB_add_to_log('api_not_found', 'NULL search', $db);
}

$card->set('request_time', stopTiming() . ' seconds');

header('content-Type: application/json');
header("Access-Control-Allow-Origin: *");
echo $card->json();

?>