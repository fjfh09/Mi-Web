<?php
// Solo afecta a la sesion cliente
session_name('SESSION_CLIENTE');
session_start();

// Borrar cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}

session_unset();
session_destroy();

header("Location: https://fjfh06.ddns.net/vpn/");
exit;
