<?php

/** 
 * ImportaNotes.php
 *
 * Importa les notes d'una UF.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

//declare(strict_types = 1);

require_once('Config.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibImporta.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor)
	header("Location: Surt.php");

if ((empty($_POST)) || (!isset($_POST['submit'])))
	header("Location: Surt.php");

CreaIniciHTML($Usuari, "Importació de notes");

// https://www.w3schools.com/php/php_file_upload.asp

$target_dir = INGEST_DATA."/upload/";
$target_file = $target_dir . basename($_FILES["Fitxer"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if file already exists
if (file_exists($target_file)) {
	unlink($target_file);
}
// Check file size
if ($_FILES["Fitxer"]["size"] > 500000) {
    echo "La mida del fitxer supera la permesa.";
    $uploadOk = 0;
}
// Allow certain file formats
if ($imageFileType != "xlsx") {
    echo "Només es permet importar de XLSX.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
	exit;
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["Fitxer"]["tmp_name"], $target_file)) {
        echo "El fitxer ". basename( $_FILES["Fitxer"]["name"]). " s'ha penjat correctament.<BR>";
    } 
	else {
		die("<div class='alert alert-danger' role='alert'><b>ERROR</b>: Hi ha hagut un error en penjar el fitxer.<br>Si el problema persisteix, contacteu amb l'administrador.</div>");
    }
}

$inputFileName = INGEST_DATA.'/upload/'.$_FILES["Fitxer"]["name"];
$UnitatPlaEstudiId = $_POST['UnitatPlaEstudiId'];

$ImportaNotes = new ImportaNotesMoodleFitxer($conn, $Usuari, $Sistema);
$ImportaNotes->UnitatPlaEstudiId = $UnitatPlaEstudiId;
$ImportaNotes->Importa($inputFileName);

$conn->close(); 
 
?>
