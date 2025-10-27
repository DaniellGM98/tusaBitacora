<?php
    // http://app.trasladosuniversales.com.mx/editRegistro.php? 
    // id_registro=87 &
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
    // cmd=200cmn
    // t=Tipo1
    // ni=1/2
    // eq=Sin equipamiento
    // es=1 
    // ob=observaciones 

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json');
    
    include_once __DIR__ . '/../geo/codigo/pdo.php';
    date_default_timezone_set('America/Mexico_City');
    
    $conn = conectaDB();
    
    // Obtener datos del JSON o del arreglo $_REQUEST (GET/POST)
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Si no hay JSON, usar $_REQUEST
    if (!$data) {
        $data = $_REQUEST;
    }
    
    // Verificar si se recibe el id_registro
    if (isset($data['id_registro'])) {
        $id_registro = (int)$data['id_registro'];
    } else {
        echo json_encode(["success" => false, "message" => "No se recibió id_registro"]);
        exit;
    }
    
    // Mapeo de campos
    $fieldMapping = [
        'fm'  => 'fk_matricula',
        'n'   => 'nombre',
        'e'   => 'empresa_externo',
        'ev'  => 'evidencia_externo',
        'fe'  => 'fecha_entrada',
        'fs'  => 'fecha_salida',
        'f'   => 'fecha',
        'g'   => 'guardia',
        'fi'  => 'fila',
        'v'   => 'VIN',
        'm'   => 'modelo_unidad',
        'll'  => 'llave',
        'd'   => 'distribuidor',
        'nu'  => 'num_ejes',
        'o'   => 'origen',
        'mi'  => 'modelo_izq',
        'cmi' => 'cm_tanque_izq',
        'md'  => 'modelo_der',
        'cmd' => 'cm_tanque_der',
        't'   => 'tipo_tanque',
        'ni'  => 'nivel_urea',
        'eq'  => 'equipamiento',
        'es'  => 'estado',
        'ob'  => 'observaciones',
    ];
    
    // Detectar si vienen TODOS los campos (modo "forzar actualización")
    $allKeys = array_keys($fieldMapping);
    $hasAll = !array_diff($allKeys, array_keys($data));
    
    $setParts = [];
    $params = [];
    
    foreach ($fieldMapping as $short => $dbField) {
        // Si vienen todos los campos, actualiza todos, aunque vengan null o ""
        if ($hasAll) {
            $valor = isset($data[$short]) ? $data[$short] : null;
        } else {
            // Si no vienen todos, solo actualiza los que se mandaron
            if (!array_key_exists($short, $data)) {
                continue;
            }
            $valor = $data[$short];
        }
    
        // Normalizar null/""/NULL
        if (is_null($valor) || strtoupper(trim((string)$valor)) === 'NULL' || trim((string)$valor) === '') {
            $params[$dbField] = null;
        } else {
            $params[$dbField] = trim($valor);
        }
    
        $setParts[] = "$dbField = :$dbField";
    }
    
    // Si no hay datos válidos para actualizar
    if (count($setParts) === 0) {
        echo json_encode(["success" => false, "message" => "No se enviaron campos válidos para actualizar."]);
        exit;
    }
    
    // Construir SQL
    $sql = "UPDATE registros_Patio SET " . implode(", ", $setParts) . " WHERE id_registro = :id_registro";
    $stmt = $conn->prepare($sql);
    
    // Enlazar parámetros
    foreach ($params as $key => $value) {
        if ($value === null) {
            $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":$key", $value);
        }
    }
    $stmt->bindValue(":id_registro", $id_registro, PDO::PARAM_INT);
    
    // Ejecutar
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Registro actualizado correctamente"]);
    } else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode([
            "success" => false,
            "message" => "Error al actualizar",
            "sqlstate" => $errorInfo[0],
            "driver_error_code" => $errorInfo[1],
            "driver_error_message" => $errorInfo[2]
        ]);
    }
?>    