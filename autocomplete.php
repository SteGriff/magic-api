<?php
require "card_object.php";
require "card_extractors.php";
require "helpers.php";
require "db.php";
require "DAL.php";

$card = new card(null);
$name_search = null;

//Get database instance
$db = db();

if ($_GET && isset($_GET['name'])){
	$name_search = strip_quotes($_GET['name']);
}

if ($name_search){
	$names = DB_maps_like($name_search, $db);
}

header('content-Type: application/json');
header("Access-Control-Allow-Origin: *");
echo json_encode($names);

?>