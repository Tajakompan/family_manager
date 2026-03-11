<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}


$family_id  = (int)$_SESSION["family_id"];
$storage_id = (int)($_GET['storage_id'] ?? 0);


$sql = "SELECT
            f.id AS food_location_id,
            p.name,
            p.amount,
            p.unit,
            f.quantity,
            COALESCE(pc.name, 'brez kategorije') AS category,
            f.expires_on,
            f.status
        FROM food_location f
        INNER JOIN product p ON f.product_id = p.id
        LEFT JOIN product_category pc ON pc.id = p.product_category_id
        WHERE f.family_id = ? AND f.storage_location_id = ? AND datediff(expires_on, current_date()) < 4
        ORDER BY 
            f.expires_on IS NULL, 
            f.expires_on ASC;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $storage_id);
$stmt->execute();
$result = $stmt->get_result();

$out = [];
while ($k = $result->fetch_assoc()) {
    $out[] = [
        "food_location_id" => (int)$k["food_location_id"],
        "name" => $k["name"],
        "amount" => (float)$k["amount"],
        "unit" => $k["unit"],
        "quantity" => (int)$k["quantity"],
        "category" => $k["category"],
        "expires_on" => $k["expires_on"],
        "status" => $k["status"]
    ];
}

$stmt->close();
header("Content-Type: application/json; charset=utf-8");
echo json_encode($out);
?>
