<?php
// Conexión a la base de datos
$host = 'localhost';
$user = 'root'; 
$pass = '';
$db   = 'login_seguridad';

$conn = new mysqli($host, $user, $pass, $db);

// Verifica la conexión
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
} else {
    echo "Conexión exitosa a la base de datos.<br>";
}

// Procesar el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Verificar si el usuario ya existe
    $sql_check = "SELECT * FROM usuarios WHERE username = '$username'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "El usuario ya existe. Por favor, elige otro nombre.";
    } else {
        // Hashear la contraseña antes de guardarla
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insertar usuario en la base de datos
        $sql = "INSERT INTO usuarios (username, password) VALUES ('$username', '$hashed_password')";
        
        if ($conn->query($sql) === TRUE) {
            echo "Registro exitoso. <a href='index.html'>Iniciar sesión</a>";
        } else {
            echo "Error al registrar el usuario: " . $conn->error;
        }
    }
}

$conn->close();
?>
