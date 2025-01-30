<?php
include '../config/connection.php';

if ($apiKey != $expected_api_key) {

    $response = [
        'response' => 100,
        'success' => false,
        'message' => "ACCESS DENIED! You are not authorized to call this API"
    ];

} else {

    $access_key=trim($_GET['access_key']);
	///////////auth/////////////////////////////////////////
	$fetch=$callclass->_validate_accesskey($conn,$access_key);
	$array = json_decode($fetch, true);
	$check=$array[0]['check'];
	$login_staff_id=$array[0]['staff_id'];

	if($check==0){ 
		$response['response']=101; 
		$response['success']=false;
		$response['message']='Invalid AccessToken. Please LogIn Again.'; 

	}else{

		$fetch_query = $callclass->_get_all_counts($conn, $login_staff_id);
		$fetch_query_array = json_decode($fetch_query, true);
		

		$response = [
			'response' => 102,
			'success' => true,
			'message' => "All counts successfully fetched",
			'data' => $fetch_query_array
		];

        
    }
}

echo json_encode($response);
?>
