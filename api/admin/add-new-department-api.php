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
    $login_role_id = $array[0]['role_id'];

    $response['check'] = $check; 

    if ($login_role_id>2){
        if($check == 0) { 
            $response['response'] = 101; 
            $response['success'] = false;
            $response['message'] = 'Invalid Access Token. Please Log In Again.';
        } else {
            $faculty_id = trim($_POST['faculty_id']);
            $department_name = strtoupper(trim($_POST['department_name']));
    
            if (empty($faculty_id) || empty($department_name)){
    
                $response = [
                    'response' => 102,
                    'success' => false,
                    'message' => "Fill all fields to continue."
                ];
    
            }else{

                $validate_text = $callclass->validateTextInput($department_name);

                if (!$validate_text){

                    $response = [
                        'response' => 103,
                        'success' => false,
                        'message' => "Please ensure there are no digits included in the department name input"
                    ];

                }else{

                    $sequence = $callclass->_get_sequence_count($conn, 'D');
                    $array = json_decode($sequence, true);
                    $no = $array[0]['no'];
                    $department_id = 'D'. $no;
        
                    $add_department=mysqli_prepare($conn, "INSERT INTO `department_tab`(`department_id`, `faculty_id`, `department_name`, `status_id`, `created_time`) VALUES(?, ?, ?, 1, NOW())");
                    mysqli_stmt_bind_param($add_department, 'sss', $department_id, $faculty_id, $department_name);
                    mysqli_stmt_execute($add_department);
        
                    $response = [
                        'response' => 104,
                        'success' => true,
                        'message' => "DEPARTMENT SUCCESSFULLY ADDED!"
                    ];
                }
    
            }
                            
        }
    }else{
        $response = [
            'response' => 105,
            'success' => false,
            'message' => "Access Denied!!!"
        ];
    }
}

echo json_encode($response);
?>
