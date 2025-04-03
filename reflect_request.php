<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if (isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

    mysqli_begin_transaction($conn);

    try {
        // 주문 정보 가져오기
        $order_query = "SELECT * FROM Orders WHERE order_id = '$order_id'";
        $order_result = mysqli_query($conn, $order_query);
        if (!$order_result) {
            throw new Exception('Order Query Error: ' . mysqli_error($conn));
        }
        $order = mysqli_fetch_assoc($order_result);
        if (!$order) {
            throw new Exception('Order not found for order_id: ' . $order_id);
        }

        // Using_for_cook 테이블에서 해당 주문의 ingredient_id와 quantity를 가져오기
        $using_query = "SELECT * FROM Using_for_cook WHERE order_id = '$order_id'";
        $using_result = mysqli_query($conn, $using_query);
        if (!$using_result) {
            throw new Exception('Using_for_cook Query Error: ' . mysqli_error($conn));
        }

        // 각 재료에 대해 인벤토리 업데이트
        while ($using = mysqli_fetch_assoc($using_result)) {
            $ingredient_id = $using['ingredient_id'];
            $quantity_needed = $using['quantity'];

            // Do 테이블을 통해 restaurant_id 가져오기
            $restaurant_query = "SELECT DISTINCT restaurant_id FROM Do WHERE order_id = '$order_id'";
            $restaurant_result = mysqli_query($conn, $restaurant_query);
            if (!$restaurant_result) {
                throw new Exception('Restaurant Query Error: ' . mysqli_error($conn));
            }

            while ($restaurant = mysqli_fetch_assoc($restaurant_result)) {
                $restaurant_id = $restaurant['restaurant_id'];

                // 해당 식자재가 존재하지 않거나, 재고가 부족한 경우 예외 처리
                $check_inventory_query = "SELECT * FROM Inventory WHERE ingredient_id = '$ingredient_id' AND restaurant_id = '$restaurant_id'";
                $check_inventory_result = mysqli_query($conn, $check_inventory_query);
                if (!$check_inventory_result) {
                    throw new Exception('Check Inventory Query Error: ' . mysqli_error($conn));
                }

                $inventory = mysqli_fetch_assoc($check_inventory_result);
                if (!$inventory || $inventory['ingredient_num'] < $quantity_needed) {
                    throw new Exception('식자재가 부족합니다: ' . $ingredient_id);
                }

                // 인벤토리 업데이트
                $update_query = "UPDATE Inventory SET ingredient_num = ingredient_num - $quantity_needed WHERE ingredient_id = '$ingredient_id' AND restaurant_id = '$restaurant_id'";
                if (!mysqli_query($conn, $update_query)) {
                    throw new Exception('Inventory Update Query Error: ' . mysqli_error($conn));
                }
            }
        }

        // Using_for_cook 테이블에서 주문 삭제
        $delete_using_query = "DELETE FROM Using_for_cook WHERE order_id = '$order_id'";
        if (!mysqli_query($conn, $delete_using_query)) {
            throw new Exception('Using_for_cook Delete Query Error: ' . mysqli_error($conn));
        }

        // 주문 삭제
        $delete_query = "DELETE FROM Orders WHERE order_id = '$order_id'";
        if (!mysqli_query($conn, $delete_query)) {
            throw new Exception('Order Delete Query Error: ' . mysqli_error($conn));
        }

        mysqli_commit($conn);
        s_msg('주문이 성공적으로 반영되었습니다.');
        echo "<script>location.replace('inventory_management.php');</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        msg($e->getMessage());
        echo "<script>location.replace('inventory_management.php');</script>";
    }
} else {
    msg('유효한 order_id가 전달되지 않았습니다.');
    echo "<script>location.replace('inventory_management.php');</script>";
}
?>
