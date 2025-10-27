<?php
    // http://app.trasladosuniversales.com.mx/getNombreByMatricula.php? 
    // id = 1

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json; charset=utf-8');

    include_once __DIR__ . '/../geo/codigo/pdo.php'; 
    $conn = conectaDB();

    if (!isset($_GET['ID_matricula'])) {
        echo json_encode(["success" => false, "message" => "Debes enviar ID"]);
        exit;
    }

    $ID_matricula = $_GET['ID_matricula'];

    $sql = "SELECT TOP 1 
        Personal.ID_matricula,
        Personal.Nombres + ' ' + Personal.Ap_paterno + ' ' + Personal.Ap_materno AS NombreCompleto
        FROM Personal
        WHERE Personal.ID_matricula = :ID_matricula
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':ID_matricula', $ID_matricula);

    if ($stmt->execute()) {
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode([
                "success" => false,
                "message" => "No existe el registro"
            ]);
            exit;
        }

        foreach ($resultados as &$fila) {
            foreach ($fila as $clave => $valor) {
                if (is_string($valor)) {
                    $fila[$clave] = utf8_encode($valor);
                }
            }
        }        

        echo json_encode([
            "success" => true,
            "data" => $resultados
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
