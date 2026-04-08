<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/send_event_reminder_email.php";
file_put_contents(__DIR__ . '/cron_test.log', date('Y-m-d H:i:s') . " cron start\n", FILE_APPEND);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only");
}

$sql = "SELECT e.id, e.name, e.event_date, e.event_time, e.whole_day, e.location, e.description, e.reminder, u.email, u.name AS user_name, u.surname AS user_surname
        FROM event e
        INNER JOIN app_user u ON u.id = e.created_by_app_user_id
        WHERE e.reminder IS NOT NULL AND e.reminder_sent_at IS NULL
          AND u.email <> '' AND u.email_verified = 1 AND e.reminder <= NOW()
        ORDER BY e.reminder ASC
        LIMIT 50";

$result = $conn->query($sql);

if (!$result) exit("Query failed.\n");

file_put_contents(__DIR__ . '/cron_test.log', date('Y-m-d H:i:s') . " query executed\n", FILE_APPEND);

while ($row = $result->fetch_assoc()) {
    file_put_contents(__DIR__ . '/cron_test.log', date('Y-m-d H:i:s') . " processing event ID: " . $row["id"] . "\n", FILE_APPEND);
    $recipientName = trim(($row["user_name"] ?? "") . " " . ($row["user_surname"] ?? ""));

    $sent = sendEventReminderEmail(
        $row["email"],
        $recipientName,
        [
            "name" => $row["name"],
            "event_date" => $row["event_date"],
            "event_time" => $row["event_time"],
            "whole_day" => (int)$row["whole_day"],
            "location" => $row["location"],
            "description" => $row["description"]
        ]
    );

    if ($sent) {
        $updateSql = "UPDATE event
                    SET reminder_sent_at = NOW(),
                        reminder_last_attempt_at = NOW()
                    WHERE id = ? AND reminder_sent_at IS NULL";
    } else {
        $updateSql = "UPDATE event
                    SET reminder_last_attempt_at = NOW()
                    WHERE id = ?";
    }
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("i", $row["id"]);
    $stmt->execute();
    $stmt->close();
}
