<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/../geo/codigo/pdo.php';
$conn = conectaDB();

if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_final'])) {
    die("Faltan parámetros de fecha.");
}

$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '---';
$fecha_final = isset($_GET['fecha_final']) ? $_GET['fecha_final'] : '---';

$fechaInicio = $_GET['fecha_inicio'] . " 00:00:00";
$fechaFinal  = $_GET['fecha_final'] . " 23:59:59";

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

$stmt->execute();
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

if (count($datos) > 0) {
    // $columnas = array_keys($datos[0]);
    // $colIndex = 'A';
    // foreach ($columnas as $col) {
    //     $sheet->setCellValue($colIndex . '1', $col);
    //     $colIndex++;
    // }

    $sheet->setCellValue('A1', "Registros de Bitacora de {$fecha_inicio} al {$fecha_final}");
    $sheet->getStyle('A1')->getFont()->setBold(true);

    $columnas = range('A', 'Y');
    foreach ($columnas as $col) {
        $celda = $col . '3';
        $sheet->getStyle($celda)->getFont()->setBold(true);
        $sheet->getStyle($celda)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($celda)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');
    }

    $sheet->setCellValue('A1', "Registros de Bitacora de {$fecha_inicio} al {$fecha_final}");

    $sheet->setCellValue('A3', "ID Registro");
    $sheet->setCellValue('B3', "Matricula");
    $sheet->setCellValue('C3', "Nombre");
    $sheet->setCellValue('D3', "Empresa Externo");
    $sheet->setCellValue('E3', "Evidencia Externo");
    $sheet->setCellValue('F3', "Fecha Entrada");
    $sheet->setCellValue('G3', "Fecha Salida");
    $sheet->setCellValue('H3', "Fecha Entrada/Salida");
    $sheet->setCellValue('I3', "Guardia");
    $sheet->setCellValue('J3', "Fila");
    $sheet->setCellValue('K3', "VIN");
    $sheet->setCellValue('L3', "Modelo de la Unidad");
    $sheet->setCellValue('M3', "LLAVE");
    $sheet->setCellValue('N3', "Distribuidor");
    $sheet->setCellValue('O3', "Núm. de ejes");
    $sheet->setCellValue('P3', "Origen de la Unidad");
    $sheet->setCellValue('Q3', "Modelo Tanque Izquierdo");
    $sheet->setCellValue('R3', "CM de Modelo Tanque Izquierdo");
    $sheet->setCellValue('S3', "Modelo Tanque Derecho");
    $sheet->setCellValue('T3', "CM de Modelo Tanque Derecho");
    $sheet->setCellValue('U3', "Tipo de Tanque");
    $sheet->setCellValue('V3', "Nivel de Urea");
    $sheet->setCellValue('W3', "Equipamiento");
    $sheet->setCellValue('X3', "Estado");
    $sheet->setCellValue('Y3', "Observaciones");

    $fila = 4;
    foreach ($datos as $row) {
        $colIndex = 'A';
        foreach ($row as $value) {
            $sheet->setCellValue($colIndex . $fila, $value);
            $colIndex++;
        }
        $fila++;
    }
} else {
    $sheet->setCellValue('A1', 'No hay datos en el rango de fechas');
}

$fechaInicioNombre = str_replace('-', '', $_GET['fecha_inicio']);
$fechaFinalNombre = str_replace('-', '', $_GET['fecha_final']);
$nombreArchivo = "registros_patio_{$fechaInicioNombre}_al_{$fechaFinalNombre}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$nombreArchivo\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;