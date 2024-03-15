<?php
/** 
 * LibGeneraPDF.php
 *
 * Llibreria d'utilitats per a la generació de PDF (massius).
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

 
 require_once('Config.php');
 require_once(ROOT.'/lib/LibURL.php');
 require_once(ROOT.'/lib/LibUsuari.php');
 require_once(ROOT.'/lib/LibExpedient.php');
 require_once(ROOT.'/lib/LibMatricula.php');
 require_once(ROOT.'/lib/LibHTML.php');

 // Crear objeto Expedient
 $conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
 if ($conn->connect_error)
     die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

     $Expedient = new Expedient($conn); 
function generarYDescargarExpedientesZip($matriculas, $Expedient) {
    
   

    // Directorio temporal para almacenar los expedientes PDF
    $tempDir = sys_get_temp_dir() . '/expedientes_' . uniqid();

    // Crear directorio temporal si no existe
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Generar expedientes PDF y almacenarlos en el directorio temporal
    foreach ($matriculas as $matricula) {
       // $Expedient->GeneraPDFArxiu($matricula);
        // Suponiendo que GeneraPDF genera el PDF y lo guarda en el sistema de archivos
        // Puedes adaptar esto según cómo funciona tu método GeneraPDF
        // Por ejemplo, si GeneraPDF devuelve la ruta del archivo PDF, puedes almacenarla en una variable y mover/copiar el archivo al directorio temporal.
         $pdfPath = $Expedient->GeneraPDFArxiu($matricula);
         copy($pdfPath, $tempDir . '/' . basename($pdfPath));
    }

    // Comprimir expedientes en un archivo ZIP
    $zipFile = sys_get_temp_dir() . '/expedientes.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = glob($tempDir . '/*');
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    } else {
        die('Error al crear el archivo ZIP');
    }

    // Descargar el archivo ZIP
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="expedientes.zip"');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);

    // Eliminar directorio temporal y archivo ZIP después de la descarga
    array_map('unlink', glob("$tempDir/*"));
    rmdir($tempDir);
    unlink($zipFile);
}

// Ejemplo de uso
$matriculas = [2528, 2529, 2530];
generarYDescargarExpedientesZip($matriculas, $Expedient);

?>
