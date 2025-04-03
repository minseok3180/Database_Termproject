<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if (isset($_GET['p_order_id']) && isset($_GET['restaurant_id']) && !empty($_GET['restaurant_id'])) {
    $p_order_id = mysqli_real_escape_string($conn, $_GET['p_order_id']);
    $restaurant_id = mysqli_real_escape_string($conn, $_GET['restaurant_id']);

    mysqli_begin_transaction($conn);

    try {
        // 발주 정보 가져오기
        $order_query = "SELECT * FROM Placing_order WHERE p_order_id = '$p_order_id'";
        $order_result = mysqli_query($conn, $order_query);
        if (!$order_result) {
            throw new Exception('Order Query Error: ' . mysqli_error($conn));
        }
        $order = mysqli_fetch_assoc($order_result);
        if (!$order) {
            throw new Exception('Order not found for p_order_id: ' . $p_order_id);
        }

        $ingredient_id = $order['ingredient_id'];
        $add_num = $order['add_num'];

        // Inventory에 해당 식자재가 있는지 확인
        $check_inventory_query = "SELECT * FROM Inventory WHERE ingredient_id = '$ingredient_id' AND restaurant_id = '$restaurant_id'";
        $check_inventory_result = mysqli_query($conn, $check_inventory_query);
        if (!$check_inventory_result) {
            throw new Exception('Check Inventory Query Error: ' . mysqli_error($conn));
        }

        // 식자재가 Inventory에 없으면 새로 추가, 있으면 업데이트
        if (mysqli_num_rows($check_inventory_result) == 0) {
            // 기존 식자재 정보 가져오기
            $ingredient_info_query = "SELECT ingredient_name, ingredient_type FROM Inventory WHERE ingredient_id = '$ingredient_id' LIMIT 1";
            $ingredient_info_result = mysqli_query($conn, $ingredient_info_query);
            if (!$ingredient_info_result) {
                throw new Exception('Ingredient Info Query Error: ' . mysqli_error($conn));
            }
            $ingredient_info = mysqli_fetch_assoc($ingredient_info_result);
            $ingredient_name = $ingredient_info['ingredient_name'];
            $ingredient_type = $ingredient_info['ingredient_type'];

            $insert_inventory_query = "INSERT INTO Inventory (ingredient_id, ingredient_name, ingredient_type, ingredient_num, restaurant_id) VALUES ('$ingredient_id', '$ingredient_name', '$ingredient_type', $add_num, '$restaurant_id')";
            if (!mysqli_query($conn, $insert_inventory_query)) {
                throw new Exception('Insert Inventory Query Error: ' . mysqli_error($conn));
            }
        } else {
            $update_query = "UPDATE Inventory SET ingredient_num = ingredient_num + $add_num WHERE ingredient_id = '$ingredient_id' AND restaurant_id = '$restaurant_id'";
            if (!mysqli_query($conn, $update_query)) {
                throw new Exception('Update Inventory Query Error: ' . mysqli_error($conn));
            }
        }

        // 발주 삭제
        $delete_query = "DELETE FROM Placing_order WHERE p_order_id = '$p_order_id'";
        if (!mysqli_query($conn, $delete_query)) {
            throw new Exception('Delete Order Query Error: ' . mysqli_error($conn));
        }

        mysqli_commit($conn);
        s_msg('발주가 성공적으로 반영되었습니다.');
        echo "<script>location.replace('inventory_management.php?restaurant_id=$restaurant_id');</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        msg($e->getMessage());
        echo "<script>location.replace('inventory_management.php');</script>";
    }
} else {
    msg('유효한 restaurant_id가 전달되지 않았습니다.');
    echo "<script>location.replace('inventory_management.php');</script>";
}
?>
