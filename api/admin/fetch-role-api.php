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
    $login_role_id=$array[0]['role_id'];

$response['check'] = $check; 
if($check==0){ 
        $response['response']=101; 
        $response['success']=False;
        $response['message']='Invalid AccessToken. Please LogIn Again.'; 
}else{

    if ($login_role_id==3){//super admin			
        $query=mysqli_query($conn,"SELECT * FROM setup_role_tab WHERE role_id IN (1, 2, 3)")or die (mysqli_error($conn));
        $response['response']=102;
        $response['success']=true;
        $response['data'] = mysqli_fetch_all($query, MYSQLI_ASSOC);

    }elseif ($login_role_id==2){//admin		
        $query=mysqli_query($conn,"SELECT * FROM setup_role_tab WHERE role_id IN (1, 2)")or die (mysqli_error($conn));
        $response['response']=103;
        $response['success']=true;
        $response['data'] = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }else{
        //
    }

    
}
}

echo json_encode($response);
?>