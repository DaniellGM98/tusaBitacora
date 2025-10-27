<?php
    // http://app.trasladosuniversales.com.mx/getRegistrosPatio.php? 

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json');

    include_once __DIR__ . '/../geo/codigo/pdo.php'; 
    $conn = conectaDB();
    
    // WHERE (fecha_entrada IS NOT NULL OR fecha IS NOT NULL) E y E/S
    $sql = "SELECT * FROM registros_patio 
            WHERE fecha_entrada IS NOT NULL
            AND fecha_salida IS NULL
            AND estado = 1
            ORDER BY fecha_entrada DESC";

    $stmt = $conn->prepare($sql);

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
