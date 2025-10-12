<?php
session_start(); // ¡Necesario para las sesiones!

include 'conection.php'; // Incluye el archivo de conexión

$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];

// Seleccionamos id, nombre y contrasena
$sql = "SELECT id, nombre, contrasena FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($contrasena, $row['contrasena'])) {
        
        // Guardar la información del usuario en la sesión
        $_SESSION['usuario'] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'correo' => $correo
        ];
        
        echo "ok";
    } else {
        echo "incorrecto"; // Contraseña incorrecta
    }
} else {
    echo "no_existe"; // Usuario no encontrado
}

$stmt->close();
$conn->close();
?>