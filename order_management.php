<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

// 음식점 목록을 가져오는 쿼리 실행
$restaurants = mysqli_query($conn, "SELECT * FROM Restaurant");
if (!$restaurants) {
    die('Query Error: ' . mysqli_error($conn));
}

// 발주 조회 처리
$selected_restaurant_id = "";
$query = "SELECT Placing_order.*, Restaurant.restaurant_name, Inventory.ingredient_name, Mart.mart_name
          FROM Placing_order
          INNER JOIN Restaurant ON Placing_order.restaurant_id = Restaurant.restaurant_id
          INNER JOIN Inventory ON Placing_order.ingredient_id = Inventory.ingredient_id
          INNER JOIN Mart ON Placing_order.mart_id = Mart.mart_id";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restaurant_id']) && $_POST['restaurant_id'] !== "") {
    $selected_restaurant_id = mysqli_real_escape_string($conn, $_POST['restaurant_id']);
    $query .= " WHERE Placing_order.restaurant_id = '$selected_restaurant_id'";
}

$result = mysqli_query($conn, $query);
if (!$result) {
    die('Query Error: ' . mysqli_error($conn) . ' Query: ' . $query);
}
?>
<div class="container">
    <h2>발주 관리</h2>
    <form method="post">
        <p>
            <label for="restaurant_id">음식점 선택</label>
            <select name="restaurant_id" id="restaurant_id">
                <option value="">전체</option>
                <?php while ($row = mysqli_fetch_assoc($restaurants)) { ?>
                <option value="<?= $row['restaurant_id'] ?>" <?= ($row['restaurant_id'] == $selected_restaurant_id) ? "selected" : "" ?>><?= $row['restaurant_name'] ?></option>
                <?php } ?>
            </select>
            <button type="submit">조회</button>
        </p>
    </form>
    <table class="table table-striped table-bordered">
        <tr>
            <th>발주 ID</th>
            <th>음식점</th>
            <th>식자재</th>
            <th>마트</th>
            <th>수량</th>
            <th>발주일</th>
            <th>수정</th>
            <th>삭제</th>
        </tr>
        <?php if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['p_order_id'] ?></td>
                <td><?= $row['restaurant_name'] ?></td>
                <td><?= $row['ingredient_name'] ?></td>
                <td><?= $row['mart_name'] ?></td>
                <td><?= $row['add_num'] ?></td>
                <td><?= $row['p_order_date'] ?></td>
                <td><a href="order_modify.php?p_order_id=<?= $row['p_order_id'] ?>">수정</a></td>
                <td><a href="order_delete.php?p_order_id=<?= $row['p_order_id'] ?>&restaurant_id=<?= $selected_restaurant_id ?>" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a></td>
            </tr>
        <?php } } else { ?>
        <tr>
            <td colspan="8">조회된 발주가 없습니다.</td>
        </tr>
        <?php } ?>
    </table>
    <a href="order_insert.php?restaurant_id=<?= $selected_restaurant_id ?>" class="btn btn-primary">추가</a>
</div>
<?php include("footer.php"); ?>
