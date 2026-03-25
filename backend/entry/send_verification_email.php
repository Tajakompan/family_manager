<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

function sendVerificationEmail(string $recipientEmail, string $recipientName, string $rawToken): bool
{
    $mailConfig = require __DIR__ . "/mail_config.php";

    $verifyUrl = $mailConfig["base_url"] . "/entry/verify_email.php?token=" . urlencode($rawToken);

    $subject = "Potrdi svoj email";
    $bodyHtml = "
        <h2>Potrdi svoj email</h2>
        <p>Pozdravljeni, " . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . ".</p>
        <p>Za dokončanje registracije kliknite spodnjo povezavo:</p>
        <p><a href=\"{$verifyUrl}\">Potrdi email</a></p>
        <p>Če se niste registrirali, to sporočilo ignorirajte.</p>
    ";

    $bodyText = "Potrdi svoj email\n\n"
        . "Pozdravljeni, {$recipientName}.\n\n"
        . "Za dokončanje registracije obiščite:\n{$verifyUrl}\n\n"
        . "Če se niste registrirali, to sporočilo ignorirajte.";

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
