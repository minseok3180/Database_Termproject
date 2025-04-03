<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);
if (isset($_GET['dish_name']) && isset($_GET['restaurant_id'])) {
    $dish_name = $_GET['dish_name'];
    $restaurant_id = $_GET['restaurant_id'];
    $query = "SELECT * FROM Menu WHERE dish_name = '$dish_name' AND restaurant_id = '$restaurant_id'";
    $result = mysqli_query($conn, $query);
    $menu = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn);
    try {
        $restaurant_id = $_POST['restaurant_id'];
        $dish_name = $_POST['dish_name'];
        $price = $_POST['price'];
        $cooking_time = $_POST['cooking_time'];
        $portion = $_POST['portion'];

        $query = "UPDATE Menu SET price = $price, cooking_time = $cooking_time, portion = $portion WHERE dish_name = '$dish_name' AND restaurant_id = '$restaurant_id'";
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Query Error: ' . mysqli_error($conn));
        }
        mysqli_commit($conn);
        s_msg('메뉴가 성공적으로 수정되었습니다.');
        echo "<script>location.replace('menu_management.php');</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        msg($e->getMessage());
    }
}

$restaurants = mysqli_query($conn, "SELECT * FROM Restaurant");
?>
<div class="container">
    <h2>메뉴 수정 세부</h2>
    <form method="post">
        <input type="hidden" name="restaurant_id" value="<?= $menu['restaurant_id'] ?>">
        <p>
            <label for="dish_name">메뉴 이름</label>
            <input type="text" name="dish_name" value="<?= $menu['dish_name'] ?>" readonly>
        </p>
        <p>
            <label for="price">가격</label>
            <input type="number" name="price" value="<?= $menu['price'] ?>">
        </p>
        <p>
            <label for="cooking_time">조리 시간</label>
            <input type="number" name="cooking_time" value="<?= $menu['cooking_time'] ?>">
        </p>
        <p>
            <label for="portion">인분</label>
            <input type="number" name="portion" value="<?= $menu['portion'] ?>">
        </p>
        <button type="submit">메뉴 수정</button>
    </form>
</div>
<?php include("footer.php"); ?>
