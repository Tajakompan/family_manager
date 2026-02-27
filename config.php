<?php
session_start(); // da $_SESSION deluje

$servername = "localhost";   // ker delaš lokalno
$username = "root";    // privzeti uporabnik v xampu
$password = "";       // privzeto brez gesla
$dbname = "family_manager";  // ime tvoje baze

// Ustvari povezavo
$conn = new mysqli($servername, $username, $password, $dbname);

// Preveri povezavo
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
