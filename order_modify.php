<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);
if (isset($_GET['p_order_id'])) {
    $p_order_id = $_GET['p_order_id'];
    $query = "SELECT * FROM Placing_order WHERE p_order_id = $p_order_id";
    $result = mysqli_query($conn, $query);
    $order = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn);
    try {
        $p_order_id = $_POST['p_order_id'];
        $restaurant_id = $_POST['restaurant_id'];
        $ingredient_id = $_POST['ingredient_id'];
        $mart_id = $_POST['mart_id'];
        $add_num = $_POST['add_num'];

        $query = "UPDATE Placing_order SET restaurant_id = '$restaurant_id', ingredient_id = '$ingredient_id', mart_id = '$mart_id', add_num = $add_num WHERE p_order_id = $p_order_id";
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Query Error: ' . mysqli_error($conn));
        }
        mysqli_commit($conn);
        s_msg('발주가 성공적으로 수정되었습니다.');
        echo "<script>location.replace('order_management.php?restaurant_id=$restaurant_id');</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        msg($e->getMessage());
    }
}

$restaurants = mysqli_query($conn, "SELECT * FROM Restaurant");
$ingredients = mysqli_query($conn, "SELECT * FROM Inventory");
$marts = mysqli_query($conn, "SELECT * FROM Mart");
?>
<div class="container">
    <h2>발주 수정 세부</h2>
    <form method="post">
        <input type="hidden" name="p_order_id" value="<?= $order['p_order_id'] ?>">
        <p>
            <label for="restaurant_id">음식점 선택</label>
            <select name="restaurant_id">
                <?php while ($row = mysqli_fetch_assoc($restaurants)) { ?>
                <option value="<?= $row['restaurant_id'] ?>" <?= $row['restaurant_id'] == $order['restaurant_id'] ? 'selected' : '' ?>><?= $row['restaurant_name'] ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="ingredient_id">식자재 선택</label>
            <select name="ingredient_id">
                <?php while ($row = mysqli_fetch_assoc($ingredients)) { ?>
                <option value="<?= $row['ingredient_id'] ?>" <?= $row['ingredient_id'] == $order['ingredient_id'] ? 'selected' : '' ?>><?= $row['ingredient_name'] ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="mart_id">마트 선택</label>
            <select name="mart_id">
                <?php while ($row = mysqli_fetch_assoc($marts)) { ?>
                <option value="<?= $row['mart_id'] ?>" <?= $row['mart_id'] == $order['mart_id'] ? 'selected' : '' ?>><?= $row['mart_name'] ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="add_num">수량</label>
            <input type="number" name="add_num" value="<?= $order['add_num'] ?>">
        </p>
        <button type="submit">발주 수정</button>
    </form>
</div>
<?php include("footer.php"); ?>
