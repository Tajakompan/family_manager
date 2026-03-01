<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id   = (int)$_SESSION["user_id"];

$task_id   = (int)($_POST["task_id"] ?? 0);

if ($task_id > 0) {
  //najdi vse userje s tem taskom in tocke za ta task
  $sql = "SELECT a.app_user_id as user, b.points as points
          FROM who_is_doing_it a
          INNER JOIN task b ON a.task_id = b.id
          WHERE task_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $task_id);
  $stmt->execute();
  $results = $stmt->get_result();
  $stmt->close();

  //vsem ki so ga opravljali dodeli tocke
  while ($row = $results->fetch_assoc()) {
    $sql = "UPDATE app_user SET user_points = user_points + ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $row["points"], $row["user"]);
    $stmt->execute();
    $stmt->close();
  }

  //brise iz who_is_doing_it
  $stmt = $conn->prepare("DELETE FROM who_is_doing_it WHERE task_id = ? AND app_user_id = ?");
  $stmt->bind_param("ii", $task_id, $user_id);
  $stmt->execute();
  $stmt->close();

  //brise iz task
  $stmt = $conn->prepare("DELETE FROM task WHERE id = ? AND family_id = ?");
  $stmt->bind_param("ii", $task_id, $family_id);
  $stmt->execute();
  $stmt->close();
}

header("Location: ../entry/login.php");
exit;
?>