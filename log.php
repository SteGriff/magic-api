<?php
require "helpers.php";
require "db.php";
require "DAL.php";

$enable_logging_api = false;
$response = ['success' => true];

function fail($msg){
	global $response;
	$response['success'] = false;
	$response['message'] = $msg;
}

if ($enable_logging_api){
	//Get database instance
	$db = db();

	if ($_POST){

		//Require event and search string
		if (isset($_POST['event']) && isset($_POST['search'])){
		
			$event = strip_quotes($_POST['event']);
			$search = strip_quotes($_POST['search']);
			
			//Get optional timing (time taken) param
			$timing = isset($_POST['timing'])
				? $_POST['timing']
				: null;
				
			//Add the log entry
			$log_status = DB_add_to_log($event, $search, $timing, $db);
			
			//Return success or error message
			if ($log_status === true){
				$response['success'] = true;
			}
			else{
				$response['success'] = false;
				$response['message'] = $log_status;
			}
			
			//Now drops out to bottom of script and returns the data.
		}
		else{
			//Event or search not set
			fail('No parameters were received');
		}
	}
	else{
		//No POST set
		fail('Use POST');
	}
}
else{
	fail('Logging API is disabled');
}

//Serialize JSON response and return
header('content-Type: application/json');
header("Access-Control-Allow-Origin: *");
echo json_encode($response);

?>