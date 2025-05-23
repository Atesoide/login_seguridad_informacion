<?php
session_start();

$sessionTimeout = 5; // 2 minutos

if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > $sessionTimeout) {
        session_unset();
        session_destroy();
        echo "expired";
        exit();
    } else {
        echo "active";
        exit();
    }
} else {
    echo "expired";
    exit();
}
?>
