<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id = (int)$_SESSION["user_id"];

$id = (int)($_POST["food_location_id"] ?? 0);
$storage_location = (int)($_POST["storage_id"] ?? 0);

$product_name = trim($_POST["product_name"] ?? "");
$product_amount_raw = str_replace(",", ".", trim($_POST["product_amount"] ?? ""));
$product_amount = (float)$product_amount_raw;
$product_unit = trim($_POST["product_unit"] ?? "");
$product_quantity = (int)($_POST["product_quantity"] ?? 0);
$product_category = (int)($_POST["product_category"] ?? 0);
$product_status = trim($_POST["product_status"] ?? "new");

$product_expires_on = trim($_POST["product_expires_on"] ?? "");
if ($product_expires_on === "") {
    $product_expires_on = null;
}

$redirect = "Location: food_storage.php?storage_id=" . $storage_location;

if (
    $id <= 0 ||
    $storage_location <= 0 ||
    $product_category <= 0 ||
    $product_name === "" ||
    $product_amount <= 0 ||
    $product_unit === "" ||
    $product_quantity <= 0 ||
    $product_status === ""
) {
    header($redirect);
    exit;
}

$sql = "SELECT id, purchased_on
        FROM food_location
        WHERE id = ? AND family_id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $family_id);
$stmt->execute();
$current_row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$current_row) {
    header($redirect);
    exit;
}

$sql = "SELECT id
        FROM storage_location
        WHERE id = ? AND family_id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $storage_location, $family_id);
$stmt->execute();
$storage_exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$storage_exists) {
    header($redirect);
    exit;
}

$sql = "SELECT id
        FROM product_category
        WHERE id = ? AND family_id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_category, $family_id);
$stmt->execute();
$category_exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$category_exists) {
    header($redirect);
    exit;
}

$current_purchased_on = $current_row["purchased_on"];

$conn->begin_transaction();
$ok = true;
$product_id = 0;
$existing_product = null;
$conflict = null;

$sql = "SELECT id
        FROM product
        WHERE family_id = ?
          AND LOWER(name) = LOWER(?)
          AND amount = ?
          AND LOWER(unit) = LOWER(?)
        LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $ok = false;
} else {
    $stmt->bind_param("isds", $family_id, $product_name, $product_amount, $product_unit);

    if (!$stmt->execute()) {
        $ok = false;
    } else {
        $existing_product = $stmt->get_result()->fetch_assoc();
    }

    $stmt->close();
}

if ($ok && $existing_product) {
    $product_id = (int)$existing_product["id"];

    $sql = "UPDATE product
            SET product_category_id = ?
            WHERE id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $ok = false;
    } else {
        $stmt->bind_param("iii", $product_category, $product_id, $family_id);

        if (!$stmt->execute()) {
            $ok = false;
        }

        $stmt->close();
    }
}

if ($ok && !$existing_product) {
    $sql = "INSERT INTO product (name, amount, unit, product_category_id, family_id)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $ok = false;
    } else {
        $stmt->bind_param("sdsii", $product_name, $product_amount, $product_unit, $product_category, $family_id);

        if (!$stmt->execute()) {
            $ok = false;
        } else {
            $product_id = (int)$conn->insert_id;
        }

        $stmt->close();
    }
}

if ($ok) {
    $sql = "SELECT id
            FROM food_location
            WHERE family_id = ?
              AND storage_location_id = ?
              AND product_id = ?
              AND id <> ?
              AND (
                  expires_on = ?
                  OR (expires_on IS NULL AND ? IS NULL)
              )
            LIMIT 1";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $ok = false;
    } else {
        $stmt->bind_param("iiiiss", $family_id, $storage_location, $product_id, $id, $product_expires_on, $product_expires_on);

        if (!$stmt->execute()) {
            $ok = false;
        } else {
            $conflict = $stmt->get_result()->fetch_assoc();
        }

        $stmt->close();
    }
}

if ($ok && $conflict) {
    $target_id = (int)$conflict["id"];

    $sql = "UPDATE food_location
            SET quantity = quantity + ?,
                status = ?,
                purchased_on = LEAST(purchased_on, ?)
            WHERE id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $ok = false;
    } else {
        $stmt->bind_param("isisi", $product_quantity, $product_status, $current_purchased_on, $target_id, $family_id);

        if (!$stmt->execute()) {
            $ok = false;
        }

        $stmt->close();
    }

    if ($ok) {
        $sql = "DELETE FROM food_location
                WHERE id = ? AND family_id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $ok = false;
        } else {
            $stmt->bind_param("ii", $id, $family_id);

            if (!$stmt->execute()) {
                $ok = false;
            }

            $stmt->close();
        }
    }
}

if ($ok && !$conflict) {
    $sql = "UPDATE food_location
            SET storage_location_id = ?,
                product_id = ?,
                expires_on = ?,
                quantity = ?,
                status = ?,
            WHERE id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $ok = false;
    } else {
        $stmt->bind_param("iisisii", $storage_location, $product_id, $product_expires_on, $product_quantity, $product_status, $id, $family_id);

        if (!$stmt->execute()) {
            $ok = false;
        }

        $stmt->close();
    }
}

if ($ok) {
    $conn->commit();
} else {
    $conn->rollback();
}

header($redirect);
exit;
?>
