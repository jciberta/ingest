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

function generariDescarregarExpedientsZip($matriculas, $Expedient) {
    
   

    // Directori temporal per emmagatzemar els expedients en PDF
    $tempDir = sys_get_temp_dir() . '/expedients' . uniqid();

    // Crear directori temporal si no existeix
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Genera expedients PDF i els emmagatzema en un directori temporal
    foreach ($matriculas as $matricula) {
      
         $pdfPath = $Expedient->GeneraPDFArxiu($matricula);
         copy($pdfPath, $tempDir . DIRECTORY_SEPARATOR . basename($pdfPath));
    }

    // Comprimeix expedients en un arxiu ZIP
    $zipFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR.'expedients.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = glob($tempDir . '/*');
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    } else {
        die('Error en crear l\'arxiu ZIP');
    }

    // Descarrega de l'arxiu zip
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="expedients.zip"');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);

    // Eliminar directori temporal
    array_map('unlink', glob("$tempDir/*"));
    rmdir($tempDir);
    unlink($zipFile);
}

// Exemple d'us
$matriculas = [2528, 2529, 2530];
generariDescarregarExpedientsZip($matriculas, $Expedient);

?>
