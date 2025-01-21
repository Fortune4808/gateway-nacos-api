<?php include '../config/connection.php'; ?>

<?php
if($apiKey!=$expected_api_key){
    $response['code']=100;
    $response['success']=false;
    $response['message']="ACCESS DENIED! You are not authiorized to call this API";

}else{
    $access_key=trim($_GET['access_key']);
    ///////////auth/////////////////////////////////////////
    $fetch=$callclass->_validate_accesskey($conn,$access_key);
    $array = json_decode($fetch, true);
    $check=$array[0]['check'];
   
    $response['check']=$check;
    if($check==0){
        $response['response']=101; 
        $response['success']=false;
        $response['message']='Invalid AccessToken. Please LogIn Again.'; 
    }else{

        $nacos_fee_amount = preg_replace('/[^0-9\.]/', '', trim($_POST['nacos_fee_amount']));
        $departmental_fee_amount = preg_replace('/[^0-9\.]/', '', trim($_POST['departmental_fee_amount']));
        $smtp_host=strtoupper(trim($_POST['smtp_host']));
        $smtp_username=strtoupper(trim($_POST['smtp_username']));
        $smtp_password=trim($_POST['smtp_password']);
        $smtp_port=trim($_POST['smtp_port']);
        $support_email=strtolower(trim($_POST['support_email']));

        if (empty($nacos_fee_amount) || empty($departmental_fee_amount) || empty($smtp_host) || empty($smtp_username) || empty($smtp_password) || empty($smtp_port) || empty($support_email)){

            $response = [
                'response' => 102,
                'success' => false,
                'message' => "Fill all fields to continue."
            ];

        }else{

            if (!is_numeric($nacos_fee_amount) || !is_numeric($departmental_fee_amount)) {

                $response = [
                    'response' => 103,
                    'success' => false,
                    'message' => "Invalid amount. Please ensure you input valid amounts."
                ];

            }else{

                if (!filter_var($support_email, FILTER_VALIDATE_EMAIL)){

                    $response = [
                        'response' => 105,
                        'success' => false,
                        'message' => "Invalid Support Email Address."
                    ];
    
                }else{
                        
                    $query = mysqli_prepare($conn, "UPDATE setup_system_setting_tab SET nacos_fee_amount=?, departmental_fee_amount=?, smtp_host=?, smtp_username=?, smtp_password=?, smtp_port=?, support_email=?") or die(mysqli_error($conn));
                    mysqli_stmt_bind_param($query, 'iisssis', $nacos_fee_amount, $departmental_fee_amount, $smtp_host, $smtp_username, $smtp_password, $smtp_port, $support_email);
    
                    if (mysqli_stmt_execute($query)){
                        $response = [
                            'response' => 106,
                            'success' => true,
                            'message' => "System Settings Successfully Updated."
                        ];
                    } else {
                        $response = [
                            'response' => 107,
                            'success' => false,
                            'message' => "Error updating data: " . mysqli_error($conn)
                        ];
                    }
                            
                }
            }
            
        }

    }
}

echo json_encode($response);
?>