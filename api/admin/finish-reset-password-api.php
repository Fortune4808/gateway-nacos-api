<?php
include '../config/connection.php';

if ($apiKey != $expected_api_key) {

    $response = [
        'response' => 100,
        'success' => false,
        'message' => "ACCESS DENIED! You are not authorized to call this API"
    ];

} else {

    $staff_id = trim($_POST['staff_id']);
    $otp = trim($_POST['otp']);
    $password=trim($_POST['password']);

    if (empty($otp) || empty($password)){

        $response = [
            'response' => 101,
            'success' => false,
            'message' => "Some Fields are Empty!"
        ];

    }else{

        $otpcheck=mysqli_prepare($conn,"SELECT a.*, b.department_id FROM staff_tab a JOIN staff_department_tab b ON a.staff_id = b.staff_id WHERE a.staff_id=? AND a.otp=?");
        mysqli_stmt_bind_param($otpcheck, 'si', $staff_id, $otp);
        mysqli_stmt_execute($otpcheck);
        $result = mysqli_stmt_get_result($otpcheck);
        $staffotp=mysqli_num_rows($result);
        mysqli_stmt_close($otpcheck);

        $hashedpassword = password_hash($password, PASSWORD_DEFAULT);

        if ($staffotp>0){
            $success = mysqli_fetch_array($result);
            $fullname = $success['fullname'];
            $role_id = $success['role_id'];
            $department_id = $success['department_id'];
            $otp_expiry_time = $success['otp_expiry_time'];

            if (time() > strtotime($otp_expiry_time)) {
                $response = [
                    'response' => 102,
                    'success' => false,
                    'message' => "OTP has expired!",
                ];
                
            }else{
                $query = mysqli_prepare($conn, "UPDATE `staff_tab` SET `password`=? WHERE `staff_id`=?");
                mysqli_stmt_bind_param($query, 'ss', $hashedpassword, $staff_id);
                mysqli_stmt_execute($query);

                $alert_description='SUCCESS ALERT: A USER WHOSE NAME IS '. $fullname. ' CHANGE HIS/HER PASSWORD';
                $sequence = $callclass->_add_alert($conn, $staff_id, $role_id, $department_id, $fullname, $alert_description, $system_name, $ip_address);
               
                $response = [
                    'response' => 103,
                    'success' => true,
                    'message' => "Password Reset Successfully!"
                ];
            }

        }else{

            $response = [
                'response' => 104,
                'success' => false,
                'message' => "Invalid OTP!"
            ];

        }
    }
}

echo json_encode($response);
?>
