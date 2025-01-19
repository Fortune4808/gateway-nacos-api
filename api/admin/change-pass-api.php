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
	$login_staff_id=$array[0]['staff_id'];
	////////////////////////////////////////////////////////
	if($check==0){ 
		$response['response']=101; 
		$response['success']=false;
		$response['message']='Invalid AccessToken. Please LogIn Again.'; 

	}else{

		$old_pass=trim($_POST['old_pass']);
		$new_pass=trim($_POST['new_pass']);
		$confirm_pass=trim($_POST['confirm_pass']);

		if (empty($old_pass)||empty($new_pass)||empty($confirm_pass)){
			$response['response']=102; 
			$response['success']=false;
			$response['message']='All Fields are Required!'; 

		}elseif ($new_pass!=$confirm_pass){
			$response['response']=103; 
			$response['success']=false;
			$response['message']='Password does not Match!'; 

		}else{

			$query = mysqli_prepare($conn, "SELECT * FROM staff_tab WHERE `staff_id`=?");
            mysqli_stmt_bind_param($query, 's', $login_staff_id);
            mysqli_stmt_execute($query);
            $result = mysqli_stmt_get_result($query);

			if  (mysqli_num_rows($result) > 0){
				$success = mysqli_fetch_array($result); 
				$hashedpassword=$success['password'];

				if(password_verify($old_pass, $hashedpassword)){
					$new_hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
					$update_query = mysqli_prepare($conn, "UPDATE staff_tab SET password='$new_hashed_password' WHERE `staff_id`=?");
					mysqli_stmt_bind_param($update_query, 's', $login_staff_id);
					mysqli_stmt_execute($update_query);
	
					if ($update_query){
						$response['response'] = 104; 
						$response['success'] = true;
						$response['message'] = 'Password Updated Successfully!';
	
					}else{
						$response['response'] = 105; 
						$response['success'] = false;
						$response['message'] = 'Error Updating Password!';
					}
	
				}else{
					$response['response'] = 106; 
					$response['success'] = false;
					$response['message'] = 'Old Password is Incorrect!';
				}
			}

		}
	}	
	
}

echo json_encode($response);
?>