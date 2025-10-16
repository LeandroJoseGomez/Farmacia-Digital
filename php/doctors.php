<?php
/**
 * Farmacia Digital - Doctors & Pharmacists API
 * Maneja búsqueda de doctores y consultas a farmacéuticos
 */

// Configuración de errores
ini_set('display_errors', 0);
error_reporting(0);

// Headers CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Limpiar buffer
ob_start();
ob_clean();

try {
    // Conexión a BD
    $servername = "sql211.infinityfree.com";
    $username = "if0_40139405";
    $password = "JvRrZIzwFwE";
    $dbname = "if0_40139405_FarmaciaDigitalBD";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión");
    }

    $conn->set_charset("utf8mb4");

    // ================================================
    // GET: Buscar doctores
    // ================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Parámetros de búsqueda
        $ars = isset($_GET['ars']) ? $conn->real_escape_string($_GET['ars']) : '';
        $city = isset($_GET['city']) ? $conn->real_escape_string($_GET['city']) : '';
        $specialty = isset($_GET['specialty']) ? $conn->real_escape_string($_GET['specialty']) : '';
        $center = isset($_GET['center']) ? $conn->real_escape_string($_GET['center']) : '';
        $name = isset($_GET['name']) ? $conn->real_escape_string($_GET['name']) : '';

        // Construir query dinámica
        $sql = "SELECT d.*, GROUP_CONCAT(DISTINCT da.ars_name) as ars_list
                FROM doctors d
                LEFT JOIN doctor_ars da ON d.id = da.doctor_id
                WHERE 1=1";
        
        $conditions = [];
        
        if (!empty($city)) {
            $sql .= " AND d.city LIKE '%$city%'";
        }
        
        if (!empty($specialty)) {
            $sql .= " AND d.specialty LIKE '%$specialty%'";
        }
        
        if (!empty($center)) {
            $sql .= " AND d.center LIKE '%$center%'";
        }
        
        if (!empty($name)) {
            $sql .= " AND d.name LIKE '%$name%'";
        }

        $sql .= " GROUP BY d.id";

        // Filtrar por ARS después de agrupar
        if (!empty($ars)) {
            $sql .= " HAVING ars_list LIKE '%$ars%'";
        }

        $sql .= " ORDER BY d.name ASC";

        $result = $conn->query($sql);

        if (!$result) {
            throw new Exception("Error en consulta: " . $conn->error);
        }

        $doctors = [];
        while ($row = $result->fetch_assoc()) {
            // Convertir ars_list en array
            $ars_array = [];
            if ($row['ars_list']) {
                $ars_array = explode(',', $row['ars_list']);
            }

            $doctors[] = [
                'id' => intval($row['id']),
                'name' => $row['name'],
                'specialty' => $row['specialty'],
                'center' => $row['center'],
                'city' => $row['city'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'schedule' => $row['schedule'],
                'experience' => $row['experience'],
                'ars' => $ars_array
            ];
        }

        echo json_encode([
            'status' => 'ok',
            'count' => count($doctors),
            'doctors' => $doctors
        ], JSON_UNESCAPED_UNICODE);

        exit();
    }

    // ================================================
    // POST: Enviar consulta a farmacéutico
    // ================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON inválido");
        }

        // Validar datos requeridos
        if (!isset($data['patient_name']) || !isset($data['phone']) || !isset($data['description'])) {
            throw new Exception("Faltan datos requeridos");
        }

        $patient_name = $conn->real_escape_string($data['patient_name']);
        $phone = $conn->real_escape_string($data['phone']);
        $email = isset($data['email']) ? $conn->real_escape_string($data['email']) : '';
        $consult_type = isset($data['consult_type']) ? $conn->real_escape_string($data['consult_type']) : 'general';
        $medication_name = isset($data['medication_name']) ? $conn->real_escape_string($data['medication_name']) : '';
        $description = $conn->real_escape_string($data['description']);

        // Insertar consulta
        $sql = "INSERT INTO pharmacist_consultations 
                (patient_name, phone, email, consult_type, medication_name, description, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta");
        }

        $stmt->bind_param("ssssss", $patient_name, $phone, $email, $consult_type, $medication_name, $description);
        
        if (!$stmt->execute()) {
            throw new Exception("Error guardando consulta");
        }

        $consultation_id = $conn->insert_id;
        $stmt->close();

        echo json_encode([
            'status' => 'ok',
            'message' => 'Consulta registrada exitosamente',
            'consultation_id' => $consultation_id
        ], JSON_UNESCAPED_UNICODE);

        exit();
    }

    throw new Exception("Método no permitido");

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    ob_end_flush();
}
?>