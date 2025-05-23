<?php
putenv("OPENSSL_CONF=C:\\xampp\\php\\extras\\openssl\\openssl.cnf");
echo getenv('OPENSSL_CONF');
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
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Verificar si el usuario o email ya existe usando prepared statements
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['username'] === $username) {
            echo "El usuario ya existe. Por favor, elige otro nombre.";
        } else {
            echo "El correo electrónico ya está registrado.";
        }
    } else {
        // Hashear la contraseña antes de guardarla
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        //Crear llave pública y privada
        $clave_config = array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        $resource = openssl_pkey_new($clave_config);

        if (!$resource) {
            
            while ($msg = openssl_error_string()) {
                echo $msg . "<br>";
            }
            die('Error al generar la clave. Verifica que OpenSSL esté habilitado en PHP.');
        }

        //Extrae clave privada
        openssl_pkey_export($resource, $clave_privada);
        //Extrae clave pública
        $detalles_clave = openssl_pkey_get_details($resource);
        $clave_publica = $detalles_clave["key"];

        // Insertar usuario en la base de datos usando prepared statements
        $stmt = $conn->prepare("INSERT INTO usuarios (username, email, password, clave_publica) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $clave_publica);
        
        if ($stmt->execute()) {
            //Aqui entregamos su llave privada al usuario
            file_put_contents("clave_privada_$username.pem", $clave_privada);
            echo "Tu clave privada ha sido generada. <a href='clave_privada_$username.pem' download>Descargar clave privada</a>";
            echo "OJO! no la pierdas.<br><br>";
            echo "Registro exitoso. <a href='index.html'>Iniciar sesión</a>";
        } else {
            echo "Error al registrar el usuario: " . $stmt->error;
        }
    }
    $stmt->close();
}

$conn->close();
?>
