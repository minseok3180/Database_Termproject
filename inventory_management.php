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

// 식자재, 주문, 발주 조회 처리
$selected_restaurant_id = "";
$ingredient_query = "SELECT Inventory.*, Restaurant.restaurant_name
                     FROM Inventory
                     INNER JOIN Restaurant ON Inventory.restaurant_id = Restaurant.restaurant_id";
$order_query = "SELECT Orders.order_id, GROUP_CONCAT(DISTINCT Restaurant.restaurant_name SEPARATOR ', ') AS restaurant_names, GROUP_CONCAT(DISTINCT Menu.dish_name SEPARATOR ', ') AS dish_names, Orders.total_price
                FROM Orders
                INNER JOIN Do ON Orders.order_id = Do.order_id
                INNER JOIN Menu ON Do.dish_name = Menu.dish_name AND Do.restaurant_id = Menu.restaurant_id
                INNER JOIN Restaurant ON Do.restaurant_id = Restaurant.restaurant_id";
$placing_order_query = "SELECT Placing_order.*, Restaurant.restaurant_name, Inventory.ingredient_name, Mart.mart_name
                        FROM Placing_order
                        INNER JOIN Restaurant ON Placing_order.restaurant_id = Restaurant.restaurant_id
                        INNER JOIN Inventory ON Placing_order.ingredient_id = Inventory.ingredient_id
                        INNER JOIN Mart ON Placing_order.mart_id = Mart.mart_id";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restaurant_id']) && $_POST['restaurant_id'] !== "") {
    $selected_restaurant_id = mysqli_real_escape_string($conn, $_POST['restaurant_id']);
    $ingredient_query .= " WHERE Inventory.restaurant_id = '$selected_restaurant_id'";
    $order_query .= " WHERE Do.restaurant_id = '$selected_restaurant_id'";
    $placing_order_query .= " WHERE Placing_order.restaurant_id = '$selected_restaurant_id'";
}

$order_query .= " GROUP BY Orders.order_id ORDER BY Orders.order_id";

$ingredient_result = mysqli_query($conn, $ingredient_query);
$order_result = mysqli_query($conn, $order_query);
$placing_order_result = mysqli_query($conn, $placing_order_query);

if (!$ingredient_result) {
    die('Ingredient Query Error: ' . mysqli_error($conn) . ' Query: ' . $ingredient_query);
}
if (!$order_result) {
    die('Order Query Error: ' . mysqli_error($conn) . ' Query: ' . $order_query);
}
if (!$placing_order_result) {
    die('Placing Order Query Error: ' . mysqli_error($conn) . ' Query: ' . $placing_order_query);
}
?>
<div class="container">
    <h2>식자재 관리</h2>
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
    
    <h3>현재 식자재</h3>
    <table class="table table-striped table-bordered">
        <tr>
            <th>식자재 ID</th>
            <th>식자재 이름</th>
            <th>식자재 종류</th>
            <th>수량</th>
            <th>음식점</th>
        </tr>
        <?php if (mysqli_num_rows($ingredient_result) > 0) {
            while ($row = mysqli_fetch_assoc($ingredient_result)) { ?>
            <tr>
                <td><?= $row['ingredient_id'] ?></td>
                <td><?= $row['ingredient_name'] ?></td>
                <td><?= $row['ingredient_type'] ?></td>
                <td><?= $row['ingredient_num'] ?></td>
                <td><?= $row['restaurant_name'] ?></td>
            </tr>
        <?php } } else { ?>
        <tr>
            <td colspan="5">조회된 식자재가 없습니다.</td>
        </tr>
        <?php } ?>
    </table>

    <h3>주문</h3>
    <table class="table table-striped table-bordered">
        <tr>
            <th>주문 ID</th>
            <th>음식점</th>
            <th>메뉴</th>
            <th>총 가격</th>
            <th>반영</th>
        </tr>
        <?php if (mysqli_num_rows($order_result) > 0) {
            while ($row = mysqli_fetch_assoc($order_result)) { ?>
            <tr>
                <td><?= $row['order_id'] ?></td>
                <td><?= $row['restaurant_names'] ?></td>
                <td><?= $row['dish_names'] ?></td>
                <td><?= $row['total_price'] ?></td>
                <td><a href="reflect_request.php?order_id=<?= $row['order_id'] ?>" onclick="return confirm('정말 반영하시겠습니까?')">반영</a></td>
            </tr>
        <?php } } else { ?>
        <tr>
            <td colspan="5">조회된 주문이 없습니다.</td>
        </tr>
        <?php } ?>
    </table>

    <h3>발주</h3>
    <table class="table table-striped table-bordered">
        <tr>
            <th>발주 ID</th>
            <th>음식점</th>
            <th>식자재</th>
            <th>마트</th>
            <th>수량</th>
            <th>발주일</th>
            <th>반영</th>
        </tr>
        <?php if (mysqli_num_rows($placing_order_result) > 0) {
            while ($row = mysqli_fetch_assoc($placing_order_result)) { ?>
            <tr>
                <td><?= $row['p_order_id'] ?></td>
                <td><?= $row['restaurant_name'] ?></td>
                <td><?= $row['ingredient_name'] ?></td>
                <td><?= $row['mart_name'] ?></td>
                <td><?= $row['add_num'] ?></td>
                <td><?= $row['p_order_date'] ?></td>
                <td><a href="reflect_order.php?p_order_id=<?= $row['p_order_id'] ?>&restaurant_id=<?= $row['restaurant_id'] ?>" onclick="return confirm('정말 반영하시겠습니까?')">반영</a></td>
            </tr>
        <?php } } else { ?>
        <tr>
            <td colspan="7">조회된 발주가 없습니다.</td>
        </tr>
        <?php } ?>
    </table>
</div>
<?php include("footer.php"); ?>
