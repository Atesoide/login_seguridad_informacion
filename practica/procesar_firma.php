<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['firma_challenge'])) {
    echo "Sesión no válida.";
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$clave_privada_pem = $data['clave_privada'];
$reto = $_SESSION['firma_challenge'];

$username = $_SESSION['username'];

// Obtener clave pública del usuario
$conn = new mysqli('localhost', 'root', '', 'login_seguridad');
$stmt = $conn->prepare("SELECT clave_publica FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$clave_publica = $row['clave_publica'] ?? null;

if (!$clave_publica) {
    echo "No se encontró clave pública para el usuario.";
    exit();
}

// Firmar reto con clave privada (solo simulación)
$privateKey = openssl_pkey_get_private($clave_privada_pem);
if (!$privateKey) {
    echo "Error: Clave privada inválida o malformateada.";
    exit();
}

//firmar el reto
openssl_sign($reto, $firma, $privateKey, OPENSSL_ALGO_SHA256);
openssl_free_key($privateKey);

// Cargar clave pública
$publicKey = openssl_pkey_get_public($clave_publica);
if (!$publicKey) {
    echo "Error: Clave pública inválida o malformateada.<br>";
    echo nl2br(htmlspecialchars($clave_publica)); // Para debug visual
    exit();
}

// Verificar la firma con la clave pública
$verificado = openssl_verify($reto, $firma, $publicKey, OPENSSL_ALGO_SHA256);

if ($verificado === 1) {
    // Firma válida
    $_SESSION['loggedin'] = true;
    unset($_SESSION['firma_digital_pendiente']);
    echo "<strong>Firma verificada correctamente.</strong> <a href='exito.html'>Continuar</a>";
}elseif ($verificado === 0) {
    echo "Firma inválida. Verifica tu clave privada.";
} else {
    echo "Error durante la verificación de la firma";
}