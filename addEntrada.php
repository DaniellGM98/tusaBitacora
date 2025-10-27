<?php
    // http://app.trasladosuniversales.com.mx/addEntrada.php? 
    // fm=2105 & 
    // n=Daniel Guerrero & 
    // e=DDSMedia & 
    // ev=1 & 
    // fe=2025-04-25 00:00:00.000 & 
    // fs=NULL & 
    // f=2025-04-25 00:00:00.000 & 
    // g=Vigilante 1 & 
    // fi=1 & 
    // v=OT-12345 & 
    // m=Kenworth w900 &
    // ll=1234567 &
    // d=Corona
    // nu=5
    // o=Sahagun
    // mi=Modelo1
    // cmi=200cm
    // md=Modelo2
    // cmd=200cm
    // t=Tipo1
    // ni=1/2
    // eq=Sin equipamiento
    // es=1 
    // ob=observaciones

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json');
	    
    include __DIR__ . '/../geo/codigo/pdo.php';

	date_default_timezone_set('America/Mexico_City');

    // Leer el cuerpo de la solicitud
    $json = file_get_contents('php://input');

    // Decodificar el JSON a un array asociativo
    $data = json_decode($json, true);

    $fk_matricula       = isset($data['fm']) ? trim($data['fm']) : NULL;
    $nombre             = isset($data['n']) ? trim($data['n']) : NULL;
    $empresa_externo    = isset($data['e']) ? trim($data['e']) : NULL;
    $evidencia_externo  = isset($data['ev']) ? trim($data['ev']) : NULL;
    $fecha_entrada      = isset($data['fe']) ? trim($data['fe']) : NULL;
    $fecha_salida       = isset($data['fs']) ? trim($data['fs']) : NULL;
    $fecha              = isset($data['f']) ? trim($data['f']) : NULL;
    $guardia            = isset($data['g']) ? trim($data['g']) : NULL;
    $fila               = isset($data['fi']) ? trim($data['fi']) : NULL;
    $VIN                = isset($data['v']) ? trim($data['v']) : NULL;
    $modelo_unidad      = isset($data['m']) ? trim($data['m']) : NULL;
    $llave              = isset($data['ll']) ? trim($data['ll']) : NULL;
    $distribuidor       = isset($data['d']) ? trim($data['d']) : NULL;
    $num_ejes           = isset($data['nu']) ? trim($data['nu']) : NULL;
    $origen             = isset($data['o']) ? trim($data['o']) : NULL;
    $modelo_izq         = isset($data['mi']) ? trim($data['mi']) : NULL;
    $cm_tanque_izq      = isset($data['cmi']) ? trim($data['cmi']) : NULL;
    $modelo_der         = isset($data['md']) ? trim($data['md']) : NULL;
    $cm_tanque_der      = isset($data['cmd']) ? trim($data['cmd']) : NULL;
    $tipo_tanque        = isset($data['t']) ? trim($data['t']) : NULL;
    $nivel_urea         = isset($data['ni']) ? trim($data['ni']) : NULL;
    $equipamiento       = isset($data['eq']) ? trim($data['eq']) : NULL;
    $observaciones      = isset($data['ob']) ? trim($data['ob']) : NULL;
        
    $conn = conectaDB();

    // Verificar si ya existe un registro con el mismo VIN y fecha_entrada
    $checkSQL = "SELECT COUNT(*) FROM registros_Patio WHERE VIN = :vin AND fecha_entrada = :fecha_entrada";
    $checkStmt = $conn->prepare($checkSQL);
    $checkStmt->bindParam(':vin', $VIN);
    $checkStmt->bindParam(':fecha_entrada', $fecha_entrada);
    $checkStmt->execute();
    $existe = $checkStmt->fetchColumn();

    if ($existe > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Ya existe un registro con ese VIN y fecha de entrada"
        ]);
        exit;
    }

    $SQLil = "INSERT INTO registros_Patio (fk_matricula, nombre, empresa_externo, evidencia_externo, fecha_entrada, fecha_salida, fecha, guardia, fila, VIN, modelo_unidad, llave, distribuidor, num_ejes, origen, modelo_izq, cm_tanque_izq, modelo_der, cm_tanque_der, tipo_tanque, nivel_urea, equipamiento, estado, observaciones) VALUES (:fk_matricula, :nombre, :empresa_externo, :evidencia_externo, :fecha_entrada, :fecha_salida, :fecha, :guardia, :fila, :VIN, :modelo_unidad, :llave, :distribuidor, :num_ejes, :origen, :modelo_izq, :cm_tanque_izq, :modelo_der, :cm_tanque_der, :tipo_tanque, :nivel_urea, :equipamiento, 1, :observaciones)";

    $ql = $conn->prepare($SQLil);    
    $ql->bindParam(':fk_matricula', $fk_matricula);
    $ql->bindParam(':nombre', $nombre);
    $ql->bindParam(':empresa_externo', $empresa_externo);
    $ql->bindParam(':evidencia_externo', $evidencia_externo);
    $ql->bindParam(':fecha_entrada', $fecha_entrada);
    $ql->bindParam(':fecha_salida', $fecha_salida);
    $ql->bindParam(':fecha', $fecha);
    $ql->bindParam(':guardia', $guardia);
    $ql->bindParam(':fila', $fila);
    $ql->bindParam(':VIN', $VIN);
    $ql->bindParam(':modelo_unidad', $modelo_unidad);
    $ql->bindParam(':llave', $llave);
    $ql->bindParam(':distribuidor', $distribuidor);
    $ql->bindParam(':num_ejes', $num_ejes);
    $ql->bindParam(':origen', $origen);
    $ql->bindParam(':modelo_izq', $modelo_izq);
    $ql->bindParam(':cm_tanque_izq', $cm_tanque_izq);
    $ql->bindParam(':modelo_der', $modelo_der);
    $ql->bindParam(':cm_tanque_der', $cm_tanque_der);
    $ql->bindParam(':tipo_tanque', $tipo_tanque);
    $ql->bindParam(':nivel_urea', $nivel_urea);
    $ql->bindParam(':equipamiento', $equipamiento);
    $ql->bindParam(':observaciones', $observaciones);

    if ($ql->execute()) {
        if (!empty($fecha_salida)) {
            $updateSQL = "UPDATE registros_Patio 
                          SET fecha_salida = :fecha_salida 
                          WHERE VIN = :vin 
                            AND (fecha_salida IS NULL OR fecha_salida = '')";
            $updateStmt = $conn->prepare($updateSQL);
            $updateStmt->bindParam(':fecha_salida', $fecha_salida);
            $updateStmt->bindParam(':vin', $VIN);
            $updateStmt->execute();
        }
        echo json_encode(["success" => true, "message" => "Registro insertado"]);
    } else {
        $errorInfo = $ql->errorInfo();
        echo json_encode([
            "success" => false,
            "message" => "Error al insertar",
            "sqlstate" => $errorInfo[0],
            "driver_error_code" => $errorInfo[1],
            "driver_error_message" => $errorInfo[2]
        ]);
    }
?>
