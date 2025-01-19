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

        $staff_id=($_POST['staff_id']);
        $status_id=($_POST['status_id']);
        $department_id=($_POST['department_id']);
        $faculty_id=($_POST['faculty_id']);
        $search_txt=($_POST['search_txt']);
        
        $search_like="(a.staff_id like '%$search_txt%' OR 
        fullname like '%$search_txt%' OR
        email_address like '%$search_txt%' OR
        department_name like '%$search_txt%' OR
        faculty_name like '%$search_txt%' OR
        mobile_no like '%$search_txt%')";
        
        if ($staff_id == '') {
           if ($login_role_id<3){
                $query = mysqli_query($conn, "SELECT a.*, b.role_name, c.position_name, e.department_name, f.faculty_name, g.gender_name, h.status_name, SUBSTRING_INDEX(a.fullname, ' ', 1) AS firstname, SUBSTRING_INDEX(a.fullname, ' ', -1) AS lastname, CASE WHEN LENGTH(a.fullname) - LENGTH(REPLACE(a.fullname, ' ', '')) > 1 THEN TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.fullname, ' ', -2), ' ', 1)) ELSE '' END AS middlename FROM staff_tab a JOIN setup_role_tab b ON b.role_id = a.role_id JOIN setup_position_tab c ON c.position_id = a.position_id JOIN staff_department_tab d ON d.staff_id = a.staff_id JOIN department_tab e ON e.department_id = d.department_id JOIN faculty_tab f ON f.faculty_id = e.faculty_id LEFT JOIN gender_tab g ON g.gender_id = a.gender_id JOIN setup_status_tab h ON h.status_id = a.status_id WHERE a.status_id LIKE '%$status_id%' AND e.department_id LIKE '%$department_id%' AND f.faculty_id LIKE '%$faculty_id%' AND a.role_id<'$login_role_id' AND e.department_id='$login_department_id' AND $search_like") or die(mysqli_error($conn));
            
                if (mysqli_num_rows($query) > 0) {
                    $response['response'] = 102;
                    $response['success'] = true;
                    $response['data'] = [];
                    
                    while ($row = mysqli_fetch_assoc($query)) {
                        $row['documentStoragePath'] = "$documentStoragePath/staff_picture";
                        $response['data'][] = $row;
                    }
                } else {
                    $response['response'] = 103;
                    $response['success'] = false;
                    $response['message'] = "NO RECORD FOUND!!!";
                }
           }else{
                $query = mysqli_query($conn, "SELECT a.*, b.role_name, c.position_name, e.department_name, f.faculty_name, g.gender_name, h.status_name, SUBSTRING_INDEX(a.fullname, ' ', 1) AS firstname, SUBSTRING_INDEX(a.fullname, ' ', -1) AS lastname, CASE WHEN LENGTH(a.fullname) - LENGTH(REPLACE(a.fullname, ' ', '')) > 1 THEN TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.fullname, ' ', -2), ' ', 1)) ELSE '' END AS middlename FROM staff_tab a JOIN setup_role_tab b ON b.role_id = a.role_id JOIN setup_position_tab c ON c.position_id = a.position_id JOIN staff_department_tab d ON d.staff_id = a.staff_id JOIN department_tab e ON e.department_id = d.department_id JOIN faculty_tab f ON f.faculty_id = e.faculty_id LEFT JOIN gender_tab g ON g.gender_id = a.gender_id JOIN setup_status_tab h ON h.status_id = a.status_id WHERE a.status_id LIKE '%$status_id%' AND e.department_id LIKE '%$department_id%' AND f.faculty_id LIKE '%$faculty_id%' AND a.role_id<'$login_role_id' AND $search_like") or die(mysqli_error($conn));
            
                if (mysqli_num_rows($query) > 0) {
                    $response['response'] = 104;
                    $response['success'] = true;
                    $response['data'] = [];
                    
                    while ($row = mysqli_fetch_assoc($query)) {
                        $row['documentStoragePath'] = "$documentStoragePath/staff_picture";
                        $response['data'][] = $row;
                    }
                } else {
                    $response['response'] = 105;
                    $response['success'] = false;
                    $response['message'] = "NO RECORD FOUND!!!";
                }
           }
            
        } else {
            $query = mysqli_query($conn, "SELECT a.*, b.role_name, c.position_name, e.department_name, f.faculty_name, g.gender_name, h.status_name, SUBSTRING_INDEX(a.fullname, ' ', 1) AS firstname, SUBSTRING_INDEX(a.fullname, ' ', -1) AS lastname, CASE WHEN LENGTH(a.fullname) - LENGTH(REPLACE(a.fullname, ' ', '')) > 1 THEN TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.fullname, ' ', -2), ' ', 1)) ELSE '' END AS middlename FROM staff_tab a JOIN setup_role_tab b ON b.role_id = a.role_id JOIN setup_position_tab c ON c.position_id = a.position_id JOIN staff_department_tab d ON d.staff_id = a.staff_id JOIN department_tab e ON e.department_id = d.department_id JOIN faculty_tab f ON f.faculty_id = e.faculty_id LEFT JOIN gender_tab g ON g.gender_id = a.gender_id JOIN setup_status_tab h ON h.status_id = a.status_id WHERE a.staff_id='$staff_id'") or die(mysqli_error($conn));
        
            if (mysqli_num_rows($query) > 0) {
                $response['response']=106;
                $response['success'] = true;
                while($row=mysqli_fetch_assoc($query)){
                    $row['documentStoragePath'] = "$documentStoragePath/staff_picture";
                    $response['data'] = $row;
                }
  
            } else {
                $response['response']=107;
                $response['success'] = false;
                $response['message'] = "NO RECORD FOUND!!!";
            }
        }

    }
}

echo json_encode($response);
?>