<?php
    // http://app.trasladosuniversales.com.mx/getHistorialRegistrosPatio.php? 
    // fecha_final = 2025-04-01 00:00:00
    // fecha_final = 2025-04-30 00:00:00

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json');

    include_once __DIR__ . '/../geo/codigo/pdo.php'; 
    $conn = conectaDB();

    if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_final'])) {
        echo json_encode(["success" => false, "message" => "Debes enviar fecha_inicio y fecha_final"]);
        exit;
    }

    $fechaInicio = $_GET['fecha_inicio'] . " 00:00:00";
    $fechaFinal = $_GET['fecha_final'] . " 23:59:59";


    $sql = "SELECT * FROM registros_patio 
        WHERE (
        fecha_entrada BETWEEN :fecha_inicio1 AND :fecha_final1
        OR fecha_salida BETWEEN :fecha_inicio2 AND :fecha_final2
        OR fecha BETWEEN :fecha_inicio3 AND :fecha_final3
        )
        AND estado = 1
        ORDER BY id_registro DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':fecha_inicio1', $fechaInicio);
    $stmt->bindValue(':fecha_final1', $fechaFinal);
    $stmt->bindValue(':fecha_inicio2', $fechaInicio);
    $stmt->bindValue(':fecha_final2', $fechaFinal);
    $stmt->bindValue(':fecha_inicio3', $fechaInicio);
    $stmt->bindValue(':fecha_final3', $fechaFinal);

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
