<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);
if (isset($_GET['dish_name']) && isset($_GET['restaurant_id'])) {
    mysqli_begin_transaction($conn);
    try {
        $dish_name = $_GET['dish_name'];
        $restaurant_id = $_GET['restaurant_id'];
        $query = "DELETE FROM Menu WHERE dish_name = '$dish_name' AND restaurant_id = '$restaurant_id'";
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Query Error: ' . mysqli_error($conn));
        }
        mysqli_commit($conn);
        s_msg('메뉴가 성공적으로 삭제되었습니다.');
        echo "<script>location.replace('menu_management.php?restaurant_id=$restaurant_id');</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        msg($e->getMessage());
    }
}
?>
