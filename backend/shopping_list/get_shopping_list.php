<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}
$family_id = (int)$_SESSION["family_id"];
$shop_id   = (int)($_GET["shop_id"] ?? 0);

$sql = "SELECT s.id, s.shop_id, p.name, p.amount, p.unit, s.quantity, s.necessity, s.purchased
        FROM shopping_list s
        INNER JOIN product p ON s.product_id = p.id
        WHERE s.family_id = ? 
        AND s.shop_id = ?
        AND (purchased = 0 OR purchased_on >= (NOW() - INTERVAL 12 HOUR))
        ORDER BY purchased ASC, purchased_on ASC, id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $shop_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

while ($k = $res->fetch_assoc()) {
    $id = (int)$k["id"];
    $purchased = (int)$k["purchased"];
    $qty = (int)$k["quantity"];
    $amount = rtrim(rtrim(number_format($k["amount"], 2, ".", ""), "0"), ".");
    $unit = trim((string)$k["unit"]);
    $amountUnit = $unit === "" ? $amount : ($amount . " " . $unit);

    echo "<tr data-row-id='{$id}' class='list_item ".($purchased ? "done" : "")."' data-shop-id='".(int)$k["shop_id"]."'>";
    echo "<td>".htmlspecialchars($k["name"])." <span class='item_measure'>".htmlspecialchars($amountUnit)."</span></td>";
    echo "<td class='ctr'><input type='number' class='qty-input' data-id='{$id}' min='1' step='1' value='{$qty}'> </td>";
    $necessity = (string)$k["necessity"];
    $necessitySort = 0;
    if ($necessity === "high") {
        $necessitySort = 3;
    } elseif ($necessity === "medium") {
        $necessitySort = 2;
    } elseif ($necessity === "low") {
        $necessitySort = 1;
    }
    echo "<td class='necessity ".htmlspecialchars($necessity)."' data-sort-value='{$necessitySort}'></td>";
    echo "<td class='ctr'> <input type='checkbox' class='check-item' data-id='{$id}' ".($purchased ? "checked" : "")."></td>";
    echo "</tr>";
}
?>
