<?php
header("Content-Type: application/json; charset=UTF-8");

// Cargar variables de entorno desde el archivo .env (se busca primero fuera de la raíz pública para máxima seguridad)
$env_path = dirname(__DIR__) . '/.env';
if (!file_exists($env_path)) {
    $env_path = __DIR__ . '/.env';
}

if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Quitar comillas simples o dobles envolventes si existen
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

// Obtener datos crudos de la petición POST
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["success" => false, "error" => "Datos de entrada no válidos"]);
    exit;
}

$name = trim(filter_var($input['name'] ?? '', FILTER_DEFAULT));
$email_raw = trim($input['email'] ?? '');
$phone = trim(filter_var($input['phone'] ?? '', FILTER_DEFAULT));
$message = trim(filter_var($input['message'] ?? '', FILTER_DEFAULT));

if (empty($name)) {
    echo json_encode(["success" => false, "error" => "El campo Nombre es obligatorio."]);
    exit;
}
if (strlen($name) < 2 || strlen($name) > 100) {
    echo json_encode(["success" => false, "error" => "El nombre debe tener entre 2 y 100 caracteres."]);
    exit;
}

if (empty($email_raw)) {
    echo json_encode(["success" => false, "error" => "El campo Correo electrónico es obligatorio."]);
    exit;
}
$email = filter_var($email_raw, FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(["success" => false, "error" => "El formato del correo electrónico no es válido."]);
    exit;
}

if (empty($phone)) {
    echo json_encode(["success" => false, "error" => "El campo Teléfono es obligatorio."]);
    exit;
}
if (!preg_match('/^[0-9\s\+\-\(\)]{7,20}$/', $phone)) {
    echo json_encode(["success" => false, "error" => "El formato del número de teléfono no es válido (mínimo 7 caracteres, solo números, espacios, + o -)."]);
    exit;
}

if (empty($message)) {
    echo json_encode(["success" => false, "error" => "El campo Mensaje es obligatorio."]);
    exit;
}
if (strlen($message) < 5 || strlen($message) > 5000) {
    echo json_encode(["success" => false, "error" => "El mensaje debe tener entre 5 y 5000 caracteres."]);
    exit;
}

// Validación de reCAPTCHA
$recaptchaToken = $input['recaptchaToken'] ?? '';
if (empty($recaptchaToken)) {
    echo json_encode(["success" => false, "error" => "Por favor, completa el CAPTCHA."]);
    exit;
}

$secretKey = $_ENV['ALMAGARA_SECRET_KEY'] ?? '';
if (!empty($secretKey)) {
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) . '&response=' . urlencode($recaptchaToken));
    $responseData = json_decode($verifyResponse);
    if (!$responseData->success) {
        echo json_encode(["success" => false, "error" => "Fallo en la verificación del CAPTCHA. Por favor, inténtalo de nuevo."]);
        exit;
    }
}

// Variables SMTP leídas desde el entorno (o valores por defecto del servidor)
$smtp_host = $_ENV['SMTP_HOST'] ?? 'mail.almagara.es';
$smtp_port = $_ENV['SMTP_PORT'] ?? 465;
$smtp_user = $_ENV['SMTP_USER'] ?? 'fjfh@almagara.es';
$smtp_pass = $_ENV['SMTP_PASS'] ?? ''; // Se cargará de forma segura desde el archivo .env
$mail_to_gmail = $_ENV['MAIL_TO_GMAIL'] ?? 'info@tudominio.com';
$mail_to_almagara = $_ENV['MAIL_TO_ALMAGARA'] ?? 'info@tudominio.com';

if (empty($smtp_pass)) {
    echo json_encode(["success" => false, "error" => "El servidor de correo no está configurado (falta contraseña)"]);
    exit;
}

