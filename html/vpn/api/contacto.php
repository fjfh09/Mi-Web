<?php

// Recoger datos JSON enviados por POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

header("Access-Control-Allow-Origin: https://vpn.almagara.es");
header("Content-Type: application/json; charset=UTF-8");

// Cargar variables de entorno desde el archivo .env
$env_path = dirname(__DIR__, 2) . '/.env';
if (!file_exists($env_path)) {
    $env_path = dirname(__DIR__, 1) . '/.env';
}

if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
            $value = $matches[1];
        }
        $_ENV[$key] = $value;
    }
}

// Endpoint para exponer la Site Key de reCAPTCHA al frontend
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['getSiteKey'])) {
    echo json_encode(["siteKey" => $_ENV['ALMAGARA_SITE_KEY'] ?? '']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'];
$archivo = "/tmp/contacto_" . md5($ip);
if (file_exists($archivo) && time() - filemtime($archivo) < 30) {
    echo json_encode(['error' => 'Espera unos segundos antes de enviar de nuevo.']);
    exit;
}
touch($archivo);

if ($data === null) {
    echo json_encode(['error' => 'No se recibieron datos JSON válidos.']);
    exit;
}

if (!isset($data['nombre'], $data['correo'], $data['asunto'], $data['mensaje'])) {
    echo json_encode(['error' => 'Faltan campos.']);
    exit;
}

$nombre = trim(filter_var($data['nombre'], FILTER_DEFAULT));
$correo = filter_var(trim($data['correo']), FILTER_VALIDATE_EMAIL);
$asunto = trim(filter_var($data['asunto'], FILTER_DEFAULT));
$mensaje = trim(filter_var($data['mensaje'], FILTER_DEFAULT));

if (empty($nombre) || !$correo || empty($asunto) || empty($mensaje)) {
    echo json_encode(['error' => 'Datos de contacto no válidos.']);
    exit;
}

if (strlen($mensaje) > 1000) {
    echo json_encode(['error' => 'Mensaje demasiado largo.']);
    exit;
}

// Validación de reCAPTCHA
$recaptchaToken = $data['recaptchaToken'] ?? '';
if (empty($recaptchaToken)) {
    echo json_encode(['error' => 'Por favor, completa el CAPTCHA.']);
    exit;
}

$secretKey = $_ENV['ALMAGARA_SECRET_KEY'] ?? '';
if (!empty($secretKey)) {
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) . '&response=' . urlencode($recaptchaToken));
    $responseData = json_decode($verifyResponse);
    if (!$responseData->success) {
        echo json_encode(['error' => 'Fallo en la verificación del CAPTCHA. Por favor, inténtalo de nuevo.']);
        exit;
    }
}

