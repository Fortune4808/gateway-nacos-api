<?php
include '../config/connection.php';

if ($apiKey != $expected_api_key) {
    $response = [
        'code' => 100,
        'success' => false,
        'message' => 'ACCESS DENIED! You are not authorized to call this API'
    ];
} else {
    $access_key = trim($_GET['access_key']);
    
    $fetch = $callclass->_validate_accesskey($conn, $access_key);
    $array = json_decode($fetch, true);
    $login_role_id = $array[0]['role_id'];
    $login_department_id = $array[0]['department_id'];
    $check = $array[0]['check'];
    $response['check'] = $check;

    if ($check == 0) {
        $response['response']=101; 
        $response['success']=false;
        $response['message']='Invalid AccessToken. Please LogIn Again.'; 
    } else {
       
        $student_id=trim($_POST['student_id']);
        $status_id=($_POST['status_id']);
        $department_id=($_POST['department_id']);
        $faculty_id=($_POST['faculty_id']);
        $level_id=($_POST['level_id']);
        $search_txt=($_POST['search_txt']);

        $search_like="(a.matric_number like '%$search_txt%' OR 
        b.department_name like '%$search_txt%' OR
        a.fullname like '%$search_txt%')";

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $items_per_page = 5;
        $offset = ($page - 1) * $items_per_page;

        $count_query = "SELECT COUNT(*) as total FROM student_tab";
        $count_result = mysqli_query($conn, $count_query);
        $total_student = mysqli_fetch_assoc($count_result)['total'];

        $total_pages = ceil($total_student / $items_per_page);

        if ($student_id==''){
            if ($login_role_id<3){
                $query = "SELECT a.*, b.department_name, c.faculty_name, d.level_name, e.session_year, f.gender_name, g1.status_name, h.programme_name, g2.status_name AS 'first_session_department_fee_status', g2.status_id AS 'first_session_department_fee_status_id', g3.status_name AS 'first_session_nacos_fee_status', g3.status_id AS 'first_session_nacos_fee_status_id', g4.status_name AS 'second_session_nacos_fee_status', g4.status_id AS 'second_session_nacos_fee_status_id', g5.status_name AS 'second_session_department_fee_status', g5.status_id AS 'second_session_department_fee_status_id', FORMAT(j.nacos_fee_amount_first_session, 2) AS 'nacos_fee_amount_first_session', FORMAT(j.nacos_fee_amount_second_session, 2) AS 'nacos_fee_amount_second_session', FORMAT(i.departmental_fee_amount_first_session, 2) AS 'departmental_fee_amount_first_session', FORMAT(i.departmental_fee_amount_second_session, 2) AS 'departmental_fee_amount_second_session', SUBSTRING_INDEX(a.fullname, ' ', 1) AS firstname, SUBSTRING_INDEX(a.fullname, ' ', -1) AS lastname, CASE WHEN LENGTH(a.fullname) - LENGTH(REPLACE(a.fullname, ' ', '')) > 1 THEN TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.fullname, ' ', -2), ' ', 1)) ELSE '' END AS middlename FROM student_tab a JOIN department_tab b ON b.department_id = a.department_id JOIN faculty_tab c ON c.faculty_id = b.faculty_id JOIN level_tab d ON d.level_id = a.level_id JOIN session_tab e ON e.session_id = a.session_id LEFT JOIN gender_tab f ON f.gender_id = a.gender_id JOIN setup_status_tab g1 ON g1.status_id = a.status_id JOIN programme_tab h ON h.programme_id = a.programme_id JOIN departmental_fee_tab i ON i.student_id = a.student_id JOIN setup_status_tab g2 ON g2.status_id = i.first_session_status_id JOIN nacos_fee_tab j ON j.student_id = a.student_id JOIN setup_status_tab g3 ON g3.status_id = j.first_session_status_id JOIN setup_status_tab g4 ON g4.status_id = j.second_session_status_id JOIN setup_status_tab g5 ON g5.status_id = i.second_session_status_id WHERE a.department_id='$login_department_id' AND a.status_id LIKE '%$status_id%' AND a.department_id LIKE '%$department_id%' AND c.faculty_id LIKE '%$faculty_id%' AND a.level_id LIKE '%$level_id%' AND $search_like LIMIT ?, ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ii', $offset, $items_per_page);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    $student_list = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $row['documentStoragePath'] = "$documentStoragePath/student_picture";
                        $student_list[] = $row;
                    }

                    $response = [
                        'response' => 102,
                        'success' => true,
                        'message' => 'Student list fetched successfully',
                        'data' => $student_list,
                        'pagination' => [
                            'current_page' => $page,
                            'total_student' => $total_student,
                            'total_pages' => $total_pages,
                            'next_page' => ($page < $total_pages) ? $page + 1 : null,
                            'prev_page' => ($page > 1) ? $page - 1 : null
                        ]
                    ];
                } else {
                    $response = [
                        'response' => 103,
                        'success' => false,
                        'message' => 'No records found'
                    ];
                }
                
            }else{
                $query = "SELECT a.*, b.department_name, c.faculty_name, d.level_name, e.session_year, f.gender_name, g1.status_name, h.programme_name, g2.status_name AS 'first_session_department_fee_status', g2.status_id AS 'first_session_department_fee_status_id', g3.status_name AS 'first_session_nacos_fee_status', g3.status_id AS 'first_session_nacos_fee_status_id', g4.status_name AS 'second_session_nacos_fee_status', g4.status_id AS 'second_session_nacos_fee_status_id', g5.status_name AS 'second_session_department_fee_status', g5.status_id AS 'second_session_department_fee_status_id', FORMAT(j.nacos_fee_amount_first_session, 2) AS 'nacos_fee_amount_first_session', FORMAT(j.nacos_fee_amount_second_session, 2) AS 'nacos_fee_amount_second_session', FORMAT(i.departmental_fee_amount_first_session, 2) AS 'departmental_fee_amount_first_session', FORMAT(i.departmental_fee_amount_second_session, 2) AS 'departmental_fee_amount_second_session', SUBSTRING_INDEX(a.fullname, ' ', 1) AS firstname, SUBSTRING_INDEX(a.fullname, ' ', -1) AS lastname, CASE WHEN LENGTH(a.fullname) - LENGTH(REPLACE(a.fullname, ' ', '')) > 1 THEN TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.fullname, ' ', -2), ' ', 1)) ELSE '' END AS middlename FROM student_tab a JOIN department_tab b ON b.department_id = a.department_id JOIN faculty_tab c ON c.faculty_id = b.faculty_id JOIN level_tab d ON d.level_id = a.level_id JOIN session_tab e ON e.session_id = a.session_id LEFT JOIN gender_tab f ON f.gender_id = a.gender_id JOIN setup_status_tab g1 ON g1.status_id = a.status_id JOIN programme_tab h ON h.programme_id = a.programme_id JOIN departmental_fee_tab i ON i.student_id = a.student_id JOIN setup_status_tab g2 ON g2.status_id = i.first_session_status_id JOIN nacos_fee_tab j ON j.student_id = a.student_id JOIN setup_status_tab g3 ON g3.status_id = j.first_session_status_id JOIN setup_status_tab g4 ON g4.status_id = j.second_session_status_id JOIN setup_status_tab g5 ON g5.status_id = i.second_session_status_id WHERE a.status_id LIKE '%$status_id%' AND a.department_id LIKE '%$department_id%' AND c.faculty_id LIKE '%$faculty_id%' AND a.level_id LIKE '%$level_id%' AND $search_like LIMIT ?, ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ii', $offset, $items_per_page);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    $student_list = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $row['documentStoragePath'] = "$documentStoragePath/student_picture";
                        $student_list[] = $row;
                    }

                    $response = [
                        'response' => 104,
                        'success' => true,
                        'message' => 'Student list fetched successfully',
                        'data' => $student_list,
                        'pagination' => [
                            'current_page' => $page,
                            'total_student' => $total_student,
                            'total_pages' => $total_pages,
                            'next_page' => ($page < $total_pages) ? $page + 1 : null,
                            'prev_page' => ($page > 1) ? $page - 1 : null
                        ]
                    ];
                } else {
                    $response = [
                        'response' => 105,
                        'success' => false,
                        'message' => 'No records found'
                    ];
                }
            }

        }else{
            $query = "SELECT a.*, b.department_name, c.faculty_name, d.level_name, e.session_year, f.gender_name, g1.status_name, h.programme_name, g2.status_name AS 'first_session_department_fee_status', g2.status_id AS 'first_session_department_fee_status_id', g3.status_name AS 'first_session_nacos_fee_status', g3.status_id AS 'first_session_nacos_fee_status_id', g4.status_name AS 'second_session_nacos_fee_status', g4.status_id AS 'second_session_nacos_fee_status_id', g5.status_name AS 'second_session_department_fee_status', g5.status_id AS 'second_session_department_fee_status_id', FORMAT(j.nacos_fee_amount_first_session, 2) AS 'nacos_fee_amount_first_session', FORMAT(j.nacos_fee_amount_second_session, 2) AS 'nacos_fee_amount_second_session', FORMAT(i.departmental_fee_amount_first_session, 2) AS 'departmental_fee_amount_first_session', FORMAT(i.departmental_fee_amount_second_session, 2) AS 'departmental_fee_amount_second_session', SUBSTRING_INDEX(a.fullname, ' ', 1) AS firstname, SUBSTRING_INDEX(a.fullname, ' ', -1) AS lastname, CASE WHEN LENGTH(a.fullname) - LENGTH(REPLACE(a.fullname, ' ', '')) > 1 THEN TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.fullname, ' ', -2), ' ', 1)) ELSE '' END AS middlename FROM student_tab a JOIN department_tab b ON b.department_id = a.department_id JOIN faculty_tab c ON c.faculty_id = b.faculty_id JOIN level_tab d ON d.level_id = a.level_id JOIN session_tab e ON e.session_id = a.session_id LEFT JOIN gender_tab f ON f.gender_id = a.gender_id JOIN setup_status_tab g1 ON g1.status_id = a.status_id JOIN programme_tab h ON h.programme_id = a.programme_id JOIN departmental_fee_tab i ON i.student_id = a.student_id JOIN setup_status_tab g2 ON g2.status_id = i.first_session_status_id JOIN nacos_fee_tab j ON j.student_id = a.student_id JOIN setup_status_tab g3 ON g3.status_id = j.first_session_status_id JOIN setup_status_tab g4 ON g4.status_id = j.second_session_status_id JOIN setup_status_tab g5 ON g5.status_id = i.second_session_status_id WHERE a.student_id=?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 's', $student_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $student_list = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['documentStoragePath'] = "$documentStoragePath/student_picture";
                    $student_list[] = $row;
                }

                $response = [
                    'response' => 106,
                    'success' => true,
                    'message' => 'Student list fetched successfully',
                    'data' => $student_list,
                ];
            } else {
                $response = [
                    'response' => 107,
                    'success' => false,
                    'message' => 'No records found'
                ];
            }
        }
    }
}

echo json_encode($response);
?>
