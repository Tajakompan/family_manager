<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];

$task_id = (int)($_POST["task_id"] ?? 0);

if ($task_id > 0) {
  //najde vse userje k so delal task da bojo vsi dobil tocke
  $sql = "SELECT a.app_user_id as user_id,
                u.name as user_name,
                b.id as task_id,
                b.name as task_name,
                b.points as points
          FROM who_is_doing_it a
          INNER JOIN task b ON a.task_id = b.id
          INNER JOIN app_user u ON u.id = a.app_user_id
          WHERE a.task_id = ? AND b.family_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $task_id, $family_id);
  $stmt->execute();
  $results = $stmt->get_result();
  $stmt->close();

  while ($row = $results->fetch_assoc()) {
    $sql = "UPDATE app_user SET user_points = user_points + ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $row["points"], $row["user_id"]);

    $stmt->execute();
    $stmt->close();

    $sql = "INSERT INTO task_history
        (family_id, task_id, task_name, app_user_id, completed_by_name, points_earned, completed_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisisi", $family_id, $row["task_id"], $row["task_name"], $row["user_id"], $row["user_name"], $row["points"]);
    $stmt->execute();
    $stmt->close();

  }

  $stmt = $conn->prepare("DELETE FROM who_is_doing_it WHERE task_id = ?");
  $stmt->bind_param("i", $task_id);
  $stmt->execute();
  $stmt->close();


  $stmt = $conn->prepare("DELETE FROM task WHERE id = ? AND family_id = ?");
  $stmt->bind_param("ii", $task_id, $family_id);
  $stmt->execute();
  $stmt->close();
}

header("Location: ../tasks/tasks.php");
exit;

?>