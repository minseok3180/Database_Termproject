<?php
include "header.php";
include "config.php";
include "util.php";

$conn = dbconnect($host, $dbid, $dbpass, $dbname);

// 음식점 목록을 가져오는 쿼리 실행
$restaurants = mysqli_query($conn, "SELECT * FROM Restaurant");

// 메뉴 조회 처리
$selected_restaurant_id = "";
$query = "SELECT * FROM Menu NATURAL JOIN Restaurant";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restaurant_id']) && $_POST['restaurant_id'] !== "") {
    $selected_restaurant_id = mysqli_real_escape_string($conn, $_POST['restaurant_id']);
    $query .= " WHERE restaurant_id = '$selected_restaurant_id'";
}
$result = mysqli_query($conn, $query);
if (!$result) {
    die('Query Error: ' . mysqli_error($conn));
}
?>
<div class="container">
    <h2>메뉴 관리</h2>
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
            <th>메뉴 이름</th>
            <th>음식점</th>
            <th>가격</th>
            <th>조리 시간</th>
            <th>인분</th>
            <th>수정</th>
            <th>삭제</th>
        </tr>
        <?php if (!empty($result) && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['dish_name'] ?></td>
                <td><?= $row['restaurant_name'] ?></td>
                <td><?= $row['price'] ?></td>
                <td><?= $row['cooking_time'] ?></td>
                <td><?= $row['portion'] ?></td>
                <td><a href="menu_modify.php?dish_name=<?= $row['dish_name'] ?>&restaurant_id=<?= $row['restaurant_id'] ?>">수정</a></td>
                <td><a href="menu_delete.php?dish_name=<?= $row['dish_name'] ?>&restaurant_id=<?= $row['restaurant_id'] ?>" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a></td>
            </tr>
        <?php } } else { ?>
        <tr>
            <td colspan="7">조회된 메뉴가 없습니다.</td>
        </tr>
        <?php } ?>
    </table>
    <a href="menu_insert.php?restaurant_id=<?= $selected_restaurant_id ?>" class="btn btn-primary">추가</a>
</div>
<?php include("footer.php"); ?>
