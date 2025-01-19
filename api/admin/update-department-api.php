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
			$department_id=trim($_POST['department_id']);
			$faculty_id=trim($_POST['faculty_id']);
			$status_id=trim($_POST['status_id']);

			if (empty($department_id) || empty($faculty_id) || empty($status_id)){
				$response['response']=102; 
				$response['success']=false;
				$response['message']='All Fields are Required!'; 

			}else{

				$update_query = mysqli_prepare($conn, "UPDATE department_tab SET department_id=?, faculty_id=?, status_id=? WHERE `department_id`=?");
				mysqli_stmt_bind_param($update_query, 'ssis', $department_id, $faculty_id, $status_id, $department_id);
				mysqli_stmt_execute($update_query);

				if ($update_query){
					$response['response'] = 104; 
					$response['success'] = true;
					$response['message'] = 'Department Updated Successfully!';

				}else{
					$response['response'] = 105; 
					$response['success'] = false;
					$response['message'] = 'Error Updating Department!';
				}

			}
		}
	}	
	
}

echo json_encode($response);
?>