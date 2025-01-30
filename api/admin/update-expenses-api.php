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
		$expenses_id = trim($_POST['expenses_id']);
		$expenses_description = strtoupper(trim($_POST['expenses_description']));
        $expenses_item = strtoupper(trim($_POST['expenses_item']));
        $expenses_amount = preg_replace('/[^0-9\.]/', '', trim($_POST['expenses_amount']));

		if (empty($expenses_description) || empty($expenses_item) || empty($expenses_amount)){
			$response['response']=102; 
			$response['success']=false;
			$response['message']='All Fields are Required!'; 

		}else{
			$query = mysqli_prepare($conn, "SELECT * FROM `expenses_tab` WHERE expenses_id = ?");
            mysqli_stmt_bind_param($query, 's', $expenses_id);
            mysqli_stmt_execute($query);
            $result = mysqli_stmt_get_result($query);
            mysqli_stmt_close($query);

			if (mysqli_num_rows($result) > 0){
				$success = mysqli_fetch_array($result); 
                $total_balance = $success['balance_before']; 
			}

			$balance_before = (float) $total_balance - (float) $total_amount_spent;
            $balance_after = $balance_before - $expenses_amount;

			$update_query = mysqli_prepare($conn, "UPDATE expenses_tab SET expenses_decription=?, expenses_items=?, expenses_amount=?, balance_before=?, balance_after=? WHERE `expenses_id`=?");
			mysqli_stmt_bind_param($update_query, 'ssiiis', $expenses_description, $expenses_item, $expenses_amount, $balance_before, $balance_after, $expenses_id);

			if (mysqli_stmt_execute($update_query)){
				$response['response'] = 104; 
				$response['success'] = true;
				$response['message'] = 'Expenses Updated Successfully!';

			}else{
				$response['response'] = 105; 
				$response['success'] = false;
				$response['message'] = 'Error Updating Expenses!';
			}

		}
	}	
	
}

echo json_encode($response);
?>