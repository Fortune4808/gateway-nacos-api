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
    $login_staff_id=$array[0]['staff_id'];
    $login_role_id=$array[0]['role_id'];
    $login_department_id=$array[0]['department_id'];
  
    $response['check']=$check;

    if($check==0){
        $response['response']=101; 
        $response['success']=false;
        $response['message']='Invalid AccessToken. Please LogIn Again.'; 
    }else{

        $alert_id=($_POST['alert_id']);
        $start_date=($_POST['start_date']);
        $end_date=($_POST['end_date']);
        $search_txt=($_POST['search_txt']);
        
        $search_like="(alert_id like '%$search_txt%' OR 
        fullname like '%$search_txt%' OR
        alert_description like '%$search_txt%' OR
        system_used like '%$search_txt%' OR
        ip_address like '%$search_txt%')";
        
        if ($alert_id == '') {
           
            if (($start_date=='') || ($end_date=='')) {
                if ($login_role_id<3){
                    $query = mysqli_query($conn, "SELECT LEFT(a.alert_description, 65) AS 'short_alert_description', a.* FROM alert_tab a WHERE a.role_id<'$login_role_id' AND a.department_id='$login_department_id' AND $search_like") or die(mysqli_error($conn));
        
                    if (mysqli_num_rows($query) > 0) {
                        $response['response'] = 102;
                        $response['success'] = true;
                        $response['data'] = [];
                        
                        while ($row = mysqli_fetch_assoc($query)) {
                            $response['data'][] = $row;
                        }
                    } else {
                        $response['response'] = 103;
                        $response['success'] = false;
                        $response['message'] = "NO RECORD FOUND!!!";
                    }
                }else{
                    $query = mysqli_query($conn, "SELECT LEFT(a.alert_description, 65) AS 'short_alert_description', a.* FROM alert_tab a WHERE $search_like") or die(mysqli_error($conn));
            
                    if (mysqli_num_rows($query) > 0) {
                        $response['response'] = 104;
                        $response['success'] = true;
                        $response['data'] = [];
                        
                        while ($row = mysqli_fetch_assoc($query)) {
                            $response['data'][] = $row;
                        }
                    } else {
                        $response['response'] = 105;
                        $response['success'] = false;
                        $response['message'] = "NO RECORD FOUND!!!";
                    }
                }
               
            }else{
                if ($login_role_id<3){
                    $query = mysqli_query($conn, "SELECT LEFT(a.alert_description, 65) AS 'short_alert_description', a.* FROM alert_tab a WHERE a.role_id<'$login_role_id' AND a.department_id='$login_department_id' AND $search_like AND DATE(a.created_time) BETWEEN '$start_date' AND '$end_date'") or die(mysqli_error($conn));
        
                    if (mysqli_num_rows($query) > 0) {
                        $response['response'] = 106;
                        $response['success'] = true;
                        $response['data'] = [];
                        
                        while ($row = mysqli_fetch_assoc($query)) {
                            $response['data'][] = $row;
                        }
                    } else {
                        $response['response'] = 107;
                        $response['success'] = false;
                        $response['message'] = "NO RECORD FOUND!!!";
                    }
                }else{
                    $query = mysqli_query($conn, "SELECT LEFT(a.alert_description, 65) AS 'short_alert_description', a.* FROM alert_tab a WHERE $search_like AND DATE(a.created_time) BETWEEN '$start_date' AND '$end_date'") or die(mysqli_error($conn));
            
                    if (mysqli_num_rows($query) > 0) {
                        $response['response'] = 108;
                        $response['success'] = true;
                        $response['data'] = [];
                        
                        while ($row = mysqli_fetch_assoc($query)) {
                            $response['data'][] = $row;
                        }
                    } else {
                        $response['response'] = 109;
                        $response['success'] = false;
                        $response['message'] = "NO RECORD FOUND!!!";
                    }
                }
            }
            
        } else {
            $query = mysqli_query($conn, "SELECT * FROM `alert_tab` WHERE alert_id='$alert_id'") or die(mysqli_error($conn));
        
            if (mysqli_num_rows($query) > 0) {
                $response['response']=110;
                $response['success'] = true;
                while($row=mysqli_fetch_assoc($query)){
                    $response['data'] = $row;
                }
  
            } else {
                $response['response']=111;
                $response['success'] = false;
                $response['message'] = "NO RECORD FOUND!!!";
            }
        }

    }
}

echo json_encode($response);
?>