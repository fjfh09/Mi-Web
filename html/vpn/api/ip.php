<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/plain");

// IP del cliente que hace la peticion
$ip_cliente = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
echo $ip_cliente;