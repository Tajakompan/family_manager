<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json; charset=utf-8");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    // =========================
    // 1) SESSION CHECK
    // =========================
    if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
        http_response_code(401);
        echo json_encode(["ok" => false, "error" => "Unauthorized"]);
        exit;
    }

    $family_id = (int)$_SESSION["family_id"];
    $user_id   = (int)$_SESSION["user_id"];

    // =========================
    // 2) READ POST DATA
    // =========================
    $list_id        = (int)($_POST["id"] ?? 0);
    $storage_id     = (int)($_POST["storage_id"] ?? 0);
    $expires_raw    = trim($_POST["expires_on"] ?? "");
    $expires_on     = ($expires_raw === "") ? null : $expires_raw;

    // DEBUG (lahko kasneje zbrišeš)
    file_put_contents(
        __DIR__ . "/debug.txt",
        date("c") . " POST=" . json_encode($_POST) . "\n",
        FILE_APPEND
    );

    if ($list_id <= 0 || $storage_id <= 0) {
        http_response_code(400);
        echo json_encode([
            "ok" => false,
            "error" => "Bad input",
            "parsed" => [
                "id" => $list_id,
                "storage_id" => $storage_id,
                "expires_on" => $expires_on
            ]
        ]);
        exit;
    }

    // =========================
    // 3) PREBERI IZ SHOPPING_LIST
    // =========================
    $sql = "SELECT product_id, quantity
            FROM shopping_list
            WHERE id = ? AND family_id = ? AND purchased = 1
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $list_id, $family_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$item) {
        http_response_code(404);
        echo json_encode([
            "ok" => false,
            "error" => "Item not found or not purchased"
        ]);
        exit;
    }

    $product_id = (int)$item["product_id"];
    $qty        = (int)$item["quantity"];

    if ($product_id <= 0 || $qty <= 0) {
        http_response_code(400);
        echo json_encode([
            "ok" => false,
            "error" => "Invalid product or quantity"
        ]);
        exit;
    }

    // =========================
    // 4) TRANSACTION START
    // =========================
    $conn->begin_transaction();

    $purchased_on   = date("Y-m-d");
    $product_status = "new";

    // =========================
    // 5) INSERT / UPDATE FOOD_LOCATION
    // =========================
    $sql = "INSERT INTO food_location
              (family_id, storage_location_id, product_id, purchased_on, expires_on, quantity, app_user_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              quantity = quantity + VALUES(quantity),
              status = VALUES(status),
              purchased_on = LEAST(purchased_on, VALUES(purchased_on)),
              app_user_id = VALUES(app_user_id)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iiissiis",
        $family_id,
        $storage_id,
        $product_id,
        $purchased_on,
        $expires_on,
        $qty,
        $user_id,
        $product_status
    );
    $stmt->execute();
    $stmt->close();

    // =========================
    // 6) DELETE IZ SHOPPING_LIST
    // =========================
    $sql = "DELETE FROM shopping_list
            WHERE id = ? AND family_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $list_id, $family_id);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new Exception("Delete failed");
    }

    $stmt->close();

    // =========================
    // 7) COMMIT
    // =========================
    $conn->commit();

    echo json_encode([
        "ok" => true,
        "message" => "Item successfully stored"
    ]);

} catch (Throwable $e) {

    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }

    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "error" => $e->getMessage()
    ]);
}
