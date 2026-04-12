<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$storage_id = (int)($_GET["storage_id"] ?? 0);

if ($storage_id <= 0) {
    http_response_code(400);
    exit;
}

$sql = "SELECT id
        FROM storage_location
        WHERE id = ? AND family_id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $storage_id, $family_id);
$stmt->execute();
$ok_storage = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ok_storage) {
    http_response_code(403);
    exit;
}

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
        WHERE f.family_id = ? AND f.storage_location_id = ?
        ORDER BY f.expires_on IS NULL, f.expires_on ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $storage_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $amount = rtrim(rtrim(number_format((float)$row["amount"], 2, ".", ""), "0"), ".");

    echo "<tr data-row-id='" . (int)$row["food_location_id"] . "'>";
    echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
    echo "<td>" . htmlspecialchars($amount) . "</td>";
    echo "<td>" . htmlspecialchars($row["unit"]) . "</td>";
    echo "<td>" . (int)$row["quantity"] . "</td>";
    echo "<td>" . htmlspecialchars($row["category"]) . "</td>";

    if (empty($row["expires_on"])) {
        echo "<td><em>ni datuma</em></td>";
    } else {
        $dt = new DateTime($row["expires_on"]);
        echo "<td>" . $dt->format("j. n. Y") . "</td>";
    }

    $status_label = match ($row["status"]) {
        "new" => "Novo",
        "open" => "Že odprto",
        "empty" => "Skoraj prazno",
        default => $row["status"],
    };
    echo "<td>" . htmlspecialchars($status_label) . "</td>";
    echo "</tr>";
}

$stmt->close();
?>
