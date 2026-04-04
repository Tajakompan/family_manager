<?php

function normalizeReminderInput(?string $rawReminder): ?string {
    $rawReminder = trim((string)$rawReminder);

    if ($rawReminder === "") {
        return null;
    }

    $formats = ['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s'];

    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $rawReminder);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d H:i:s');
        }
    }

    return null;
}

function formatReminderForInput(?string $dbValue): string {
    if ($dbValue === null || $dbValue === "") {
        return "";
    }

    $ts = strtotime($dbValue);
    return $ts ? date('Y-m-d\TH:i', $ts) : "";
}

function formatReminderForDisplay(?string $dbValue): string {
    if ($dbValue === null || $dbValue === "") {
        return "/";
    }

    $ts = strtotime($dbValue);
    return $ts ? date('d.m.Y H:i', $ts) : "/";
}
