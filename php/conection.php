<?php
$host = "localhost";        
$user = "root";             
$pass = "";                 
$dbname = "farmaciadigitalbd"; 

// Intentar la conexión sin el 'die' inmediatamente, solo para la prueba
$conn = new mysqli($host, $user, $pass, $dbname, 3306);

if ($conn->connect_error) {
    // Si la conexión falla, muestra el error de conexión y detiene todo.
    echo "FalloCriticoDB: " . $conn->connect_error;
    exit(); // Detenemos la ejecución
}
// Si la conexión es exitosa, no imprime nada. Los demás scripts continuarán.
?>