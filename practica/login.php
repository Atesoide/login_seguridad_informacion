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

// Recibe los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // Encriptación MD5 para comparar

    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Consulta para verificar el usuario
    //$sql = "SELECT * FROM usuarios WHERE username='$username' AND password='$hash'";
    $sql = "SELECT * FROM usuarios WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        // Verificar la contraseña ingresada con el hash almacenado
        if (password_verify($password, $hashed_password)) {
            echo "¡Inicio de sesión exitoso! Bienvenido, $username.";
        } else {
            echo "Usuario o contraseña incorrectos.";
        }
    } else {
        echo "Usuario o contraseña incorrectos.";
    }
}

$conn->close();
?>