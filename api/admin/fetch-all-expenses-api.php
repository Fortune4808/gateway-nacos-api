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
       
        $expenses_id=trim($_POST['expenses_id']);
        $search_txt=($_POST['search_txt']);

        $search_like="(expenses_decription like '%$search_txt%' OR
        expenses_id like '%$search_txt%' OR 
        expenses_items like '%$search_txt%')";

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $items_per_page = 5;
        $offset = ($page - 1) * $items_per_page;

        $count_query = "SELECT COUNT(*) as total FROM expenses_tab";
        $count_result = mysqli_query($conn, $count_query);
        $total_expenses = mysqli_fetch_assoc($count_result)['total'];

        $total_pages = ceil($total_expenses / $items_per_page);

        if ($expenses_id==''){
                
            $query = "SELECT * FROM `expenses_tab` WHERE $search_like LIMIT ?, ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ii', $offset, $items_per_page);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $expenses_list = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $expenses_list[] = $row;
                }

                $response = [
                    'response' => 102,
                    'success' => true,
                    'message' => 'Expenses list fetched successfully',
                    'data' => $expenses_list,
                    'pagination' => [
                        'current_page' => $page,
                        'total_student' => $total_expenses,
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
            $query = "SELECT * FROM `expenses_tab` WHERE expenses_id=?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 's', $expenses_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $expenses_list = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $expenses_list[] = $row;
                }

                $response = [
                    'response' => 104,
                    'success' => true,
                    'message' => 'expenses list fetched successfully',
                    'data' => $expenses_list,
                ];
            } else {
                $response = [
                    'response' => 105,
                    'success' => false,
                    'message' => 'No records found'
                ];
        
            }    
        }
    }
}

echo json_encode($response);
?>
