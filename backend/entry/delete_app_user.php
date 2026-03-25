<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$session_user_id = (int)$_SESSION["user_id"];
$user_id = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : $session_user_id;

if ($user_id !== $session_user_id && (($_SESSION["user_role"] ?? "") !== "Starš - admin")) {
    header("Location: ../dashboard/dashboard.php");
    exit;
}

$conn->begin_transaction();

try {
    $sql = "DELETE FROM who_is_doing_it WHERE app_user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $sql = "DELETE FROM task_history WHERE app_user_id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $family_id);
    $stmt->execute();
    $stmt->close();

    $sql = "DELETE FROM event WHERE created_by_app_user_id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $family_id);
    $stmt->execute();
    $stmt->close();

    $sql = "DELETE FROM task WHERE created_by_app_user_id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $family_id);
    $stmt->execute();
    $stmt->close();

    $sql = "DELETE FROM shopping_list WHERE app_user_id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $family_id);
    $stmt->execute();
    $stmt->close();

    $sql = "DELETE FROM food_location WHERE app_user_id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $family_id);
    $stmt->execute();
    $stmt->close();

    $sql = "DELETE FROM app_user WHERE id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $family_id);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new Exception("Brisanje uporabnika ni uspelo.");
    }

    $stmt->close();
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    exit("Brisanje uporabnika ni uspelo.");
}

if ($user_id === $session_user_id) {
    header("Location: ../entry/logout.php");
    exit;
}

header("Location: ../admin_page/admin_page.php");
exit;
?>
