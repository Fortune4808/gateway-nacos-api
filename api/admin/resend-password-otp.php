<?php
include '../config/connection.php';

$action = $_GET['action'];

if ($apiKey != $expected_api_key) {

    $response = [
        'response' => 100,
        'success' => false,
        'message' => "ACCESS DENIED! You are not authorized to call this API"
    ];

} else {

    $staff_id = trim($_POST['staff_id']);
    
    $otp = rand(111111,999999);
    $expires_at = date('Y-m-d H:i:s', time() + 600);
    $query = mysqli_prepare($conn, "UPDATE staff_tab SET otp=?, otp_expiry_time=? WHERE staff_id =?");
    mysqli_stmt_bind_param($query, 'iss', $otp, $expires_at, $staff_id);
    mysqli_stmt_execute($query);
    mysqli_stmt_close($query);

    $response = [
        'response' => 105,
        'success' => true,
        'message' => "OTP Resent Successfully!"
    ];
}

echo json_encode($response);
?>
