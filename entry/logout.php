<?php
require_once __DIR__ . "/../config.php";

session_unset();    // izbriše VSE $_SESSION spremenljivke
session_destroy();  // uniči sejo na strežniku

header("Location: login.php");
exit;
?>