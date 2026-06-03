<?php
require_once __DIR__ . '/../models/Instalacion.php';

class InstalacionController {

    public function index() {
        try {
            $data = Instalacion::getAll();
            echo json_encode($data);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function show() {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido']);
            return;
        }
        try {
            $data = Instalacion::getById($_GET['id']);
            if ($data) {
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'No encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id_servicio']) || !isset($data['id_tecnico']) || !isset($data['fecha_instalacion'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
            return;
        }

        try {
            // Validar si la unidad ya tiene una instalación en el periodo del servicio
            if (Instalacion::hasInstallationInServicePeriod($data['id_servicio'])) {
                echo json_encode(['success' => false, 'message' => "La unidad ya tiene una instalación registrada dentro del periodo de fechas (inicial y final) del servicio."]);
                return;
            }

            $id = Instalacion::create($data);
            
            // Enviar ficha técnica por correo al técnico asignado
            $correoEnviado = Instalacion::enviarFichaTecnica($id);
            
            if (isset($data['estado']) && $data['estado'] === 'completada') {
                // Facturación automática eliminada para dar paso a Facturación Múltiple
            }

            echo json_encode([
                'success' => true, 
                'id' => $id, 
                'correo_enviado' => $correoEnviado,
                'message' => $correoEnviado 
                    ? 'Instalación guardada y ficha técnica enviada por correo al técnico asignado.' 
                    : 'Instalación guardada, pero falló el envío del correo de la ficha técnica al técnico. Detalle: ' . Instalacion::$lastMailError
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update() {
        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$id && isset($data['id_instalacion'])) $id = $data['id_instalacion'];

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        try {
            $currentInst = Instalacion::getById($id);
            $wasPending = ($currentInst && $currentInst['estado'] !== 'completada');
            $isChangingToCompleted = (isset($data['estado']) && $data['estado'] === 'completada');

            if (Instalacion::update($id, $data)) {
                // Enviar ficha técnica actualizada por correo al técnico asignado
                $correoEnviado = Instalacion::enviarFichaTecnica($id);

                if ($wasPending && $isChangingToCompleted) {
                    // Facturación automática eliminada para dar paso a Facturación Múltiple
                }

                echo json_encode([
                    'success' => true, 
                    'correo_enviado' => $correoEnviado,
                    'message' => $correoEnviado 
                        ? 'Actualizado correctamente y correo enviado al técnico asignado.' 
                        : 'Actualizado correctamente, pero falló el envío del correo de la ficha técnica al técnico. Detalle: ' . Instalacion::$lastMailError
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id_instalacion'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        try {
            if (Instalacion::delete($id)) {
                echo json_encode(['success' => true, 'message' => 'Eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró el registro']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /** Acción para cuando el técnico acepta la instalación desde el correo */
    public function aceptar() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Content-Type: text/html; charset=UTF-8');
            http_response_code(400);
            echo "<h2>Error: ID de instalación no especificado.</h2>";
            return;
        }

        try {
            $inst = Instalacion::getById($id);
            if (!$inst) {
                header('Content-Type: text/html; charset=UTF-8');
                http_response_code(404);
                echo "<h2>Error: La instalación no existe.</h2>";
                return;
            }

            $alreadyVerified = ($inst['verificado'] == 1);
            $mailEnviado = false;

            if (!$alreadyVerified) {
                // 1. Actualizar el campo verificado de 0 a 1 en la tabla de instalaciones
                Instalacion::marcarComoAceptada($id);
                // 2. Enviar correo al cliente con los datos del técnico asignado
                $mailEnviado = Instalacion::enviarCorreoConfirmacionCliente($id);
            }

            // Renderizar una página HTML5 con diseño premium y estéticas modernas
            header('Content-Type: text/html; charset=UTF-8');
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?php echo $alreadyVerified ? 'Instalación Confirmada Previamente' : 'Instalación Confirmada Exitosamente'; ?></title>
                <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                <style>
                    :root {
                        --bg-gradient: radial-gradient(circle at top, #1e1b4b, #0f172a);
                        --glass-bg: rgba(255, 255, 255, 0.03);
                        --glass-border: rgba(255, 255, 255, 0.08);
                        --text-main: #f8fafc;
                        --text-muted: #94a3b8;
                        --primary: #4f46e5;
                        --success: #10b981;
                    }
                    * { box-sizing: border-box; margin: 0; padding: 0; }
                    body {
                        font-family: 'Outfit', sans-serif;
                        background: var(--bg-gradient);
                        min-height: 100vh;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 20px;
                        color: var(--text-main);
                        overflow-x: hidden;
                    }
                    .container {
                        max-width: 550px;
                        width: 100%;
                        background: var(--glass-bg);
                        backdrop-filter: blur(20px);
                        -webkit-backdrop-filter: blur(20px);
                        border: 1px solid var(--glass-border);
                        border-radius: 24px;
                        padding: 40px;
                        text-align: center;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                        animation: fadeIn 0.8s ease-out;
                        position: relative;
                    }
                    .container::before {
                        content: '';
                        position: absolute;
                        top: -10%;
                        left: 50%;
                        transform: translateX(-50%);
                        width: 80%;
                        height: 100px;
                        background: radial-gradient(50% 50% at 50% 50%, rgba(79, 70, 229, 0.15) 0%, rgba(79, 70, 229, 0) 100%);
                        z-index: -1;
                        pointer-events: none;
                    }
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(20px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    .icon-wrapper {
                        width: 88px;
                        height: 88px;
                        background: rgba(16, 185, 129, 0.1);
                        border: 2px solid rgba(16, 185, 129, 0.3);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 25px;
                        box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
                        position: relative;
                    }
                    .icon-wrapper svg {
                        width: 42px;
                        height: 42px;
                        stroke: var(--success);
                        stroke-width: 3.5;
                        stroke-linecap: round;
                        stroke-linejoin: round;
                        fill: none;
                        stroke-dasharray: 100;
                        stroke-dashoffset: 100;
                        animation: drawCheck 0.6s 0.2s ease-out forwards;
                    }
                    @keyframes drawCheck {
                        to { stroke-dashoffset: 0; }
                    }
                    h1 {
                        font-size: 32px;
                        font-weight: 700;
                        letter-spacing: -0.02em;
                        margin-bottom: 12px;
                        background: linear-gradient(135deg, #ffffff 30%, #e2e8f0 100%);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                    }
                    p.subtitle {
                        font-size: 16px;
                        color: var(--text-muted);
                        line-height: 1.6;
                        margin-bottom: 30px;
                    }
                    .details-card {
                        background: rgba(255, 255, 255, 0.02);
                        border: 1px solid rgba(255, 255, 255, 0.04);
                        border-radius: 16px;
                        padding: 24px;
                        margin-bottom: 30px;
                        text-align: left;
                    }
                    .details-title {
                        font-size: 13px;
                        font-weight: 600;
                        color: var(--text-muted);
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        margin-bottom: 16px;
                        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
                        padding-bottom: 8px;
                    }
                    .detail-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 12px;
                        font-size: 15px;
                    }
                    .detail-row:last-child { margin-bottom: 0; }
                    .detail-label { color: var(--text-muted); }
                    .detail-value { font-weight: 600; color: var(--text-main); }
                    .success-banner {
                        background: rgba(16, 185, 129, 0.06);
                        border: 1px solid rgba(16, 185, 129, 0.15);
                        border-radius: 12px;
                        padding: 16px;
                        font-size: 14px;
                        color: #34d399;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 10px;
                        margin-bottom: 10px;
                        line-height: 1.5;
                    }
                    .footer-brand {
                        font-size: 13px;
                        color: var(--text-muted);
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="icon-wrapper">
                        <svg viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h1><?php echo $alreadyVerified ? '¡Instalación ya Aceptada!' : '¡Instalación Aceptada!'; ?></h1>
                    <p class="subtitle">
                        <?php 
                        if ($alreadyVerified) {
                            echo "Esta instalación ya había sido aceptada y verificada previamente por ti. ¡Muchas gracias por tu compromiso!";
                        } else {
                            echo "Has confirmado y aceptado la ficha técnica de la instalación. Los detalles del servicio han quedado validados en el sistema.";
                        }
                        ?>
                    </p>
                    
                    <div class="details-card">
                        <div class="details-title">Detalles de la Instalación</div>
                        <div class="detail-row">
                            <span class="detail-label">Folio de Instalación:</span>
                            <span class="detail-value">#<?php echo $inst['id_instalacion']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Unidad:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($inst['nombre_unidad']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Fecha Programada:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($inst['fecha_instalacion']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Cliente:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($inst['nombre_cliente']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Técnico Asignado:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($inst['nombre_tecnico']); ?></span>
                        </div>
                    </div>

                    <div class="success-banner">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <?php 
                        if ($alreadyVerified) {
                            echo "El correo de confirmación con tus datos ya había sido enviado al cliente previamente.";
                        } else {
                            echo "Se ha enviado automáticamente un correo de confirmación al cliente con tus datos de contacto.";
                        }
                        ?>
                    </div>

                    <div class="footer-brand">
                        Saturno Sistema de Control y Gestión
                    </div>
                </div>
            </body>
            </html>
            <?php
        } catch (Exception $e) {
            header('Content-Type: text/html; charset=UTF-8');
            http_response_code(500);
            echo "<h2>Error interno: " . htmlspecialchars($e->getMessage()) . "</h2>";
        }
    }
}
?>
