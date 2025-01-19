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
        $response['success']=False;
        $response['message']='Invalid AccessToken. Please LogIn Again.'; 
}else{

    $query=mysqli_query($conn,"SELECT * FROM gender_tab")or die (mysqli_error($conn));
    $response['response']=102;
    $response['success']=true;
    $response['data'] = mysqli_fetch_all($query, MYSQLI_ASSOC); 
}	
}

echo json_encode($response);
?>