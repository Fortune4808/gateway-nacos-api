<?php 
include '../config/connection.php';

if($apiKey != $expected_api_key) {
    $response['code'] = 100;
    $response['success'] = false;
    $response['message'] = "ACCESS DENIED! You are not authorized to call this API";
} else {
    $access_key = trim($_GET['access_key']);

    $fetch = $callclass->_validate_accesskey($conn, $access_key);
    $array = json_decode($fetch, true);
    $check = $array[0]['check'];
    $login_staff_id = $array[0]['staff_id'];
    $login_role_id = $array[0]['role_id'];

    $response['check'] = $check; 

    if($check == 0) { 
        $response['response'] = 101; 
        $response['success'] = false;
        $response['message'] = 'Invalid Access Token. Please Log In Again.';
    } else {
        $expenses_description = strtoupper(trim($_POST['expenses_description']));
        $expenses_item = strtoupper(trim($_POST['expenses_item']));
        $expenses_amount = preg_replace('/[^0-9\.]/', '', trim($_POST['expenses_amount']));

        if (empty($expenses_description) || empty($expenses_item) || empty($expenses_amount)){

            $response = [
                'response' => 102,
                'success' => false,
                'message' => "Fill all fields to continue."
            ];

        }else{

            $expenses = $callclass->_get_all_counts($conn, $login_staff_id);
			$array = json_decode($expenses, true);
			$total_balance = $array[0]['total_balance'];
            $total_amount_spent = $array[0]['total_amount_spent'];

            $balance_before = (float) $total_balance - (float) $total_amount_spent;
            $balance_after = $balance_before - $expenses_amount;

            $sequence = $callclass->_get_sequence_count($conn, 'E');
			$array = json_decode($sequence, true);
			$no = $array[0]['no'];
			$expenses_id = 'E'. $no;

            $add_expenses=mysqli_prepare($conn, "INSERT INTO `expenses_tab`(`expenses_id`, `expenses_decription`, `expenses_items`, `expenses_amount`, `balance_before`, `balance_after`, `created_Time`) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($add_expenses, 'sssiii', $expenses_id, $expenses_description, $expenses_item, $expenses_amount, $balance_before, $balance_after);

            if (mysqli_stmt_execute($add_expenses)){
                $response = [
                    'response' => 103,
                    'success' => true,
                    'message' => "EXPENSES SUCCESSFULLY ADDED!"
                ];
                
            }
           
        }
    }
}

echo json_encode($response);
?>
