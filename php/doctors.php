<?php
header('Content-Type: application/json');
try {
    require_once 'conection.php';

    $sql = "SELECT id, name, specialty, phone, email, center, city FROM doctors";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception($conn->error);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }

    echo json_encode(['status' => 'ok', 'doctors' => $doctors]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error retrieving doctors', 'detail' => $e->getMessage()]);
    if (isset($stmt) && $stmt) $stmt->close();
    if (isset($conn) && $conn) $conn->close();
}
?>