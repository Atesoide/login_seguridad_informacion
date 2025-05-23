<?php
session_start();
if (!isset($_SESSION['firma_digital_pendiente'])) {
    header("Location: index.html");
    exit();
}

$challenge = bin2hex(random_bytes(32)); // Reto de 64 caracteres
$_SESSION['firma_challenge'] = $challenge;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Firma Digital</title>
    <script>
        async function firmarReto() {
            const fileInput = document.getElementById('privateKey');
            const reto = document.getElementById('reto').textContent;

            if (fileInput.files.length === 0) {
                alert("Selecciona tu archivo de clave privada (.pem)");
                return;
            }

            const reader = new FileReader();
            reader.onload = async function (event) {
                const privateKeyPem = event.target.result;

                // Este paso normalmente requiere una librería JS que pueda procesar RSA PEM.
                // Por simplicidad, vamos a simular que enviamos la firma generada externamente.

                const response = await fetch('procesar_firma.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        clave_privada: privateKeyPem,
                        reto: reto
                    })
                });

                const result = await response.text();
                document.getElementById('resultado').innerHTML = result;
            };

            reader.readAsText(fileInput.files[0]);
        }
    </script>
</head>
<body>
    <h2>Verificación por Firma Digital</h2>
    <p>Firma este reto con tu clave privada:</p>
    <pre id="reto"><?php echo $_SESSION['firma_challenge']; ?></pre>
    <p>Selecciona tu archivo `.pem`:</p>
    <input type="file" id="privateKey" accept=".pem"><br><br>
    <button onclick="firmarReto()">Firmar y enviar</button>
    <div id="resultado" style="margin-top: 20px;"></div>
</body>
</html>