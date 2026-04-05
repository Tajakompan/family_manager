<?php

if (session_status() === PHP_SESSION_NONE) {
    session_save_path(sys_get_temp_dir());
    session_start();
}

$host_name = $_SERVER["HTTP_HOST"] ?? "";

if ($host_name === "localhost" || $host_name === "127.0.0.1") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "family_manager";
} else {
    $servername = "localhost";
    $username = "familoop_familoop";
    $password = "familoop_user";
    $dbname = "familoop_family_manager";
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
date_default_timezone_set("Europe/Ljubljana");
$conn->query("SET time_zone = '+02:00'");
?>
