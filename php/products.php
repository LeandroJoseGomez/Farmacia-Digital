<?php
// CORS: permitir orígenes (para facilitar desarrollo y hospedajes que redirigen)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // permitir el origen que hizo la petición
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
// Siempre devolver JSON
header('Content-Type: application/json; charset=utf-8');

// Responder rápido a preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
// No mostrar errores crudos en producción
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Manejador de excepciones que devuelve JSON
set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unhandled exception', 'detail' => $e->getMessage()]);
    exit;
});

// Capturar cualquier output inesperado y asegurarnos de devolver JSON válido
ob_start();
try {
    require_once __DIR__ . '/conection.php';

    // Devuelve lista de productos (opcional parámetro category)
    $category = isset($_GET['category']) ? $_GET['category'] : null;

    if ($category) {
        $sql = "SELECT id, name, price, category, description, icon FROM products WHERE category = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param('s', $category);
    } else {
        $sql = "SELECT id, name, price, category, description, icon FROM products";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception($conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    // Limpiar cualquier output previo (warnings u otro HTML)
    $buffer = ob_get_clean();
    // Si hubo output no deseado, lo registramos en 'detail' (sin exponer datos sensibles)
    if ($buffer && trim($buffer) !== '') {
        // No incluir buffer completo en producción; incluir indicación breve
        error_log("products.php: unexpected output from includes: " . substr($buffer, 0, 1000));
    }

    echo json_encode(['status' => 'ok', 'products' => $products]);

    $stmt->close();
    $conn->close();
    exit;
} catch (Exception $e) {
    // Desechamos cualquier buffer y respondemos en JSON
    if (ob_get_level()) ob_end_clean();
    http_response_code(500);
    $detail = $e->getMessage();
    error_log('products.php error: ' . $detail);
    echo json_encode(['status' => 'error', 'message' => 'Error retrieving products', 'detail' => $detail]);
    if (isset($stmt) && $stmt) $stmt->close();
    if (isset($conn) && $conn) $conn->close();
    exit;
}