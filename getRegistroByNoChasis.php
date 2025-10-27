<?php
    // http://app.trasladosuniversales.com.mx/getRegistroByNoChasis.php? 
    // id = 80

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json; charset=utf-8');

    include_once __DIR__ . '/../geo/codigo/pdo.php'; 
    $conn = conectaDB();

    if (!isset($_GET['No_chasis'])) {
        echo json_encode(["success" => false, "message" => "Debes enviar ID"]);
        exit;
    }

    $No_chasis = $_GET['No_chasis'];

    $sql = "SELECT TOP 1 Personal.ID_matricula, Personal.Nombres, Personal.Ap_paterno, Orden_traslados.Modelo, r_socialesapp.id_razon, r_socialesapp.r_social, RTRIM(Codigos_vehiculos.ID_clave) as ID_clave, RTRIM(Codigos_vehiculos.Descripcion) as Descripcion, Directorio.ID_entidad, Directorio.Nombre as Origen_nombre
            FROM Orden_traslados
            INNER JOIN Directorio ON Orden_traslados.Origen = Directorio.ID_entidad
            INNER JOIN Personal ON Orden_traslados.ID_matricula = Personal.ID_matricula
            INNER JOIN r_socialesapp ON Orden_traslados.Cliente = r_socialesapp.id_razon
            INNER JOIN Codigos_vehiculos ON LTRIM(RTRIM(Orden_traslados.ID_clave)) = Codigos_vehiculos.ID_clave
            WHERE Orden_traslados.No_chasis = :No_chasis
            ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':No_chasis', $No_chasis);

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
