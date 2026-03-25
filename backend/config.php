<?php
if (session_status() === PHP_SESSION_NONE) {
    session_save_path(sys_get_temp_dir());
    session_start();
}

if (isset($_ENV['DATABASE_URL']) && !empty($_ENV['DATABASE_URL'])) {
    $url = $_ENV['DATABASE_URL'];
    $dbparts = parse_url($url);

    $conn = new mysqli(
        $dbparts['host'],
        $dbparts['user'],
        $dbparts['pass'],
        ltrim($dbparts['path'], '/'),
        $dbparts['port']
    );
} else {
    // lokalno (XAMPP)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "family_manager";

    $conn = new mysqli($servername, $username, $password, $dbname);
}

// Preveri povezavo
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>