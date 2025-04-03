<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn);
    try {
        $restaurant_id = $_POST['restaurant_id'];
        $ingredient_id = $_POST['ingredient_id'];
        $mart_id = $_POST['mart_id'];
        $add_num = $_POST['add_num'];

        $query = "INSERT INTO Placing_order (restaurant_id, ingredient_id, mart_id, add_num, p_order_date) VALUES ('$restaurant_id', '$ingredient_id', '$mart_id', $add_num, NOW())";
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Query Error: ' . mysqli_error($conn));
        }
        mysqli_commit($conn);
        s_msg('발주가 성공적으로 추가되었습니다.');
		echo "<script>location.replace('order_management.php?restaurant_id=$restaurant_id');</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        msg($e->getMessage());
    }
}

$selected_restaurant_id = isset($_GET['restaurant_id']) ? $_GET['restaurant_id'] : "";
$restaurants = mysqli_query($conn, "SELECT * FROM Restaurant");
$ingredients = mysqli_query($conn, "SELECT * FROM Inventory");
$marts = mysqli_query($conn, "SELECT * FROM Mart");
?>
<div class="container">
    <h2>발주 추가</h2>
    <form method="post">
        <p>
            <label for="restaurant_id">음식점 선택</label>
            <select name="restaurant_id" id="restaurant_id">
                <?php while ($row = mysqli_fetch_assoc($restaurants)) { ?>
                <option value="<?= $row['restaurant_id'] ?>" <?= ($row['restaurant_id'] == $selected_restaurant_id) ? "selected" : "" ?>><?= $row['restaurant_name'] ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="ingredient_id">식자재 선택</label>
            <select name="ingredient_id">
                <?php while ($row = mysqli_fetch_assoc($ingredients)) { ?>
                <option value="<?= $row['ingredient_id'] ?>"><?= $row['ingredient_name'] ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="mart_id">마트 선택</label>
            <select name="mart_id">
                <?php while ($row = mysqli_fetch_assoc($marts)) { ?>
                <option value="<?= $row['mart_id'] ?>"><?= $row['mart_name'] ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="add_num">수량</label>
            <input type="number" name="add_num">
        </p>
        <button type="submit">발주 추가</button>
    </form>
</div>
<?php include("footer.php"); ?>
