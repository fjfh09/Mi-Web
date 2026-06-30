<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/plain");

// IP pública fija de tu casa (DDNS)
$ip_ddns = trim(file_get_contents("https://api.ipify.org") ?: "");
echo $ip_ddns;