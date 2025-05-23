<?php
session_start();
require_once 'email_verification.php';

// Si no hay una sesión de verificación pendiente, redirigir al login
if (!isset($_SESSION['pending_verification']) || !$_SESSION['pending_verification']) {
    header("Location: index.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);
    $username = $_SESSION['temp_username'];
    
    $conn = new mysqli('localhost', 'root', '', 'login_seguridad');
    
    if (verifyCode($conn, $username, $code)) {
        // Código válido, completar el login
        $_SESSION['firma_digital_pendiente'] = true;
        $_SESSION['username'] = $username;
        unset($_SESSION['pending_verification']);
        unset($_SESSION['temp_username']);
        
        //redirige al usuario a la página de firma digital
        header("Location: firma_digital.php");
        exit();
    } else {
        $error = "Código inválido o expirado";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verificación de dos factores</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type="text"] {
            padding: 8px;
            margin: 10px 0;
            width: 200px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verificación de dos factores</h2>
        <p>Se ha enviado un código a tu correo electrónico.</p>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="code" placeholder="Ingresa el código de 6 dígitos" required pattern="\d{6}">
            <br>
            <button type="submit">Verificar</button>
        </form>
    </div>
</body>
</html> 