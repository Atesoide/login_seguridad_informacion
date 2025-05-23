<?php
session_start();
require_once 'email_verification.php';

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
    $password = trim($_POST['password']);

    // Consulta preparada para prevenir SQL injection
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];
        $email = $row['email']; // Asegúrate de que tu tabla usuarios tenga una columna email

        // Verificar la contraseña ingresada con el hash almacenado
        if (password_verify($password, $hashed_password)) {
            // Generar y guardar código de verificación
            $verification_code = generateVerificationCode();
            if (saveVerificationCode($conn, $username, $verification_code)) {
                // Enviar código por email
                if (sendVerificationEmail($email, $verification_code)) {
                    // Guardar información temporal en la sesión
                    $_SESSION['pending_verification'] = true;
                    $_SESSION['temp_username'] = $username;
                    
                    // Redirigir a la página de verificación
                    header("Location: verify.php");
                    exit();
                } else {
                    echo "Error al enviar el correo de verificación.";
                }
            } else {
                echo "Error al generar el código de verificación.";
            }
        } else {
            echo "Usuario o contraseña incorrectos.";
        }
    } else {
        echo "Usuario o contraseña incorrectos.";
    }
    
    $stmt->close();
}

$conn->close();
?>