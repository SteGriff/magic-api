<?php

function db(){
	//Connect to db using MySQLi
	$sv = "db.your-mysqli-server.com";
	$un = "your-username";
	$conn = new mysqli($sv, $un, "password", "database");
	if ($conn->connect_error) die("Could not connect to database: " . $conn->connect_error);
	return $conn;
}

?>