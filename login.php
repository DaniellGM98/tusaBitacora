<?php
// http://app.trasladosuniversales.com.mx/app/bitacora/login.php? matricula=123 & pass=pass123



header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/db.php';

try {
    $matr = isset($_POST['m']) ? intval($_POST['m']) : 0;
    $pass = isset($_POST['p']) ? trim($_POST['p']) : '';

    //echo json_encode(["debug" => "POST recibido", "m" => $matr, "p" => $pass]); flush();

    if($pass=='Baja de la empresa'){
        echo json_encode([
            'status' => false, 
            'message' => 'Usuario dado de baja'
        ]);
        exit;
    }

    $conn = conectaDB();

    if (!$conn) {
        echo json_encode([
            'status' => false, 
            'message' => 'Error en la conexión']);
        exit;
    }

    //echo json_encode(["debug" => "Conexión establecida"]); flush();

    $sql = "SELECT * FROM usuarios2 WHERE ( Matricula = :matr1 AND Carpeta_SGC = 'Patio' ) OR ( ID_Usuario IN ('AndresM', 'CarlosF') AND Matricula = :matr2 )";
    
    //echo json_encode(["debug" => "SQL preparado", "sql" => $sql]); flush();

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':matr1', $matr, PDO::PARAM_INT);
    $stmt->bindParam(':matr2', $matr, PDO::PARAM_INT);
    $stmt->execute();

    //echo json_encode(["debug" => "Después de execute()"]); flush();

    $row = $stmt->fetch(PDO::FETCH_NUM);

    if ($row === false) {
        echo json_encode([
            "status" => false, 
            "mensaje" => "No se encontró matrícula"
        ]);
        exit;
    }

    //echo json_encode(["debug" => "Columnas encontradas", "total_columnas" => count($row)]); flush();

    // Verificar la tercera columna
    if (!isset($row[2])) {
        echo json_encode([
            "status" => false,
            "mensaje" => "Error, columna no encontrada"
        ]);
        exit;
    }

    $columna3 = trim($row[2]);

    //echo json_encode(["debug" => "Valor columna[2]", "valor" => $columna3]); flush();

    // Comparar
    if ($columna3 === $pass) {

        // Asegurar que $row es un array
        if (!is_array($row)) {
            echo json_encode([
                "status" => false,
                "mensaje" => "Error, datos no válidos"
            ]);
            exit;
        }
    
        // Filtrar las posiciones que quieres devolver
        $usuario = [
            "ID_usuario"        => isset($row[0]) ? $row[0] : null,
            "Nombre"            => isset($row[3]) ? $row[3] : null,
            //"Apellidos"         => isset($row[4]) ? $row[4] : null,
            "Matricula"         => isset($row[19]) ? $row[19] : null,
            "permisoBitacora"   => isset($row[26]) ? $row[26] : null,
        ];
    
        echo json_encode([
            "status" => true,
            "mensaje" => "Login correcto",
            "result" => $usuario
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    } else {
        echo json_encode([
            "status" => false,
            "mensaje" => "Contraseña incorrecta"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "mensaje" => "Error SQL",
        "detalle" => $e->getMessage()
    ]);
} catch (Throwable $e) {
    echo json_encode([
        "status" => false,
        "mensaje" => "Error inesperado",
        "detalle" => $e->getMessage()
    ]);
}
exit;






/*
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/db.php'; // tu función conectaDB()

function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string($d)) {
        return mb_convert_encoding($d, 'UTF-8', 'UTF-8');
    }
    return $d;
}

// Validar parámetros
if (!isset($_POST['m']) || !isset($_POST['p'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Faltan parámetros 'm' o 'p'",
        "post" => $_POST
    ]);
    exit;
}

$matr = intval($_POST['m']);
$pass = trim($_POST['p']);

$conn = conectaDB();

try {
// Preparar consulta con dos parámetros
$stmt = $conn->prepare("SELECT * FROM usuarios2 WHERE Matricula = :matr AND Nombre = :nombre");

// Vincular parámetros
$stmt->bindParam(':matr', $matr, PDO::PARAM_INT);
$stmt->bindParam(':nombre', $pass, PDO::PARAM_STR);

// Ejecutar
$stmt->execute();

// Obtener resultado
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Imprimir para prueba
var_dump($row);
exit;

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if (isset($row['Contrase�a'])) unset($row['Contrase�a']); // opcional
        echo json_encode([
            "status" => "success",
            "data" => utf8ize($row)
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No se encontró personal con esa matrícula o nombre"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error en la consulta: " . $e->getMessage()
    ]);
}
exit();
*/



/*
header("Content-Type: application/json");
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 'On');

include __DIR__ . '/../geo/codigo/pdo.php';

if (isset($_POST['m']) && isset($_POST['p'])) {
    $matr = intval($_POST['m']);
    $pass = trim($_POST['p']);

    $conn = conectaDB();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la conexión']);
        exit;
    }

    try {
        $SQL = "SELECT * FROM usuarios2 WHERE Matricula = :matr AND Nombre = :pass";
        $stmt = $conn->prepare($SQL);
        $stmt->bindParam(':matr', $matr, PDO::PARAM_INT);
        $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode([
                "status" => "success",
                "data" => $row
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No se encontró el personal con esa matrícula"
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Error en la consulta: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Parámetros 'm' o 'p' no proporcionados"
    ]);
}*/





/*

    include __DIR__ . '/../geo/codigo/pdo.php';

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la conexión']);
        exit;
    }
    
    // Obtener datos POST
    $matricula = $_POST["matricula"] ?? "";
    $password = $_POST["password"] ?? "";

    // Consulta
    $sql = "SELECT * FROM usuarios2 WHERE Matricula = ? AND Contraseña = ?";
    $params = array($matricula, $password);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt && sqlsrv_has_rows($stmt)) {
        $userData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        // Remover contraseña si quieres más seguridad
        unset($userData["password"]);

        echo json_encode([
            "response" => true,
            "message" => "Login exitoso",
            "data" => $userData
        ]);
    } else {
        echo json_encode([
            "response" => false,
            "message" => "Credenciales incorrectas",
            "data" => null
        ]);
    }

    // Cerramos la conexión si es necesario
    sqlsrv_close($conn);
    */
    

?>
