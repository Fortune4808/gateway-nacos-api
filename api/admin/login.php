<?php include '../config/connection.php'; ?>

<?php
if($apiKey!=$expected_api_key){

    $response = [
        'response' => 100,
        'success' => false,
        'message' => "ACCESS DENIED! You are not authorized to call this API"
    ];

}else{

    $email_address=trim($_POST['email_address']);
    $password=trim($_POST['password']);

    if (empty($email_address) || empty($password)) {

        $response = [
            'response' => 101,
            'success' => false,
            'message' => "LOGIN ERROR! Fill all fields to continue."
        ];

    }else{

        if(filter_var($email_address, FILTER_VALIDATE_EMAIL)){
            
            $login_email_query=mysqli_prepare($conn, "SELECT a.*, c.status_id AS 'department-status' FROM staff_tab a JOIN staff_department_tab b ON b.staff_id = a.staff_id JOIN department_tab c ON c.department_id = b.department_id WHERE a.`email_address`=?");
            mysqli_stmt_bind_param($login_email_query, 's', $email_address);
            mysqli_stmt_execute($login_email_query);
            $result = mysqli_stmt_get_result($login_email_query);

            if (mysqli_num_rows($result)>0){
                $fetch=mysqli_fetch_array($result);
                $hashedpassword=$fetch['password'];
                $status_id=$fetch['status_id'];
                $department_status=$fetch['department-status'];
                $staff_id=$fetch['staff_id'];
                $role_id=$fetch['role_id'];

            }if(password_verify($password, $hashedpassword)) {

                if($status_id==1){ /// check if the user is active

                   if ($department_status==1){ // check if user department is active
                        $access_key = password_hash($staff_id.date("Ymdhis"), PASSWORD_DEFAULT);
        
                        $update_accesskey = mysqli_prepare($conn, "UPDATE staff_tab SET access_key=?, last_login=NOW() WHERE staff_id=?");
                        mysqli_stmt_bind_param($update_accesskey, 'ss', $access_key, $staff_id);
                        mysqli_stmt_execute($update_accesskey);

                        $response = [
                            'response' => 102,
                            'success' => true,
                            'message' => "User successfully logged in",
                            'role_id' => $role_id,
                            'staff_id' => $staff_id,
                            'access_key' => $access_key
                        ];
                        
                   }elseif ($department_status==2){
                        $response = [
                            'response' => 103,
                            'success' => false,
                            'message' => "Department has been Suspended"
                        ];

                   }else{
                        $response = [
                            'response' => 104,
                            'success' => false,
                            'message' => "Department still on Pending"
                        ];
                   }
            
                }elseif ($status_id==2){
                    $response = [
                        'response' => 105,
                        'success' => false,
                        'message' => "Account Suspended"
                    ];

                }else{

                    $response = [
                        'response' => 106,
                        'success' => false,
                        'message' => "Account still on Pending"
                    ];
                }

            }else{

                $response = [
                    'response' => 107,
                    'success' => false,
                    'message' => "Invalid Login Parameters"
                ];
            }

        }else{

            $response = [
                'response' => 108,
                'success' => false,
                'message' => "Invalid Email Format"
            ];
        }
    
    }

      
}

echo json_encode($response);
?>