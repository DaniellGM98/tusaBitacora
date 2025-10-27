<?php
    // http://app.trasladosuniversales.com.mx/getRegistroByID.php? 
    // id = 80

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json');

    include_once __DIR__ . '/../geo/codigo/pdo.php'; 
    $conn = conectaDB();

    if (!isset($_GET['id_registro'])) {
        echo json_encode(["success" => false, "message" => "Debes enviar ID"]);
        exit;
    }

    $id_registro = $_GET['id_registro'];

    $sql = "SELECT * FROM registros_patio 
            WHERE id_registro = :id_registro
            AND estado = 1
            ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id_registro', $id_registro);

    if ($stmt->execute()) {
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $resultados
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode([
            "success" => false,
            "message" => "Error al consultar",
            "sqlstate" => $errorInfo[0],
            "driver_error_code" => $errorInfo[1],
            "driver_error_message" => $errorInfo[2]
        ]);
    }
?>
