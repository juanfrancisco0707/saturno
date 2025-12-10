<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// El cuerpo se envía como JSON, por lo que usamos php://input
$data = json_decode(file_get_contents("php://input"));
$id_instalacion = $data->id_instalacion ?? null;

if (!$id_instalacion) {
    echo json_encode(['success' => false, 'message' => 'ID de instalación no proporcionado.']);
    exit;
}

$con = Conexion::conectar();

try {
    // Iniciar una transacción para asegurar que todas las operaciones se completen o ninguna lo haga
    $con->beginTransaction();

    // --- PASO 1: Validar si la instalación se puede borrar ---
    $checkSql = "SELECT estado, id_servicio FROM instalaciones WHERE id_instalacion = :id";
    $stmtCheck = $con->prepare($checkSql);
    $stmtCheck->execute([':id' => $id_instalacion]);
    $instalacion = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$instalacion) {
        throw new Exception("La instalación con ID $id_instalacion no existe.");
    }
    
    // Buscar si el servicio asociado a esta instalación ya tiene una factura
    $servicioId = $instalacion['id_servicio'];
    $facturaSql = "SELECT id_factura FROM servicios WHERE id_servicio = :id_servicio AND id_factura IS NOT NULL";
    $stmtFactura = $con->prepare($facturaSql);
    $stmtFactura->execute([':id_servicio' => $servicioId]);
    
    if ($stmtFactura->fetch()) {
        throw new Exception("No se puede borrar. La instalación ya ha sido facturada.");
    }

    // Tampoco se puede borrar si ya está marcada como 'completada'
    if ($instalacion['estado'] == 'completada') {
        throw new Exception("No se puede borrar. La instalación ya está marcada como completada.");
    }

    // --- PASO 2: Obtener IDs de los checklist items para borrar evidencias ---
    $listaSql = "SELECT id_lista_verificacion FROM listas_verificacion_instalacion WHERE id_instalacion = :id";
    $stmtLista = $con->prepare($listaSql);
    $stmtLista->execute([':id' => $id_instalacion]);
    $items_a_borrar = $stmtLista->fetchAll(PDO::FETCH_COLUMN, 0);

    if (!empty($items_a_borrar)) {
        // --- PASO 3: Borrar evidencias asociadas a esos checklist items ---
        // (Opcional: aquí podrías agregar código para borrar los archivos físicos del servidor)
        $inQuery = implode(',', array_fill(0, count($items_a_borrar), '?'));
        $deleteEvidenciasSql = "DELETE FROM evidencias WHERE id_lista_verificacion IN ($inQuery)";
        $stmtDeleteEv = $con->prepare($deleteEvidenciasSql);
        $stmtDeleteEv->execute($items_a_borrar);
    }

    // --- PASO 4: Borrar los registros del checklist para esta instalación ---
    $deleteListaSql = "DELETE FROM listas_verificacion_instalacion WHERE id_instalacion = :id";
    $stmtDeleteLista = $con->prepare($deleteListaSql);
    $stmtDeleteLista->execute([':id' => $id_instalacion]);

    // --- PASO 5: Finalmente, borrar la instalación principal ---
    $deleteInstalacionSql = "DELETE FROM instalaciones WHERE id_instalacion = :id";
    $stmtDeleteInst = $con->prepare($deleteInstalacionSql);
    $stmtDeleteInst->execute([':id' => $id_instalacion]);

    // Si todo salió bien, confirmamos los cambios en la base de datos
    $con->commit();

    echo json_encode(['success' => true, 'message' => 'Instalación y datos asociados eliminados correctamente.']);

} catch (Exception $e) {
    // Si algo falló, revertimos todos los cambios
    if ($con->inTransaction()) {
        $con->rollBack();
    }
    // Enviamos el mensaje de error a la App
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>