try {
    $subject = "Mensaje de $name";
    
    $html_message = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <meta name='color-scheme' content='light dark'>
      <meta name='supported-color-schemes' content='light dark'>
      <title>Mensaje de $name</title>
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
            
            <table class='container-table' width='100%' cellpadding='0' cellspacing='0' border='0' style='max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; text-align: left;'>
              
              <!-- Cabecera Sólida Oscura Premium (Inmune a inversiones molestas) -->
              <tr>
                <td class='header-td' style='background-color: #1e293b; padding: 35px 24px; text-align: center;'>
                  <span style='background-color: rgba(255, 255, 255, 0.15); color: #ffffff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; padding: 4px 12px; border-radius: 100px; display: inline-block; margin-bottom: 12px;'>
                    Contacto Web
                  </span>
                  <h1 class='header-title' style='margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.5px; line-height: 1.3;'>
                    ¡Nuevo Mensaje Recibido!
                  </h1>
                </td>
              </tr>

              <!-- Contenido Principal -->
              <tr>
                <td class='content-td' style='padding: 35px 24px;'>
                  <p style='margin: 0 0 24px 0; font-size: 16px; color: #4b5563; line-height: 1.6;'>
                    Hola, has recibido una nueva consulta a través del formulario de contacto de tu portfolio. A continuación tienes los detalles del remitente:
                  </p>
                  
                  <!-- Tabla de Detalles con Diseño Limpio -->
                  <table width='100%' cellpadding='0' cellspacing='0' border='0' style='font-size: 15px; border-collapse: collapse; text-align: left; margin-bottom: 30px;'>
                    <tr>
                      <td class='detail-label' width='100' style='padding: 14px 0; color: #64748b; font-weight: 500; border-bottom: 1px solid #e2e8f0;'>Nombre</td>
                      <td class='detail-value' style='padding: 14px 0; color: #0f172a; font-weight: 600; border-bottom: 1px solid #e2e8f0;'>$name</td>
                    </tr>
                    <tr>
                      <td class='detail-label' style='padding: 14px 0; color: #64748b; font-weight: 500; border-bottom: 1px solid #e2e8f0;'>Correo</td>
                      <td class='detail-value' style='padding: 14px 0; border-bottom: 1px solid #e2e8f0;'>
                        <a href='mailto:$email' style='color: #6366f1; text-decoration: none; font-weight: 600;'>$email</a>
                      </td>
                    </tr>
                    <tr>
                      <td class='detail-label' style='padding: 14px 0; color: #64748b; font-weight: 500; border-bottom: 1px solid #e2e8f0;'>Teléfono</td>
                      <td class='detail-value' style='padding: 14px 0; color: #0f172a; font-weight: 600; border-bottom: 1px solid #e2e8f0;'>" . (!empty($phone) ? $phone : "<span style='color: #94a3b8; font-style: italic; font-weight: 400;'>No especificado</span>") . "</td>
                    </tr>
                  </table>
                  
                  <!-- Cabecera de Mensaje -->
                  <h3 class='detail-label' style='margin: 0 0 12px 0; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b;'>
                    Mensaje escrito:
                  </h3>
                  
                  <!-- Tarjeta de Mensaje con Div (Evita anidamiento excesivo de tablas) -->
                  <div class='message-box' style='background-color: #f8fafc; font-size: 15px; border-left: 4px solid #6366f1; border-radius: 4px 12px 12px 4px; padding: 20px; color: #334155; line-height: 1.6; font-style: italic;'>
                    " . nl2br(htmlspecialchars($message)) . "
                  </div>
                  
                  <!-- Botón de Acción Directo (Mesa de Botón a Prueba de Balas) -->
                  <table width='100%' cellpadding='0' cellspacing='0' border='0' style='margin-top: 30px; text-align: center;'>
                    <tr>
                      <td align='center'>
                        <table cellpadding='0' cellspacing='0' border='0' style='margin: 0 auto;'>
                          <tr>
                            <td align='center' bgcolor='#6366f1' style='border-radius: 8px;'>
                              <a href='mailto:$email' target='_blank' style='font-size: 15px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 14px 28px; border: 1px solid #6366f1; display: inline-block; font-weight: 600;'>
                                Responder a $name
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
            
            <!-- Pie de Página -->
            <table width='100%' cellpadding='0' cellspacing='0' border='0' style='max-width: 600px; margin: 0 auto; margin-top: 24px;'>
              <tr>
                <td align='center' class='footer-text' style='padding: 10px 20px; font-size: 12px; color: #6b7280; line-height: 1.6;'>
                  <p style='margin: 0 0 16px 0;'>Este es un correo automático generado por el sistema de tu portfolio.</p>
                  
                  <table cellpadding='0' cellspacing='0' border='0' style='margin: 0 auto;'>
                    <tr>
                      <td align='center' class='footer-sep' style='border-top: 1px solid #e5e7eb; padding-top: 16px;'>
                        <p style='margin: 0; font-size: 13px; color: #4b5563; font-weight: 500;'>
                          <strong>© 2026 fjfh.</strong> Todos los derechos reservados.
                        </p>
                        <p style='margin: 4px 0 0 0; font-size: 12px;'>
                          <a href='https://fjfh.almagara.es' target='_blank' style='color: #6366f1; text-decoration: none; font-weight: 600;'>fjfh.almagara.es</a>
                        </p>
                        <!-- Identificador dinámico único para evitar que Gmail colapse/oculte el footer -->
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





    // Destinatarios a los que se enviará la copia
    $recipients = [$mail_to_gmail, $mail_to_almagara];

    // Conectar por socket usando SSL
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);
    
    $socket = @stream_socket_client("ssl://$smtp_host:$smtp_port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        throw new Exception("Error de conexión SMTP: $errstr ($errno)");
    }
    
    function smtp_read($socket, $expected) {
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
    
    smtp_read($socket, 220);
    
    fwrite($socket, "EHLO " . ($smtp_host ?? 'almagara.es') . "\r\n");
    smtp_read($socket, 250);
    
    fwrite($socket, "AUTH LOGIN\r\n");
    smtp_read($socket, 334);
    
    fwrite($socket, base64_encode($smtp_user) . "\r\n");
    smtp_read($socket, 334);
    
    fwrite($socket, base64_encode($smtp_pass) . "\r\n");
    smtp_read($socket, 235);
    
    // Generar un ID de mensaje único y válido
    $domain = $_SERVER['SERVER_NAME'] ?? 'almagara.es';
    $message_id = "<" . uniqid(time()) . "@" . $domain . ">";

    // Versión en texto plano para clientes sin soporte HTML y mejor puntuación antispam
    $text_message = "Nuevo mensaje de contacto recibido desde tu portfolio:\n\n"
                  . "Nombre: $name\n"
                  . "Correo: $email\n"
                  . "Teléfono: " . (!empty($phone) ? $phone : "No especificado") . "\n\n"
                  . "Mensaje:\n$message\n\n"
                  . "--- \n"
                  . "Este mensaje es una notificación automática del sistema.\n"
                  . "© 2026 fjfh. Todos los derechos reservados. https://fjfh.almagara.es\n";

    // Límite único para separar las partes del mensaje multipart
    $boundary = "----MultipartBoundary_" . uniqid(time());

    // Codificación en quoted-printable para máxima compatibilidad y evitar spam/cortes de etiquetas
    $text_encoded = quoted_printable_encode($text_message);
    $html_encoded = quoted_printable_encode($html_message);

    // Cuerpo en formato multipart (Texto plano + HTML)
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
        smtp_read($socket, 250);
        
        fwrite($socket, "RCPT TO: <$recipient>\r\n");
        smtp_read($socket, 250);
        
        fwrite($socket, "DATA\r\n");
        smtp_read($socket, 354);
        
        // Encabezados formateados individualmente para evitar Spam
        $headers = [
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"$boundary\"",
            "From: Contacto Portfolio <$smtp_user>",
            "To: <$recipient>",
            "Message-ID: $message_id",
            "Content-Language: es",
            "Subject: =?utf-8?B?" . base64_encode($subject) . "?=",
            "Date: " . date("r")
        ];
        
        $headers_str = implode("\r\n", $headers);
        $body = $headers_str . "\r\n\r\n" . $multipart_body . "\r\n.\r\n";
        
        fwrite($socket, $body);
        smtp_read($socket, 250);
    }
    
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
