<?php
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';
require_once 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function generateVerificationCode() {
    return sprintf("%06d", mt_rand(0, 999999));
}

function saveVerificationCode($conn, $username, $code) {
    $stmt = $conn->prepare("INSERT INTO verification_codes (username, code, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $username, $code);
    return $stmt->execute();
}

function sendVerificationEmail($email, $code) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Ajusta según tu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'josue.naranja@gmail.com'; 
        $mail->Password = 'pkdu ozaj mvos gixd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('josue.naranja@gmail.com', 'Sistema de Login');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Codigo de verificacion';
        $mail->Body = "Tu código de verificación es: <b>$code</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        return false;
    }
}

function verifyCode($conn, $username, $code) {
    $stmt = $conn->prepare("SELECT * FROM verification_codes WHERE username = ? AND code = ? AND used = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
    $stmt->bind_param("ss", $username, $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Marcar el código como usado
        $stmt = $conn->prepare("UPDATE verification_codes SET used = 1 WHERE username = ? AND code = ?");
        $stmt->bind_param("ss", $username, $code);
        $stmt->execute();
        return true;
    }
    return false;
}
?> 