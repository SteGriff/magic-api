<?php

function db(){
	//Connect to db using MySQLi
	$sv = "db425111171.db.1and1.com";
	$un = "dbo425111171";
	$conn = new mysqli($sv, $un, "24orangesegastap", "db425111171");
	if ($conn->connect_error) die("Could not connect to database: " . $conn->connect_error);
	return $conn;
}

?>