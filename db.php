<?php

function db(){
	//Connect to db using MySQLi
	$sv = "server.address.com";
	$un = "db-username";
	$conn = new mysqli($sv, $un, "db-password", "db-name");
	if ($conn->connect_error) die("Could not connect to database: " . $conn->connect_error);
	return $conn;
}

?>