<?php
header('Content-Type: application/json');
try {
    require_once 'conection.php';

    $input = json_decode(file_get_contents('php://input'), true);
    $nombre = $input['nombre'] ?? $_POST['nombre'] ?? null;
    $correo = $input['correo'] ?? $_POST['correo'] ?? null;
    $plain = $input['contrasena'] ?? $_POST['contrasena'] ?? null;

    if (!$nombre || !$correo || !$plain) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos']);
        exit;
    }

    $hash = password_hash($plain, PASSWORD_BCRYPT);

    // Verificar si el correo ya existe
    $sql_check = "SELECT id FROM users WHERE correo = ?";
    $stmt = $conn->prepare($sql_check);
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'existe']);
        exit;
    }
    $stmt->close();

    $sql = "INSERT INTO users (nombre, correo, contrasena) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('sss', $nombre, $correo, $hash);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo registrar', 'detail' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Registro error', 'detail' => $e->getMessage()]);
}
