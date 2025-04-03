<?php

include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if (isset($_GET['p_order_id']) && isset($_GET['restaurant_id'])) {
    mysqli_begin_transaction($conn);
    try {
        $p_order_id = (int)$_GET['p_order_id'];
        $restaurant_id = $_GET['restaurant_id'];
        $query = "DELETE FROM Placing_order WHERE p_order_id = $p_order_id";
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Query Error: ' . mysqli_error($conn));
        }
        mysqli_commit($conn);
        s_msg('발주가 성공적으로 취소되었습니다.');
        echo "<script>location.replace('order_management.php');</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        msg($e->getMessage());
    }
}
?>
