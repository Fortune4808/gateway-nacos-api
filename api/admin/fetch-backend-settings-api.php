<?php include '../config/connection.php'; ?>

<?php
if($apiKey!=$expected_api_key){
    $response['code']=100;
    $response['success']=false;
    $response['message']="ACCESS DENIED! You are not authiorized to call this API";
}else{
    $access_key=trim($_GET['access_key']);
    $fetch=$callclass->_validate_accesskey($conn,$access_key);
    $array = json_decode($fetch, true);
    $check=$array[0]['check'];
   
$response['check']=$check;
if($check==0){ 
        $response['response']=101; 
        $response['success']=false;
        $response['message']='Invalid AccessToken. Please LogIn Again.'; 
}else{

    $query=mysqli_query($conn,"SELECT a.*, FORMAT(a.nacos_fee_amount, 2) AS 'formatted_nacos_fee_amount', FORMAT(a.departmental_fee_amount, 2) AS 'formatted_departmental_fee_amount' FROM setup_system_setting_tab a")or die (mysqli_error($conn));
    $response['response']=102;
    $response['success']=true;
    $response['data'] = mysqli_fetch_all($query, MYSQLI_ASSOC);
}	
}

echo json_encode($response);
?>