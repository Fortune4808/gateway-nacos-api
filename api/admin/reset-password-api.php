<?php
include '../config/connection.php';

if ($apiKey != $expected_api_key) {
    $response = [
        'response' => 100,
        'success' => false,
        'message' => "ACCESS DENIED! You are not authorized to call this API"
    ];
} else {
    $email_address = trim($_POST['email_address']);

    if (empty($email_address)) {
        $response = [
            'response' => 101,
            'success' => false,
            'message' => "Enter your Email Address to continue!"
        ];
    } else {
        if (filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
            $query = mysqli_prepare($conn, "SELECT * FROM `staff_tab` WHERE email_address = ?");
            mysqli_stmt_bind_param($query, 's', $email_address);
            mysqli_stmt_execute($query);
            $result = mysqli_stmt_get_result($query);
            mysqli_stmt_close($query);

            if (mysqli_num_rows($result) > 0) {
                $success = mysqli_fetch_array($result); 
                $staff_id = $success['staff_id']; 
                $fullname = $success['fullname']; 
                $email_address = $success['email_address']; 
                $status_id = $success['status_id']; 

                if ($status_id == 1) {
                    $otp = rand(111111, 999999);
                    $expires_at = date('Y-m-d H:i:s', time() + 600);
                    $query = mysqli_prepare($conn, "UPDATE staff_tab SET otp=?, otp_expiry_time=? WHERE staff_id =?");
                    mysqli_stmt_bind_param($query, 'iss', $otp, $expires_at, $staff_id);
                    mysqli_stmt_execute($query);
                    mysqli_stmt_close($query);

                    $response = [
                        'response' => 102,
                        'success' => true,
                        'message' => "An OTP has been sent to your Email Address!",
                        'staff_id' => $staff_id,
                        'fullname' => $fullname,
                        'email_address' => $email_address
                    ];
                } else {
                    $response = [
                        'response' => 103,
                        'success' => false,
                        'message' => "Account Suspended!",
                    ];
                }
            } else {
                $response = [
                    'response' => 104,
                    'success' => false,
                    'message' => "Email Address not Found!"
                ];
            }
        } else {
            $response = [
                'response' => 105,
                'success' => false,
                'message' => "Invalid Email Address!"
            ];
        }
    }
}

echo json_encode($response);
?>
