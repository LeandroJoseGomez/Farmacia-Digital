<?php
/**
 * Farmacia Digital - Orders API
 * Maneja la creación y consulta de pedidos
 */

// Configuración de errores para producción
ini_set('display_errors', 0);
error_reporting(0);

// Headers CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Limpiar buffer de salida
ob_start();
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
        throw new Exception("Error de conexión a la base de datos");
    }

    // Establecer charset
    $conn->set_charset("utf8mb4");

    // ================================================
    // GET: Obtener pedidos de un usuario
    // ================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;

        if ($usuario_id === 0) {
            // Si no hay usuario_id, devolver array vacío
            echo json_encode([
                "status" => "ok",
                "orders" => [],
                "message" => "No user ID provided"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Consultar pedidos del usuario
        $sql = "SELECT o.id, o.usuario_id, o.total, o.status, o.created_at,
                       oi.product_id, oi.cantidad, oi.precio, p.name as product_name
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE o.usuario_id = ?
                ORDER BY o.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $conn->error);
        }

        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Agrupar items por pedido
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $order_id = $row['id'];
            
            if (!isset($orders[$order_id])) {
                $orders[$order_id] = [
                    'id' => $order_id,
                    'usuario_id' => $row['usuario_id'],
                    'total' => floatval($row['total']),
                    'status' => $row['status'] ?: 'pending',
                    'created_at' => $row['created_at'],
                    'items' => []
                ];
            }

            // Agregar item si existe
            if ($row['product_id']) {
                $orders[$order_id]['items'][] = [
                    'product_id' => intval($row['product_id']),
                    'product_name' => $row['product_name'],
                    'cantidad' => intval($row['cantidad']),
                    'precio' => floatval($row['precio'])
                ];
            }
        }

        $stmt->close();

        echo json_encode([
            "status" => "ok",
            "orders" => array_values($orders)
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================
    // POST: Crear nuevo pedido
    // ================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Leer JSON del body
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON inválido: " . json_last_error_msg());
        }

        // Validar datos requeridos
        if (!isset($data['total']) || !isset($data['items'])) {
            throw new Exception("Faltan datos requeridos: total e items");
        }

        if (!is_array($data['items']) || count($data['items']) === 0) {
            throw new Exception("El pedido debe tener al menos un producto");
        }

        $usuario_id = isset($data['usuario_id']) ? intval($data['usuario_id']) : 0;
        $total = floatval($data['total']);
        $status = isset($data['status']) ? $data['status'] : 'pending';

        // Validar total
        if ($total <= 0) {
            throw new Exception("El total debe ser mayor a 0");
        }

        // Iniciar transacción
        $conn->begin_transaction();

        try {
            // Insertar pedido principal
            $sql = "INSERT INTO orders (usuario_id, total, status, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Error preparando insert de order: " . $conn->error);
            }

            $stmt->bind_param("ids", $usuario_id, $total, $status);
            
            if (!$stmt->execute()) {
                throw new Exception("Error insertando order: " . $stmt->error);
            }

            $order_id = $conn->insert_id;
            $stmt->close();

            // Insertar items del pedido
            $sql_item = "INSERT INTO order_items (order_id, product_id, cantidad, precio) VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);

            if (!$stmt_item) {
                throw new Exception("Error preparando insert de order_items: " . $conn->error);
            }

            foreach ($data['items'] as $item) {
                $product_id = intval($item['product_id']);
                $cantidad = intval($item['cantidad']);
                $precio = floatval($item['precio']);

                if ($product_id <= 0 || $cantidad <= 0 || $precio < 0) {
                    throw new Exception("Datos de producto inválidos");
                }

                $stmt_item->bind_param("iiid", $order_id, $product_id, $cantidad, $precio);
                
                if (!$stmt_item->execute()) {
                    throw new Exception("Error insertando order_item: " . $stmt_item->error);
                }
            }

            $stmt_item->close();

            // Commit de la transacción
            $conn->commit();

            // Respuesta exitosa
            echo json_encode([
                "status" => "ok",
                "message" => "Pedido creado exitosamente",
                "order_id" => $order_id,
                "total" => $total
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            // Rollback en caso de error
            $conn->rollback();
            throw $e;
        }

        exit();
    }

    // Método no permitido
    throw new Exception("Método HTTP no permitido");

} catch (Exception $e) {
    // Manejar cualquier error
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
        "timestamp" => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} finally {
    // Cerrar conexión si existe
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    ob_end_flush();
}
?>