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

        $staff_id=trim($_POST['staff_id']);
        $fullname=strtoupper(trim($_POST['fullname']));
        $dob=trim($_POST['dob']);
        $gender=trim($_POST['gender']);
        $religion=trim($_POST['religion']);
        $email_address=strtolower(trim($_POST['email_address']));
        $phoneno=trim($_POST['phoneno']);
        $role = trim($_POST['role_id']);
        $status = trim($_POST['status_id']);

        if (empty($staff_id) || empty($fullname) || empty($email_address) || empty($phoneno) || empty($role) || empty($status)){

            $response = [
                'response' => 102,
                'success' => false,
                'message' => "Fill all fields to continue."
            ];

        }else{

            $validate_fullname = $callclass->validateTextInput($fullname);
            $validate_phone = $callclass->validatePhoneNumber($phoneno);

            if (!$validate_fullname){

                $response = [
                    'response' => 103,
                    'success' => false,
                    'message' => "Digit is not allowed in fullname input"
                ];

            }elseif (!$validate_phone){

                $response = [
                    'response' => 105,
                    'success' => false,
                    'message' => "Please ensure you enter a valid phone numner"
                ];

            }else{

                if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)){

                    $response = [
                        'response' => 106,
                        'success' => false,
                        'message' => "Invalid Email Address."
                    ];

                }else{

                    $query=mysqli_prepare($conn,"SELECT * FROM `staff_tab` WHERE email_address=? AND staff_id!=? LIMIT 1") or die (mysqli_error($conn));
                    mysqli_stmt_bind_param($query, 'ss', $email_address, $staff_id);
                    mysqli_stmt_execute($query);
                    $result = mysqli_stmt_get_result($query);

                    if (mysqli_num_rows($result)>0){
                        $response = [
                            'response' => 107,
                            'success' => false,
                            'message' => "Email Address already exists."
                        ];

                    }else{
                        
                        $query = mysqli_prepare($conn, "UPDATE staff_tab SET fullname=?, dob=?, gender_id=?, religion_id=?, email_address=?, mobile_no=?, role_id=?, status_id=? WHERE staff_id=?") or die(mysqli_error($conn));
                        mysqli_stmt_bind_param($query, 'ssiissiis', $fullname, $dob, $gender, $religion, $email_address, $phoneno, $role, $status, $staff_id);

                        if (mysqli_stmt_execute($query)){
                            $response = [
                                'response' => 108,
                                'success' => true,
                                'message' => "Staff Data Successfully Updated."
                            ];
                        } else {
                            $response = [
                                'response' => 109,
                                'success' => false,
                                'message' => "Error updating data: " . mysqli_error($conn)
                            ];
                        }
                        

                    }
                }
            }
        }

    }
}

echo json_encode($response);
?>