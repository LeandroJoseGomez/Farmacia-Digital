<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['usuario'])) {
    echo json_encode([
        "status" => "ok",
        "nombre" => $_SESSION['usuario']['nombre'],
        "correo" => $_SESSION['usuario']['correo']
    ]);
} else {
    echo json_encode(["status" => "no_session"]);
}
?>