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
        $fullname = strtoupper(trim($_POST['fullname']));
        $mobile_no = (trim($_POST['mobile_no']));
        $email_address = strtolower(trim($_POST['email_address']));
        $role_id = (trim($_POST['role_id']));
        $status_id = (trim($_POST['status_id']));
        $post_id = (trim($_POST['post_id']));
        $faculty_id = (trim($_POST['faculty_id']));
        $department_id = (trim($_POST['department_id']));

        if ($login_role_id>1){

            if (empty($fullname) || empty($mobile_no) || empty($email_address) || empty($role_id) || empty($status_id) || empty($post_id) || empty($faculty_id) || empty($department_id)){

                $response = [
                    'response' => 101,
                    'success' => false,
                    'message' => "Fill all fields to continue."
                ];
    
            }else{

                $validate_text = $callclass->validateTextInput($fullname);
                $validate_phone = $callclass->validatePhoneNumber($mobile_no);

                if (!$validate_text){

                    $response = [
                        'response' => 103,
                        'success' => false,
                        'message' => "Please ensure there are no digits included in the fullname input"
                    ];

                }elseif (!$validate_phone){

                    $response = [
                        'response' => 104,
                        'success' => false,
                        'message' => "Please ensure you enter a valid phone numner"
                    ];
                    

                }else{
    
                    if (filter_var($email_address, FILTER_VALIDATE_EMAIL)){
        
                        if ($callclass->checkExistingField($conn, 'email_address', $email_address)){

                            $response = [
                                'response' => 105,
                                'success' => false,
                                'message' => "Email Address Entered is already Exist"
                            ];
                            
                        }elseif ($callclass->checkExistingField($conn, 'mobile_no', $mobileno)){

                            $response = [
                                'response' => 106,
                                'success' => false,
                                'message' => "Phone Number Entered is already Exist"
                            ];
    
                        }else{
    
                            $registrarData = $callclass->_get_staff($conn, $login_staff_id);
                            $registrarDataArray = json_decode($registrarData, true);
                            $registrar_staff_id = $registrarDataArray[0]['staff_id'];
                            $registrar_fullname = $registrarDataArray[0]['fullname'];

                            $sequence = $callclass->_get_sequence_count($conn, 'STF');
                            $array = json_decode($sequence, true);
                            $no = $array[0]['no'];
                            $staff_id = 'STF' . date("Ymdhis") . $no;

                            $hashedpassword = password_hash($staff_id, PASSWORD_BCRYPT);
                            $passport="friends.png";

                            $add_staff=mysqli_prepare($conn, "INSERT INTO `staff_tab`(`staff_id`, `role_id`, `position_id`, `status_id`, `fullname`, `email_address`, `mobile_no`, `passport`, `password`, `registered_fullname`, `registered_staff_id`, `created_time`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                            mysqli_stmt_bind_param($add_staff, 'siiisssssss', $staff_id, $role_id, $post_id, $status_id, $fullname, $email_address, $mobile_no, $passport, $hashedpassword, $registrar_fullname, $registrar_staff_id);
                            mysqli_stmt_execute($add_staff);

                            $add_department = mysqli_prepare($conn, "INSERT INTO `staff_department_tab`(`department_id`, `staff_id`, `created_time`) VALUES(?, ?, NOW())");
                            mysqli_stmt_bind_param($add_department, 'ss', $department_id, $staff_id);
                            mysqli_stmt_execute($add_department);

                            $alert_description='SUCCESS ALERT: A USER WHOSE NAME IS '. $registrar_fullname. ' ADDED NEW ADMINISTRATOR WITH ID: '. $staff_id;
                            $callclass->_add_alert($conn, $login_staff_id, $login_role_id, $registrar_fullname, $alert_description, $system_name, $ip_address);

                            $response = [
                                'response' => 107,
                                'success' => true,
                                'message' => "Staff Registration Successfully!"
                            ];
        
                        }
                        
                    }else{

                        $response = [
                            'response' => 108,
                            'success' => false,
                            'message' => "Email Address Entered is not Valid"
                        ];
                    }
                }
            }
        }
    }
}

echo json_encode($response);
?>
