function checkSession() {
    fetch("checar_sesion.php")
        .then(response => response.text())
        .then(status => {
            if (status === "expired") {
                alert("Tu sesión ha expirado. Serás redirigido al login.");
                window.location.href = "index.html"; // Redirigir a la página de login
            }
        })
        .catch(error => console.error("Error verificando sesión:", error));
}

setInterval(checkSession, 3000);