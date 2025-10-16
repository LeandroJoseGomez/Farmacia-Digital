<?php
// Desactivar cualquier salida previa
ob_start();

// Configuración de errores
ini_set('display_errors', 0);
error_reporting(0);

// Headers CORS - IMPORTANTE: deben ir antes de cualquier salida
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Limpiar cualquier salida previa
ob_clean();

try {
    // Datos de conexión
    $servername = "sql211.infinityfree.com";
    $username = "if0_40139405";
    $password = "JvRrZIzwFwE";
    $dbname = "if0_40139405_FarmaciaDigitalBD";

    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Establecer charset
    $conn->set_charset("utf8mb4");

    // Consulta SQL
    $sql = "SELECT id, name, price, category, description, icon FROM products";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }

    // Recopilar productos
    $products = array();
    
   if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Asignar icono según categoría
            $icon = match (strtolower($row['category'])) {
                'vitaminas' => '🍊',
                'antibioticos' => '💉',
                'analgesicos' => '💊',
                'dermatologicos' => '🧴',
                'respiratorios' => '💨',
                'digestivos' => '🍽️',
                default => '⚕️'
            };

            $products[] = array(
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'price' => (float)$row['price'],
                'category' => $row['category'],
                'description' => $row['description'] ?? '',
                'icon' => $icon
            );
        }
    }

    // Cerrar conexión
    $conn->close();

    // Preparar respuesta
    $response = array(
        "status" => "ok",
        "count" => count($products),
        "products" => $products,
        "timestamp" => date('Y-m-d H:i:s')
    );

    // Enviar respuesta
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // En caso de error, enviar respuesta de error
    $error_response = array(
        "status" => "error",
        "message" => $e->getMessage(),
        "timestamp" => date('Y-m-d H:i:s')
    );
    
    echo json_encode($error_response, JSON_UNESCAPED_UNICODE);
}

// Finalizar buffer
ob_end_flush();
?>