try {
    // 1. Guardar en Base de Datos
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

    $sql = "INSERT INTO mensajes (nombre, correo, asunto, mensaje) VALUES (:nombre, :correo, :asunto, :mensaje)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':asunto', $asunto);
    $stmt->bindParam(':mensaje', $mensaje);

    $stmt->execute();

    // 2. Enviar Notificación por Correo Electrónico (dentro de try/catch para no romper la respuesta del cliente)
    try {
        $smtp_host = $_ENV['SMTP_HOST'] ?? 'mail.almagara.es';
        $smtp_port = $_ENV['SMTP_PORT'] ?? 465;
        $smtp_user = 'vpn@almagara.es';
        $smtp_pass = $_ENV['SMTP_PASS'];
        
        // Destinatarios leídos desde .env para evitar filtraciones
        $mail_javier = $_ENV['MAIL_TO_JAVIER'] ?? 'info@tudominio.com';
        $mail_vpn = $_ENV['MAIL_TO_VPN'] ?? 'info@tudominio.com';
        $mail_almagara = $_ENV['MAIL_TO_ALMAGARA'] ?? 'info@tudominio.com';
        $mail_test = $_ENV['MAIL_TO_TEST'] ?? 'info@tudominio.com';
        $recipients = [$mail_javier, $mail_vpn, $mail_almagara, $mail_test];
        $subject = "Web AlmagaraVPN - $nombre: $asunto";

        // Conectar por socket usando SSL
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $socket = @stream_socket_client("ssl://$smtp_host:$smtp_port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if ($socket) {
            if (!function_exists('smtp_read_vpn')) {
                function smtp_read_vpn($socket, $expected) {
                    $response = "";
                    while ($line = fgets($socket, 512)) {
                        $response .= $line;
                        if (substr($line, 3, 1) == " ") {
                            break;
                        }
                    }
                    $code = substr($response, 0, 3);
                    if ($code != $expected) {
                        throw new Exception("Error SMTP: Esperaba código $expected, obtuve: $response");
                    }
                    return $response;
                }
            }
            
            smtp_read_vpn($socket, 220);
            
            fwrite($socket, "EHLO " . $smtp_host . "\r\n");
            smtp_read_vpn($socket, 250);
            
            fwrite($socket, "AUTH LOGIN\r\n");
            smtp_read_vpn($socket, 334);
            
            fwrite($socket, base64_encode($smtp_user) . "\r\n");
            smtp_read_vpn($socket, 334);
            
            fwrite($socket, base64_encode($smtp_pass) . "\r\n");
            smtp_read_vpn($socket, 235);
            
            $message_id = "<" . uniqid(time()) . "@almagara.es>";
            $boundary = "----MultipartBoundary_" . uniqid(time());
            
            // Mensaje de texto plano
            $text_message = "Nueva solicitud recibida a través de la sección de contacto de la VPN:\n\n"
                          . "Nombre: $nombre\n"
                          . "Correo: $correo\n"
                          . "Asunto: $asunto\n\n"
                          . "Mensaje:\n$mensaje\n\n"
                          . "--- \n"
                          . "Este mensaje es una notificación automática del sistema.\n"
                          . "© 2026 VPN Almagara. Todos los derechos reservados. https://vpn.almagara.es\n";
                          
            // Mensaje HTML
            $html_message = "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
              <meta charset='UTF-8'>
              <meta name='viewport' content='width=device-width, initial-scale=1.0'>
              <meta name='color-scheme' content='light dark'>
              <meta name='supported-color-schemes' content='light dark'>
              <title>Mensaje de $nombre</title>
              <style>
                :root {
                  color-scheme: light dark;
                  supported-color-schemes: light dark;
                }
                @media only screen and (max-width: 600px) {
                  .container-table {
                    border-radius: 8px !important;
                    margin: 0 !important;
                  }
                  .header-td {
                    padding: 30px 20px !important;
                  }
                  .header-title {
                    font-size: 20px !important;
                  }
                  .content-td {
                    padding: 25px 16px !important;
                  }
                  .message-box {
                    font-size: 16px !important;
                    padding: 16px !important;
                  }
                }
                @media (prefers-color-scheme: dark) {
                  body {
                    background-color: #0f172a !important;
                  }
                  .outer-table {
                    background-color: #0f172a !important;
                  }
                  .container-table {
                    background-color: #1e293b !important;
                    border-color: #334155 !important;
                  }
                  .content-td {
                    color: #e2e8f0 !important;
                  }
                  .message-box {
                    background-color: #0f172a !important;
                    color: #f1f5f9 !important;
                    border-left-color: #818cf8 !important;
                  }
                  .detail-label {
                    color: #94a3b8 !important;
                    border-bottom-color: #334155 !important;
                  }
                  .detail-value {
                    color: #f1f5f9 !important;
                    border-bottom-color: #334155 !important;
                  }
                  .footer-text {
                    color: #94a3b8 !important;
                  }
                  .footer-sep {
                    border-top-color: #334155 !important;
                  }
                }
              </style>
            </head>
            <body style='margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;'>
              <table class='outer-table' width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color: #f4f6f8; padding: 40px 15px;'>
                <tr>
                  <td align='center'>
                    <table class='container-table' width='100%' cellpadding='0' cellspacing='0' border='0' style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; text-align: left;'>
                      <tr>
                        <td class='header-td' style='background-color: #1e293b; padding: 35px 24px; text-align: center;'>
                          <span style='background-color: rgba(255, 255, 255, 0.15); color: #ffffff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; padding: 4px 12px; border-radius: 100px; display: inline-block; margin-bottom: 12px;'>
                            VPN Almagara
                          </span>
                          <h1 class='header-title' style='margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.5px; line-height: 1.3;'>
                            ¡Nueva Solicitud VPN!
                          </h1>
                        </td>
                      </tr>
                      <tr>
                        <td class='content-td' style='padding: 35px 24px;'>
                          <p style='margin: 0 0 24px 0; font-size: 16px; color: #4b5563; line-height: 1.6;'>
                            Hola, has recibido una nueva solicitud a través de la sección de contacto de la VPN. Estos son los detalles del remitente:
                          </p>
                          <table width='100%' cellpadding='0' cellspacing='0' border='0' style='font-size: 15px; border-collapse: collapse; text-align: left; margin-bottom: 30px;'>
                            <tr>
                              <td class='detail-label' width='100' style='padding: 14px 0; color: #64748b; font-weight: 500; border-bottom: 1px solid #e2e8f0;'>Nombre</td>
                              <td class='detail-value' style='padding: 14px 0; color: #0f172a; font-weight: 600; border-bottom: 1px solid #e2e8f0;'>$nombre</td>
                            </tr>
                            <tr>
                              <td class='detail-label' style='padding: 14px 0; color: #64748b; font-weight: 500; border-bottom: 1px solid #e2e8f0;'>Correo</td>
                              <td class='detail-value' style='padding: 14px 0; border-bottom: 1px solid #e2e8f0;'>
                                <a href='mailto:$correo' style='color: #6366f1; text-decoration: none; font-weight: 600;'>$correo</a>
                              </td>
                            </tr>
                            <tr>
                              <td class='detail-label' style='padding: 14px 0; color: #64748b; font-weight: 500; border-bottom: 1px solid #e2e8f0;'>Asunto</td>
                              <td class='detail-value' style='padding: 14px 0; color: #0f172a; font-weight: 600; border-bottom: 1px solid #e2e8f0;'>$asunto</td>
                            </tr>
                          </table>
                          <h3 class='detail-label' style='margin: 0 0 12px 0; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b;'>
                            Mensaje escrito:
                          </h3>
                          <div class='message-box' style='background-color: #f8fafc; border-left: 4px solid #6366f1; border-radius: 4px 12px 12px 4px; padding: 20px; color: #334155; font-size: 15px; line-height: 1.6; font-style: italic;'>
                            " . nl2br(htmlspecialchars($mensaje)) . "
                          </div>
                          
                          <!-- Botón de Acción Directo (Mesa de Botón a Prueba de Balas) -->
                          <table width='100%' cellpadding='0' cellspacing='0' border='0' style='margin-top: 30px; text-align: center;'>
                            <tr>
                              <td align='center'>
                                <table cellpadding='0' cellspacing='0' border='0' style='margin: 0 auto;'>
                                  <tr>
                                    <td align='center' bgcolor='#6366f1' style='border-radius: 8px;'>
                                      <a href='mailto:$correo' target='_blank' style='font-size: 15px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 14px 28px; border: 1px solid #6366f1; display: inline-block; font-weight: 600;'>
                                        Responder a $nombre
                                      </a>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                    <table width='100%' cellpadding='0' cellspacing='0' border='0' style='max-width: 600px; margin: 0 auto; margin-top: 24px;'>
                      <tr>
                        <td align='center' class='footer-text' style='padding: 10px 20px; font-size: 12px; color: #6b7280; line-height: 1.6;'>
                          <p style='margin: 0 0 16px 0;'>Este es un correo automático generado por el sistema de la VPN.</p>
                          <table cellpadding='0' cellspacing='0' border='0' style='margin: 0 auto;'>
                            <tr>
                              <td align='center' class='footer-sep' style='border-top: 1px solid #e5e7eb; padding-top: 16px;'>
                                <p style='margin: 0; font-size: 13px; color: #4b5563; font-weight: 500;'>
                                  <strong>© 2026 VPN Almagara.</strong> Todos los derechos reservados.
                                </p>
                                <p style='margin: 4px 0 0 0; font-size: 12px;'>
                                  <a href='https://vpn.almagara.es' target='_blank' style='color: #6366f1; text-decoration: none; font-weight: 600;'>vpn.almagara.es</a>
                                </p>
                                <p style='margin: 12px 0 0 0; font-size: 10px; color: #9ca3af;'>
                                  Ref: " . uniqid('msg_') . " | " . date("d-m-Y H:i:s") . "
                                </p>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </body>
            </html>
            ";
            
            // Codificación quoted-printable para evitar spam y prevenir roturas de etiquetas HTML (ej. </tr>)
            $text_encoded = quoted_printable_encode($text_message);
            $html_encoded = quoted_printable_encode($html_message);

            $multipart_body = "--$boundary\r\n"
                            . "Content-Type: text/plain; charset=UTF-8\r\n"
                            . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
                            . $text_encoded . "\r\n\r\n"
                            . "--$boundary\r\n"
                            . "Content-Type: text/html; charset=UTF-8\r\n"
                            . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
                            . $html_encoded . "\r\n\r\n"
                            . "--$boundary--";

            foreach ($recipients as $recipient) {
                fwrite($socket, "MAIL FROM: <$smtp_user>\r\n");
                smtp_read_vpn($socket, 250);
                
                fwrite($socket, "RCPT TO: <$recipient>\r\n");
                smtp_read_vpn($socket, 250);
                
                fwrite($socket, "DATA\r\n");
                smtp_read_vpn($socket, 354);
                
                // Encabezados individuales
                $headers = [
                    "MIME-Version: 1.0",
                    "Content-Type: multipart/alternative; boundary=\"$boundary\"",
                    "From: Soporte AlmagaraVPN <$smtp_user>",
                    "To: <$recipient>",
                    "Message-ID: $message_id",
                    "Content-Language: es",
                    "List-Unsubscribe: <mailto:vpn@almagara.es?subject=unsubscribe>, <https://vpn.almagara.es>",
                    "Subject: =?utf-8?B?" . base64_encode($subject) . "?=",
                    "Date: " . date("r")
                ];
                $headers_str = implode("\r\n", $headers);
                $body = $headers_str . "\r\n\r\n" . $multipart_body . "\r\n.\r\n";
                
                fwrite($socket, $body);
                smtp_read_vpn($socket, 250);
            }
            
            fwrite($socket, "QUIT\r\n");
            fclose($socket);
        }
    } catch (Exception $mail_error) {
        error_log("Error de correo en VPN contacto: " . $mail_error->getMessage());
    }

    echo json_encode(['mensaje' => 'Mensaje enviado correctamente.']);
} catch (PDOException $e) {
    error_log("Error PDO: " . $e->getMessage());
    echo json_encode(['error' => 'Error al guardar en la base de datos.', 'detalle' => $e->getMessage()]);
}
