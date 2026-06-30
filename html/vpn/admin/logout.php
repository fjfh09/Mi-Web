<?php
if (isset($_COOKIE['SESSION_ADMIN'])) {
    session_name('SESSION_ADMIN');
} else {
    session_name('SESSION_CLIENTE');
}

session_start();
setcookie(session_name(), '', time() - 3600, '/');
session_unset();
session_destroy();

header("Location: https://vpn.almagara.es/");
exit;
