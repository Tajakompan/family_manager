<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

function sendPasswordResetEmail(string $recipientEmail, string $recipientName, string $rawToken): bool
{
    $mailConfig = require __DIR__ . "/mail_config.php";

    $resetUrl = $mailConfig["base_url"] . "/entry/reset_password.php?token=" . urlencode($rawToken);

    $subject = "Ponastavitev gesla";
    $bodyHtml = "
        <h2>Ponastavitev gesla</h2>
        <p>Pozdravljeni, " . htmlspecialchars($recipientName, ENT_QUOTES, "UTF-8") . ".</p>
        <p>Za nastavitev novega gesla kliknite spodnjo povezavo:</p>
        <p><a href=\"{$resetUrl}\">Nastavi novo geslo</a></p>
        <p>Povezava velja 1 uro.</p>
        <p>Če zahteve niste podali vi, sporočilo ignorirajte.</p>
    ";

    $bodyText = "Ponastavitev gesla\n\n"
        . "Pozdravljeni, {$recipientName}.\n\n"
        . "Za nastavitev novega gesla obiščite:\n{$resetUrl}\n\n"
        . "Povezava velja 1 uro.\n\n"
        . "Če zahteve niste podali vi, sporočilo ignorirajte.";

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
