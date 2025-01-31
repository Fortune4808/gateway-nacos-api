<?php
class allClass{

    function _get_sequence_count($conn, $item){
	$count=mysqli_fetch_array(mysqli_query($conn,"SELECT count_value FROM setup_master_count_tab WHERE count_id = '$item' FOR UPDATE"));
	$num=$count[0]+1;
	mysqli_query($conn,"UPDATE `setup_master_count_tab` SET `count_value` = '$num' WHERE count_id = '$item'")or die (mysqli_error($conn));
	if ($num<10){$no='00'.$num;}elseif($num>=10 && $num<100){$no='0'.$num;}else{$no=$num;}
	return '[{"num":"'.$num.'","no":"'.$no.'"}]';
}

function _validate_accesskey($conn,$access_key){
	$query=mysqli_query($conn,"SELECT a.*, b.department_id FROM staff_tab a JOIN staff_department_tab b ON b.staff_id = a.staff_id JOIN department_tab c ON c.department_id = b.department_id WHERE a.access_key='$access_key' AND a.status_id=1 AND c.status_id=1;")or die (mysqli_error($conn));
	$count = mysqli_num_rows($query);
		if ($count>0){
			$fetch_query=mysqli_fetch_array($query);
			$staff_id=$fetch_query['staff_id'];
			$role_id=$fetch_query['role_id'];
			$department_id=$fetch_query['department_id'];
			$check=1; 
		}else{
			$check=0;
		}
		return '[{"staff_id":"'.$staff_id.'","check":"'.$check.'","role_id":"'.$role_id.'","department_id":"'.$department_id.'"}]';
	}


		function _get_staff($conn, $staff_id){
			$query=mysqli_query($conn,"SELECT * FROM staff_tab WHERE staff_id = '$staff_id'");
			$fetch_query=mysqli_fetch_array($query);
			$staff_id=$fetch_query['staff_id'];
			$fullname=$fetch_query['fullname'];
			$email_address=$fetch_query['email_address'];
			$phoneno=$fetch_query['phoneno'];
			$role_id=$fetch_query['role_id'];
			$status_id=$fetch_query['status_id'];
			$password=$fetch_query['password'];
			$otp=$fetch_query['otp'];
			$date=$fetch_query['date'];
			$last_login=$fetch_query['last_login'];
			$passport=$fetch_query['passport'];
			
			return '[{"staff_id":"'.$staff_id.'","fullname":"'.$fullname.'","email_address":"'.$email_address.'","phoneno":"'.$phoneno.'","role_id":"'.$role_id.'","status_id":"'.$status_id.'","password":"'.$password.'","otp":"'.$otp.'","date":"'.$date.'","last_login":"'.$last_login.'","passport":"'.$passport.'"}]';
		}

		function _get_backend_settings(){
			$query=mysqli_query($conn,"SELECT * FROM setup_system_setting_tab");
			$fetch_query=mysqli_fetch_array($query);
			$nacos_fee_amount=$fetch_query['nacos_fee_amount'];
			$departmental_fee_amount=$fetch_query['departmental_fee_amount'];
			$smtp_host=$fetch_query['smtp_host'];
			$smtp_username=$fetch_query['smtp_username'];
			$smtp_password=$fetch_query['smtp_password'];
			$smtp_port=$fetch_query['smtp_port'];
			$support_email=$fetch_query['support_email'];
			
			return '[{"nacos_fee_amount":"'.$nacos_fee_amount.'","departmental_fee_amount":"'.$departmental_fee_amount.'","smtp_host":"'.$smtp_host.'","smtp_username":"'.$smtp_username.'","smtp_password":"'.$smtp_password.'","smtp_port":"'.$smtp_port.'","support_email":"'.$support_email.'"}]';
		}

		function _add_alert($conn, $staff_id, $role_id, $department_id, $fullname, $alert_description, $system_used, $ip_address){
			$callclass = new allClass(); 

			$sequence = $callclass->_get_sequence_count($conn, 'ALT');
			$array = json_decode($sequence, true);
			$no = $array[0]['no'];
			$alert_id = 'ALT'. $no;
	
			$add_new_alert = mysqli_prepare($conn, "INSERT INTO `alert_tab`(`alert_id`, `staff_id`, `role_id`, `department_id`, `fullname`, `alert_description`, `system_used`, `ip_address`, `created_time`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, NOW())");
			mysqli_stmt_bind_param($add_new_alert, 'ssisssss', $alert_id, $staff_id, $role_id, $department_id, $fullname, $alert_description, $system_used, $ip_address);
			mysqli_stmt_execute($add_new_alert);
			mysqli_stmt_close($add_new_alert);
		}

