<?php
// Evitar que navegadores o proxies cacheen respuestas de los endpoints PHP
// (esto explica por qué en incógnito —con cache vacío— funciona pero en ventana normal falla).
if (php_sapi_name() !== 'cli') {
    header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
    header('Pragma: no-cache'); // HTTP 1.0.
    header('Expires: 0'); // Proxies.
}

// Conexión segura que no imprime texto plano en errores — devuelve JSON si es llamado directamente.
$host = "sql211.infinityfree.com";      // o el host del servidor MySQL
$user = "if0_40139405";     // cambia por tu usuario MySQL
$pass = "JvRrZIzwFwE";  // cambia por tu contraseña MySQL
$dbname = "if0_40139405_FarmaciaDigitalBD"; // tu base de datos

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    // Si la petición espera JSON, devolver JSON de error; de lo contrario, lanzar una excepción.
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'DB connection error', 'detail' => $conn->connect_error]);
        exit;
    }
    throw new Exception('DB connection error: ' . $conn->connect_error);
}

