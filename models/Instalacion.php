<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Instalacion {

    // Configuración SMTP para envío de correos (Gmail)
    private const SMTP_HOST = 'smtp.gmail.com';
    private const SMTP_USER = 'jfug0707@gmail.com'; // Dirección de Gmail completa
    private const SMTP_PASS = 'pfhh qlel jegg cbmc'; // Código de 16 caracteres sin espacios (App Password)
    private const SMTP_PORT = 587; // Recomendado: 587 (con STARTTLS / TLS), Alternativo: 465 (con SSL)
    private const SMTP_SECURE = 'tls'; // 'tls' para puerto 587, 'ssl' para puerto 465
    private const SMTP_AUTH = true;

    // Guarda el último error ocurrido durante el envío del correo para mostrarlo en el frontend
    public static $lastMailError = '';

    /** Lista todas las instalaciones con datos relacionados */
    public static function getAll() {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            SELECT 
                i.id_instalacion,
                i.id_servicio,
                i.id_tecnico,
                i.fecha_instalacion,
                i.componentes_instalados,
                i.estado,
                (i.verificado + 0) as verificado,
                i.comentarios,
                i.creado_en,
                i.actualizado_en,
                s.tipo as servicio_tipo,
                u.nombre_unidad,
                c.nombre as nombre_cliente,
                c.id_cliente,
                t.nombre as nombre_tecnico
            FROM instalaciones i
            INNER JOIN servicios s ON i.id_servicio = s.id_servicio
            INNER JOIN unidades u ON s.id_unidad = u.id_unidad
            INNER JOIN clientes c ON u.id_cliente = c.id_cliente
            INNER JOIN tecnicos t ON i.id_tecnico = t.id_tecnico
            ORDER BY i.fecha_instalacion DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Obtiene una instalación por ID */
    public static function getById($id) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            SELECT 
                i.id_instalacion,
                i.id_servicio,
                i.id_tecnico,
                i.fecha_instalacion,
                i.componentes_instalados,
                i.estado,
                (i.verificado + 0) as verificado,
                i.comentarios,
                i.creado_en,
                i.actualizado_en,
                s.tipo as servicio_tipo,
                u.nombre_unidad,
                u.id_unidad,
                c.nombre as nombre_cliente,
                c.id_cliente,
                c.email as email_cliente,
                t.nombre as nombre_tecnico,
                t.correo as correo_tecnico,
                t.telefono as telefono_tecnico
            FROM instalaciones i
            INNER JOIN servicios s ON i.id_servicio = s.id_servicio
            INNER JOIN unidades u ON s.id_unidad = u.id_unidad
            INNER JOIN clientes c ON u.id_cliente = c.id_cliente
            INNER JOIN tecnicos t ON i.id_tecnico = t.id_tecnico
            WHERE i.id_instalacion = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Crea una nueva instalación */
    public static function create($data) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            INSERT INTO instalaciones 
                (id_servicio, id_tecnico, fecha_instalacion, componentes_instalados, estado, comentarios, verificado)
            VALUES 
                (:id_servicio, :id_tecnico, :fecha_instalacion, :componentes_instalados, :estado, :comentarios, 0)
        ");

        $stmt->bindParam(':id_servicio',           $data['id_servicio']);
        $stmt->bindParam(':id_tecnico',            $data['id_tecnico']);
        $stmt->bindParam(':fecha_instalacion',     $data['fecha_instalacion']);
        $stmt->bindParam(':componentes_instalados', $data['componentes_instalados']);
        $stmt->bindParam(':estado',                $data['estado']);
        $stmt->bindParam(':comentarios',           $data['comentarios']);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        throw new Exception("Error al crear la instalación");
    }

    /** Actualiza una instalación */
    public static function update($id, $data) {
        $db = Conexion::conectar();
        
        $allowed = ['id_servicio', 'id_tecnico', 'fecha_instalacion', 'componentes_instalados', 'estado', 'comentarios', 'verificado'];
        $fields = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = :$key";
            }
        }

        if (empty($fields)) return false;

        $query = "UPDATE instalaciones SET " . implode(', ', $fields) . ", actualizado_en = current_timestamp() WHERE id_instalacion = :id_instalacion";
        $stmt = $db->prepare($query);
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $stmt->bindValue(":$key", $value);
            }
        }
        $stmt->bindValue(':id_instalacion', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /** Elimina una instalación */
    public static function delete($id) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("DELETE FROM instalaciones WHERE id_instalacion = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /** Verifica si la unidad asociada tiene una instalación previa dentro del periodo del servicio */
    public static function hasInstallationInServicePeriod($id_servicio) {
        $db = Conexion::conectar();
        
        // Obtener id_unidad y fechas del servicio
        $stmt_servicio = $db->prepare("SELECT id_unidad, fecha_inicio, fecha_fin FROM servicios WHERE id_servicio = :id_servicio");
        $stmt_servicio->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $stmt_servicio->execute();
        $servicio = $stmt_servicio->fetch(PDO::FETCH_ASSOC);
        
        if (!$servicio || empty($servicio['fecha_inicio'])) return false;
        
        $id_unidad = $servicio['id_unidad'];
        $fecha_inicio = $servicio['fecha_inicio'];
        $fecha_fin = $servicio['fecha_fin'];
        
        // Comprobar instalaciones previas para esta unidad dentro del periodo del servicio
        $query = "
            SELECT i.id_instalacion 
            FROM instalaciones i
            INNER JOIN servicios s ON i.id_servicio = s.id_servicio
            WHERE s.id_unidad = :id_unidad
            AND i.fecha_instalacion >= :fecha_inicio
        ";
        
        if (!empty($fecha_fin)) {
            $query .= " AND i.fecha_instalacion <= :fecha_fin";
        }
        $query .= " LIMIT 1";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_unidad', $id_unidad, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        if (!empty($fecha_fin)) {
            $stmt->bindParam(':fecha_fin', $fecha_fin);
        }
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /** Envía por correo la ficha técnica de la instalación al técnico asignado */
    public static function enviarFichaTecnica($id_instalacion) {
        $mail = null;
        try {
            $inst = self::getById($id_instalacion);
            if (!$inst) return false;

            $tecnico_email = $inst['correo_tecnico'] ?? null;
            if (!$tecnico_email) return false;

            $db = Conexion::conectar();
            $stmtCompany = $db->prepare("SELECT nombre, correo FROM empresa LIMIT 1");
            $stmtCompany->execute();
            $empresa = $stmtCompany->fetch(PDO::FETCH_ASSOC);

            $fromName = $empresa ? $empresa['nombre'] : 'Saturno Sistema';

            $subject = "Ficha Técnica de Instalación #" . $inst['id_instalacion'] . " - " . $inst['nombre_unidad'];

            // Construir de forma dinámica la URL base del servidor
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . $host . '/saturno';

            // Build a stunning HTML body with CSS styling
            $body = '
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: \'Outfit\', Arial, sans-serif; background-color: #f3f4f6; color: #1f2937; margin: 0; padding: 20px; }
                    .container { max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 30px; margin: 0 auto; border: 1px solid #e5e7eb; }
                    .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 20px; }
                    .header h2 { color: #4f46e5; margin: 0; font-size: 24px; }
                    .header p { color: #6b7280; margin: 5px 0 0 0; font-size: 14px; }
                    .section-title { font-weight: 600; color: #1f2937; font-size: 16px; margin-top: 25px; margin-bottom: 10px; border-left: 4px solid #4f46e5; padding-left: 10px; }
                    .footer { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>Ficha Técnica de Instalación</h2>
                        <p>Asignación de servicio técnico y detalles del equipo</p>
                    </div>
                    
                    <div class="section-title">Detalles Generales</div>
                    <table width="100%" cellpadding="6" cellspacing="0" style="margin-bottom: 20px; border-collapse: collapse;">
                        <tr style="background-color: #f9fafb;">
                            <td width="35%" style="font-size: 13px; color: #6b7280; font-weight: 500; border-bottom: 1px solid #f3f4f6;">Folio Instalación:</td>
                            <td style="font-size: 14px; font-weight: bold; color: #111827; border-bottom: 1px solid #f3f4f6;">#' . $inst['id_instalacion'] . '</td>
                        </tr>
                        <tr>
                            <td style="font-size: 13px; color: #6b7280; font-weight: 500; border-bottom: 1px solid #f3f4f6;">Fecha Programada:</td>
                            <td style="font-size: 14px; font-weight: 600; color: #111827; border-bottom: 1px solid #f3f4f6;">' . $inst['fecha_instalacion'] . '</td>
                        </tr>
                        <tr style="background-color: #f9fafb;">
                            <td style="font-size: 13px; color: #6b7280; font-weight: 500; border-bottom: 1px solid #f3f4f6;">Estado:</td>
                            <td style="border-bottom: 1px solid #f3f4f6; font-size: 14px; font-weight: 600;">' . strtoupper(str_replace('_', ' ', $inst['estado'])) . '</td>
                        </tr>
                    </table>

                    <div class="section-title">Información del Cliente y Unidad</div>
                    <table width="100%" cellpadding="6" cellspacing="0" style="margin-bottom: 20px; border-collapse: collapse;">
                        <tr style="background-color: #f9fafb;">
                            <td width="35%" style="font-size: 13px; color: #6b7280; font-weight: 500; border-bottom: 1px solid #f3f4f6;">Cliente:</td>
                            <td style="font-size: 14px; font-weight: 600; color: #111827; border-bottom: 1px solid #f3f4f6;">' . htmlspecialchars($inst['nombre_cliente']) . ' (ID: ' . $inst['id_cliente'] . ')</td>
                        </tr>
                        <tr>
                            <td style="font-size: 13px; color: #6b7280; font-weight: 500; border-bottom: 1px solid #f3f4f6;">Unidad:</td>
                            <td style="font-size: 14px; font-weight: 600; color: #111827; border-bottom: 1px solid #f3f4f6;">' . htmlspecialchars($inst['nombre_unidad']) . ' (ID: ' . $inst['id_unidad'] . ')</td>
                        </tr>
                        <tr style="background-color: #f9fafb;">
                            <td style="font-size: 13px; color: #6b7280; font-weight: 500; border-bottom: 1px solid #f3f4f6;">Tipo de Servicio:</td>
                            <td style="font-size: 14px; font-weight: 600; color: #111827; border-bottom: 1px solid #f3f4f6; text-transform: capitalize;">' . htmlspecialchars($inst['servicio_tipo']) . '</td>
                        </tr>
                    </table>

                    <div class="section-title">Técnico Asignado</div>
                    <table width="100%" cellpadding="6" cellspacing="0" style="margin-bottom: 20px; border-collapse: collapse;">
                        <tr style="background-color: #f9fafb;">
                            <td width="35%" style="font-size: 13px; color: #6b7280; font-weight: 500; border-bottom: 1px solid #f3f4f6;">Nombre Técnico:</td>
                            <td style="font-size: 14px; font-weight: 600; color: #111827; border-bottom: 1px solid #f3f4f6;">' . htmlspecialchars($inst['nombre_tecnico']) . '</td>
                        </tr>
                        <tr>
                            <td style="font-size: 13px; color: #6b7280; font-weight: 500; border-bottom: 1px solid #f3f4f6;">Correo Electrónico:</td>
                            <td style="font-size: 14px; font-weight: 600; color: #4f46e5; border-bottom: 1px solid #f3f4f6;">' . htmlspecialchars($inst['correo_tecnico']) . '</td>
                        </tr>
                    </table>

                    <div class="section-title">Componentes y Observaciones</div>
                    <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                        <div style="font-size: 12px; color: #6b7280; font-weight: 500; margin-bottom: 4px;">Componentes Instalados:</div>
                        <div style="font-size: 14px; color: #111827; font-weight: 500; margin-bottom: 15px;">' . ($inst['componentes_instalados'] ? htmlspecialchars($inst['componentes_instalados']) : 'Ninguno especificado') . '</div>
                        
                        <div style="font-size: 12px; color: #6b7280; font-weight: 500; margin-bottom: 4px;">Comentarios / Indicaciones:</div>
                        <div style="font-size: 14px; color: #374151; font-style: italic;">' . ($inst['comentarios'] ? nl2br(htmlspecialchars($inst['comentarios'])) : 'Sin comentarios adicionales') . '</div>
                    </div>

                    <!-- Botón de Aceptación con estéticas premium -->
                    <div style="text-align: center; margin: 35px 0; background-color: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 8px; padding: 25px;">
                        <p style="font-size: 14px; color: #4b5563; margin-top: 0; margin-bottom: 15px; font-weight: 500;">
                            ¿Aceptas realizar este servicio de instalación?
                        </p>
                        <a href="' . $baseUrl . '/instalaciones/aceptar.php?id=' . $inst['id_instalacion'] . '" 
                           style="background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px; display: inline-block; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25); border: 1px solid #4338ca;">
                            Aceptar y Confirmar Instalación
                        </a>
                    </div>

                    <div class="footer">
                        Este es un correo automático enviado por el sistema de gestión de ' . htmlspecialchars($fromName) . '.<br>
                        Por favor, no respondas a este mensaje directamente.
                    </div>
                </div>
            </body>
            </html>
            ';

            $mail = new PHPMailer(true);

            // Capturar la conversación SMTP detallada para depuración
            $debugOutput = "";
            $mail->SMTPDebug = 2; // SMTP::DEBUG_SERVER
            $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
                $debugOutput .= trim($str) . " | ";
            };

            // Configuración del Servidor
            $mail->isSMTP();
            $mail->Host       = self::SMTP_HOST;
            $mail->SMTPAuth   = self::SMTP_AUTH;
            $mail->Username   = self::SMTP_USER;
            $mail->Password   = str_replace(' ', '', self::SMTP_PASS);

            // Selección de cifrado según puerto
            if (self::SMTP_PORT == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif (self::SMTP_PORT == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = self::SMTP_SECURE;
            }
            $mail->Port       = self::SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // Destinatarios y Remitente
            $mail->setFrom(self::SMTP_USER, $fromName);
            $mail->addAddress($tecnico_email, $inst['nombre_tecnico']);

            // Responder a (correo real de la empresa) si está configurado
            if ($empresa && !empty($empresa['correo'])) {
                $mail->addReplyTo($empresa['correo'], $fromName);
            }

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            $mailErrorInfo = ($mail ? $mail->ErrorInfo : '');
            self::$lastMailError = $e->getMessage() . ($mailErrorInfo ? " (Detalle: " . $mailErrorInfo . ")" : "") . (!empty($debugOutput) ? " / SMTP Logs: " . $debugOutput : "");
            error_log("Error al enviar correo de instalación (PHPMailer): " . self::$lastMailError);
            return false;
        }
    }

    /** Marca una instalación como verificada/aceptada por el técnico */
    public static function marcarComoAceptada($id) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE instalaciones SET verificado = 1, actualizado_en = CURRENT_TIMESTAMP() WHERE id_instalacion = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Envía una confirmación al cliente informándole los datos del técnico */
    public static function enviarCorreoConfirmacionCliente($id_instalacion) {
        $mail = null;
        try {
            $inst = self::getById($id_instalacion);
            if (!$inst) return false;

            $cliente_email = $inst['email_cliente'] ?? null;
            if (!$cliente_email) return false;

            $db = Conexion::conectar();
            $stmtCompany = $db->prepare("SELECT nombre, correo FROM empresa LIMIT 1");
            $stmtCompany->execute();
            $empresa = $stmtCompany->fetch(PDO::FETCH_ASSOC);

            $fromName = $empresa ? $empresa['nombre'] : 'Saturno Sistema';

            $subject = "Confirmación de Instalación Programada - " . $fromName;

            // Formatear detalles de comentarios si existen
            $observaciones = $inst['comentarios'] 
                ? '<div style="font-size: 14px; color: #374151; font-style: italic;">' . nl2br(htmlspecialchars($inst['comentarios'])) . '</div>'
                : '<div style="font-size: 14px; color: #6b7280; font-style: italic;">Sin observaciones adicionales</div>';

            // Beautiful HTML body for the client email
            $body = '
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: \'Outfit\', Arial, sans-serif; background-color: #f3f4f6; color: #1f2937; margin: 0; padding: 20px; }
                    .container { max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); padding: 35px; margin: 0 auto; border: 1px solid #e5e7eb; }
                    .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 25px; }
                    .header h2 { color: #10b981; margin: 0; font-size: 24px; }
                    .header p { color: #6b7280; margin: 5px 0 0 0; font-size: 14px; }
                    .welcome { font-size: 16px; line-height: 1.5; color: #374151; margin-bottom: 20px; }
                    .welcome strong { color: #111827; }
                    .info-box { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
                    .info-title { font-weight: bold; font-size: 14px; color: #111827; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
                    .detail-row { display: flex; margin-bottom: 8px; font-size: 14px; }
                    .detail-row:last-child { margin-bottom: 0; }
                    .detail-label { width: 35%; color: #6b7280; font-weight: 500; }
                    .detail-value { width: 65%; color: #111827; font-weight: 600; }
                    .footer { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>¡Servicio Confirmado!</h2>
                        <p>Tu solicitud de instalación ha sido programada y aceptada</p>
                    </div>
                    
                    <div class="welcome">
                        Estimado/a <strong>' . htmlspecialchars($inst['nombre_cliente']) . '</strong>,<br><br>
                        Le informamos que la solicitud de servicio para su unidad <strong>' . htmlspecialchars($inst['nombre_unidad']) . '</strong> ha sido aceptada por el técnico asignado y se encuentra programada de forma exitosa.
                    </div>
                    
                    <div class="info-box">
                        <div class="info-title">Detalles de la Instalación</div>
                        <table width="100%" cellpadding="4" cellspacing="0" style="border-collapse: collapse; font-size: 14px;">
                            <tr>
                                <td width="35%" style="color: #6b7280; font-weight: 500; padding: 4px 0;">Tipo de Servicio:</td>
                                <td style="color: #111827; font-weight: bold; padding: 4px 0; text-transform: capitalize;">' . htmlspecialchars($inst['servicio_tipo']) . '</td>
                            </tr>
                            <tr>
                                <td style="color: #6b7280; font-weight: 500; padding: 4px 0;">Fecha Programada:</td>
                                <td style="color: #111827; font-weight: bold; padding: 4px 0;">' . $inst['fecha_instalacion'] . '</td>
                            </tr>
                        </table>
                    </div>

                    <div class="info-box" style="border-left: 4px solid #10b981;">
                        <div class="info-title" style="color: #10b981;">Datos del Técnico Asignado</div>
                        <p style="font-size: 13px; color: #6b7280; margin-top: 0; margin-bottom: 12px;">A continuación se detallan los datos del técnico certificado que acudirá a realizar el servicio a su unidad:</p>
                        <table width="100%" cellpadding="4" cellspacing="0" style="border-collapse: collapse; font-size: 14px;">
                            <tr>
                                <td width="35%" style="color: #6b7280; font-weight: 500; padding: 4px 0;">Nombre del Técnico:</td>
                                <td style="color: #111827; font-weight: bold; padding: 4px 0;">' . htmlspecialchars($inst['nombre_tecnico']) . '</td>
                            </tr>
                            <tr>
                                <td style="color: #6b7280; font-weight: 500; padding: 4px 0;">Teléfono de Contacto:</td>
                                <td style="color: #111827; font-weight: bold; padding: 4px 0;">' . ($inst['telefono_tecnico'] ? htmlspecialchars($inst['telefono_tecnico']) : 'No especificado') . '</td>
                            </tr>
                            <tr>
                                <td style="color: #6b7280; font-weight: 500; padding: 4px 0;">Correo Electrónico:</td>
                                <td style="color: #4f46e5; font-weight: bold; padding: 4px 0;">' . htmlspecialchars($inst['correo_tecnico']) . '</td>
                            </tr>
                        </table>
                    </div>

                    <div class="info-box">
                        <div class="info-title">Observaciones</div>
                        ' . $observaciones . '
                    </div>

                    <div class="welcome" style="font-size: 14px; background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 6px; color: #166534;">
                        <strong>Nota importante:</strong> Por favor asegúrese de tener la unidad lista y disponible en el horario pactado para facilitar las tareas del técnico. ¡Muchas gracias por su preferencia!
                    </div>

                    <div class="footer">
                        Este es un correo automático enviado en nombre de ' . htmlspecialchars($fromName) . '.<br>
                        Si tiene alguna duda, comuníquese con soporte de la empresa.
                    </div>
                </div>
            </body>
            </html>
            ';

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = self::SMTP_HOST;
            $mail->SMTPAuth   = self::SMTP_AUTH;
            $mail->Username   = self::SMTP_USER;
            $mail->Password   = str_replace(' ', '', self::SMTP_PASS);

            if (self::SMTP_PORT == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif (self::SMTP_PORT == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = self::SMTP_SECURE;
            }
            $mail->Port       = self::SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(self::SMTP_USER, $fromName);
            $mail->addAddress($cliente_email, $inst['nombre_cliente']);

            if ($empresa && !empty($empresa['correo'])) {
                $mail->addReplyTo($empresa['correo'], $fromName);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            $mailErrorInfo = ($mail ? $mail->ErrorInfo : '');
            error_log("Error al enviar correo de confirmación al cliente: " . $e->getMessage() . " (PHPMailer: " . $mailErrorInfo . ")");
            return false;
        }
    }
}
?>
