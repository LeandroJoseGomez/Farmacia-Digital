<?php
session_start();
header('Content-Type: application/json');
try {
    require_once 'conection.php';

    // Aceptar application/json o form-data
    $input = json_decode(file_get_contents('php://input'), true);
    $correo = $input['correo'] ?? $_POST['correo'] ?? null;
    $contrasena = $input['contrasena'] ?? $_POST['contrasena'] ?? null;

    if (!$correo || !$contrasena) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Faltan credenciales']);
        exit;
    }

    $sql = "SELECT id, nombre, correo, contrasena, role FROM users WHERE correo = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'no_existe']);
        exit;
    }

    $row = $result->fetch_assoc();
    if (!password_verify($contrasena, $row['contrasena'])) {
        echo json_encode(['status' => 'incorrecto']);
        exit;
    }

    // Autenticación OK
    $_SESSION['usuario'] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'correo' => $row['correo'],
        'role' => $row['role']
    ];

    echo json_encode(['status' => 'ok', 'usuario' => $_SESSION['usuario']]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Login error', 'detail' => $e->getMessage()]);
}