        <?php
        require_once '../conexion.php';
        $data = json_decode(file_get_contents("php://input"));
        $id_usuario = $data->id_usuario;
        $token = $data->fcm_token;
        
        $con = Conexion::conectar();
        $stmt = $con->prepare("UPDATE usuarios SET fcm_token = :token WHERE id = :id");
        $stmt->execute([':token' => $token, ':id' => $id_usuario]);
        echo json_encode(['success' => true]);
        ?>
        