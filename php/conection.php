<?php
$host = "sql211.infinityfree.com";      // o el host del servidor MySQL
$user = "if0_40139405";     // cambia por tu usuario MySQL
$pass = "JvRrZIzwFwE";  // cambia por tu contraseña MySQL
$dbname = "if0_40139405_FarmaciaDigitalBD"; // tu base de datos

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>