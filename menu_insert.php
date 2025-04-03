<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn);
    try {
        $restaurant_id = $_POST['restaurant_id'];
        $dish_name = $_POST['dish_name'];
        $price = $_POST['price'];
        $cooking_time = $_POST['cooking_time'];
        $portion = $_POST['portion'];

        $query = "INSERT INTO Menu (restaurant_id, dish_name, price, cooking_time, portion) VALUES ('$restaurant_id', '$dish_name', $price, '$cooking_time', $portion)";
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Query Error: ' . mysqli_error($conn));
        }
        mysqli_commit($conn);
        s_msg('메뉴가 성공적으로 추가되었습니다.');
        echo "<script>location.replace('menu_management.php?restaurant_id=$restaurant_id');</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        msg($e->getMessage());
    }
}

$selected_restaurant_id = isset($_GET['restaurant_id']) ? $_GET['restaurant_id'] : "";
$restaurants = mysqli_query($conn, "SELECT * FROM Restaurant");
?>
<style>
    .form-group {
        display: flex;
        align-items: center;
        margin-bottom: 1em;
    }
    .form-group label {
        width: 100px;
        margin-right: 10px;
    }
    .form-group input,
    .form-group select {
        flex: 1;
        max-width: 300px;
    }
    .form-group span {
        margin-left: 5px;
    }
    
</style>
<div class="container">
    <h2>메뉴 추가</h2>
    <form method="post">
        <div class="form-group">
            <label for="restaurant_id">음식점 선택</label>
            <select name="restaurant_id" id="restaurant_id">
                <?php while ($row = mysqli_fetch_assoc($restaurants)) { ?>
                <option value="<?= $row['restaurant_id'] ?>" <?= ($row['restaurant_id'] == $selected_restaurant_id) ? "selected" : "" ?>><?= $row['restaurant_name'] ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="dish_name">메뉴 이름</label>
            <input type="text" name="dish_name" id="dish_name" required>
        </div>
        <div class="form-group">
            <label for="price">가격</label>
            <input type="number" name="price" id="price" required>
            <span>원</span>
        </div>
        <div class="form-group">
            <label for="cooking_time">조리 시간</label>
            <input type="text" name="cooking_time" id="cooking_time" required>
            <span>분</span>
        </div>
        <div class="form-group">
            <label for="portion">인분</label>
            <input type="number" name="portion" id="portion" required>
            <span>인분</span>
        </div>
        <button type="submit" class="btn btn-primary">메뉴 추가</button>
    </form>
</div>
<?php include("footer.php"); ?>
