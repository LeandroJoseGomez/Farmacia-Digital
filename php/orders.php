<?php
// orders.php
// POST: crear un pedido. Espera JSON: { usuario_id, total, items: [ { product_id, cantidad, precio } ] }
// GET: listar pedidos de un usuario: ?usuario_id=1

header('Content-Type: application/json');
require_once __DIR__ . '/conection.php';

$method = $_SERVER['REQUEST_METHOD'];
try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['usuario_id']) || !isset($data['items']) || !is_array($data['items'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'invalid_payload']);
            exit;
        }

        $usuario_id = intval($data['usuario_id']);
        $total = floatval($data['total'] ?? 0);
        $items = $data['items'];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO orders (usuario_id, total, created_at) VALUES (?, ?, NOW())");
            if (!$stmt) throw new Exception($conn->error);
            $stmt->bind_param('id', $usuario_id, $total);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();

            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, cantidad, precio) VALUES (?, ?, ?, ?)");
            if (!$stmtItem) throw new Exception($conn->error);
            foreach ($items as $it) {
                $product_id = intval($it['product_id']);
                $cantidad = intval($it['cantidad']);
                $precio = floatval($it['precio']);
                $stmtItem->bind_param('iiid', $order_id, $product_id, $cantidad, $precio);
                $stmtItem->execute();
            }
            $stmtItem->close();

            $conn->commit();
            echo json_encode(['status' => 'ok', 'order_id' => $order_id]);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($method === 'GET') {
        // List orders for a user
        $usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
        if ($usuario_id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'missing_usuario_id']);
            exit;
        }

        $stmt = $conn->prepare("SELECT id, total, created_at FROM orders WHERE usuario_id = ? ORDER BY created_at DESC");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $orders = [];
        while ($row = $res->fetch_assoc()) {
            $order_id = $row['id'];
            $stmtItems = $conn->prepare("SELECT product_id, cantidad, precio FROM order_items WHERE order_id = ?");
            if (!$stmtItems) throw new Exception($conn->error);
            $stmtItems->bind_param('i', $order_id);
            $stmtItems->execute();
            $resItems = $stmtItems->get_result();
            $items = [];
            while ($it = $resItems->fetch_assoc()) {
                $items[] = $it;
            }
            $stmtItems->close();

            $orders[] = [
                'id' => $order_id,
                'total' => $row['total'],
                'created_at' => $row['created_at'],
                'items' => $items
            ];
        }
        $stmt->close();

        echo json_encode(['status' => 'ok', 'orders' => $orders]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'method_not_allowed']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Orders error', 'detail' => $e->getMessage()]);
}
}