﻿<?php

/** 
 * ImportaUsuaris.php
 *
 * Importa els usuaris.
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
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

if ((empty($_POST)) || (!isset($_POST['submit'])))
	header("Location: Surt.php");

// https://www.w3schools.com/php/php_file_upload.asp

$target_dir = "upload/";
$target_file = $target_dir . basename($_FILES["FitxerCSV"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["FitxerCSV"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if ($imageFileType != "csv") {
    echo "Només es permet importar de CSV.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["FitxerCSV"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["FitxerCSV"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

$inputFileName = 'upload/'.$_FILES["FitxerCSV"]["name"];

CreaIniciHTML($Usuari, "Importació d'usuaris");

$ImportaUsuaris = new ImportaUsuaris($conn, $Usuari);

$row = 1;
if (($handle = fopen($inputFileName, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		if ($row == 1)
			$ImportaUsuaris->TractaPrimeraLinia($data);
		else 
			$ImportaUsuaris->Importa($data);
        $row++;
    }
    fclose($handle);
}

echo "Importació realitzada amb èxit.";

$conn->close(); 
 
?>