		function _get_all_counts($conn, $staff_id){
			$query = mysqli_query($conn, "
				SELECT 
				(SELECT COUNT(*) FROM staff_tab WHERE staff_id!='$staff_id') AS staff_count, 
				(SELECT COUNT(*) FROM student_tab) AS student_count, 
				(SELECT SUM(departmental_fee_amount_first_session + departmental_fee_amount_second_session) FROM departmental_fee_tab) AS expected_departmental_fee,
				(SELECT SUM( CASE WHEN a.first_session_status_id = 4 AND a.second_session_status_id = 4 THEN a.departmental_fee_amount_first_session + a.departmental_fee_amount_second_session WHEN a.first_session_status_id = 4 AND a.second_session_status_id = 7 THEN a.departmental_fee_amount_first_session WHEN a.first_session_status_id = 4 AND a.second_session_status_id != 7 THEN a.departmental_fee_amount_first_session ELSE 0 END ) FROM departmental_fee_tab a WHERE a.first_session_status_id = 4) AS available_departmental_balance,
				(SELECT SUM(nacos_fee_amount_first_session + nacos_fee_amount_second_session) FROM nacos_fee_tab) AS expected_nacos_fee,
				(SELECT SUM( CASE WHEN a.first_session_status_id = 4 AND a.second_session_status_id = 4 THEN a.nacos_fee_amount_first_session + a.nacos_fee_amount_second_session WHEN a.first_session_status_id = 4 AND a.second_session_status_id = 7 THEN a.nacos_fee_amount_first_session WHEN a.first_session_status_id = 4 AND a.second_session_status_id != 7 THEN a.nacos_fee_amount_first_session ELSE 0 END ) FROM nacos_fee_tab a WHERE a.first_session_status_id = 4) AS available_nacos_balance,
				(SELECT (SELECT SUM(CASE WHEN a.first_session_status_id = 4 AND a.second_session_status_id = 4 THEN a.departmental_fee_amount_first_session + a.departmental_fee_amount_second_session WHEN a.first_session_status_id = 4 AND a.second_session_status_id = 7 THEN a.departmental_fee_amount_first_session WHEN a.first_session_status_id = 4 AND a.second_session_status_id != 7 THEN a.departmental_fee_amount_first_session ELSE 0 END ) FROM departmental_fee_tab a WHERE a.first_session_status_id = 4 ) + ( SELECT SUM( CASE WHEN b.first_session_status_id = 4 AND b.second_session_status_id = 4 THEN b.nacos_fee_amount_first_session + b.nacos_fee_amount_second_session WHEN b.first_session_status_id = 4 AND b.second_session_status_id = 7 THEN b.nacos_fee_amount_first_session WHEN b.first_session_status_id = 4 AND b.second_session_status_id != 7 THEN b.nacos_fee_amount_first_session ELSE 0 END ) FROM nacos_fee_tab b WHERE b.first_session_status_id = 4 )) AS total_balance,
				(SELECT (SUM(expenses_amount)) FROM expenses_tab) AS total_amount_spent,
				(SELECT COUNT(*) FROM alert_tab) AS alert_count
			");
		
			$fetch_query = mysqli_fetch_array($query);
			$staff_count = $fetch_query['staff_count'];
			$student_count = $fetch_query['student_count'];
			$expected_departmental_fee = $fetch_query['expected_departmental_fee'];
			$available_departmental_balance = $fetch_query['available_departmental_balance'];
			$expected_nacos_fee = $fetch_query['expected_nacos_fee'];
			$available_nacos_balance = $fetch_query['available_nacos_balance'];
			$total_balance = $fetch_query['total_balance'];
			$total_amount_spent = $fetch_query['total_amount_spent'];
			$alert_count = $fetch_query['alert_count'];

			$expected_departmental_fee = number_format((float)$expected_departmental_fee, 2, '.', ',');
			$available_departmental_balance = number_format((float)$available_departmental_balance, 2, '.', ',');
			$expected_nacos_fee = number_format((float)$expected_nacos_fee, 2, '.', ',');
			$available_nacos_balance = number_format((float)$available_nacos_balance, 2, '.', ',');
			$total_balance = number_format((float)$total_balance, 2, '.', ',');
			$total_amount_spent = number_format((float)$total_amount_spent, 2, '.', ',');
		
			return '[{"staff_count": "'.$staff_count.'","student_count": "'.$student_count.'","expected_departmental_fee": "'.$expected_departmental_fee.'","available_departmental_balance": "'.$available_departmental_balance.'","expected_nacos_fee": "'.$expected_nacos_fee.'","available_nacos_balance": "'.$available_nacos_balance.'","total_balance": "'.$total_balance.'","total_amount_spent": "'.$total_amount_spent.'","alert_count": "'.$alert_count.'"}]';
		}
		


		function checkExistingField($conn, $field, $value) {
			$query = mysqli_query($conn, "SELECT * FROM staff_tab WHERE $field = '$value'");
			return mysqli_num_rows($query) > 0;
		}
	
		function validateTextInput($input) {
			$input = trim($input);
			return empty($input) || preg_match("/^[a-zA-Z\s]+$/", $input);
		}
		

		function validatePhoneNumber($input) {
			$input = trim($input);
			return preg_match("/^[\d\s()+-]+$/", $input); // Allow digits, spaces, parentheses, and dashes
		}

}$callclass=new allClass();
?>