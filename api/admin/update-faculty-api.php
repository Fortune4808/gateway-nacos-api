<?php include '../config/connection.php'; ?>

<?php
if($apiKey!=$expected_api_key){
    $response['response']=100;
    $response['success']=false;
    $response['message']="ACCESS DENIED! You are not authorized to call this API";
}else{

    $access_key=trim($_GET['access_key']);
	///////////auth/////////////////////////////////////////
	$fetch=$callclass->_validate_accesskey($conn,$access_key);
	$array = json_decode($fetch, true);
	$check=$array[0]['check'];
	$login_role_id=$array[0]['role_id'];
	////////////////////////////////////////////////////////
	if($check==0){ 
		$response['response']=101; 
		$response['success']=false;
		$response['message']='Invalid AccessToken. Please LogIn Again.'; 

	}else{

		if ($login_role_id>1){
			$faculty_name=strtoupper(trim($_POST['faculty_name']));
			$faculty_id=trim($_POST['faculty_id']);

			if (empty($faculty_name)){
				$response['response']=102; 
				$response['success']=false;
				$response['message']='All Fields are Required!'; 

			}else{

				$update_query = mysqli_prepare($conn, "UPDATE faculty_tab SET faculty_name='$faculty_name' WHERE `faculty_id`=?");
				mysqli_stmt_bind_param($update_query, 's', $faculty_id);
				mysqli_stmt_execute($update_query);

				if ($update_query){
					$response['response'] = 104; 
					$response['success'] = true;
					$response['message'] = 'Faculty Updated Successfully!';

				}else{
					$response['response'] = 105; 
					$response['success'] = false;
					$response['message'] = 'Error Updating Faculty!';
				}

			}
		}
	}	
	
}

echo json_encode($response);
?>