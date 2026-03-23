<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

function sendEventReminderEmail(string $recipientEmail, string $recipientName, array $eventData): bool
{
    $mailConfig = require __DIR__ . "/../entry/mail_config.php";

    $eventDate = !empty($eventData["event_date"]) ? date("d.m.Y", strtotime($eventData["event_date"])) : "/";
    $eventTime = !empty($eventData["whole_day"]) ? "Celodnevni dogodek" : (!empty($eventData["event_time"]) ? substr($eventData["event_time"], 0, 5) : "/");
    $location = trim((string)($eventData["location"] ?? "")) !== "" ? $eventData["location"] : "/";
    $description = trim((string)($eventData["description"] ?? "")) !== "" ? $eventData["description"] : "/";

    $subject = "Opomnik za dogodek: " . $eventData["name"];

    $bodyHtml = "
        <h2>Opomnik za dogodek</h2>
        <p>Pozdravljeni, " . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . ".</p>
        <p>To je opomnik za vaš dogodek:</p>
        <ul>
            <li><b>Naziv:</b> " . htmlspecialchars($eventData["name"], ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Datum:</b> " . htmlspecialchars($eventDate, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Ura:</b> " . htmlspecialchars($eventTime, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Lokacija:</b> " . htmlspecialchars($location, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Opis:</b> " . nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')) . "</li>
        </ul>
    ";

    $bodyText = "Opomnik za dogodek\n\n"
        . "Pozdravljeni, {$recipientName}.\n\n"
        . "Naziv: {$eventData["name"]}\n"
        . "Datum: {$eventDate}\n"
        . "Ura: {$eventTime}\n"
        . "Lokacija: {$location}\n"
        . "Opis: {$description}\n";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $mailConfig["host"];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig["username"];
        $mail->Password = $mailConfig["password"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $mailConfig["port"];
        $mail->CharSet = "UTF-8";

        $mail->setFrom($mailConfig["from_email"], $mailConfig["from_name"]);
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;
        $mail->AltBody = $bodyText;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
