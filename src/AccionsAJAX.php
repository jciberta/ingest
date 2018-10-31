<?php

/** 
 * AccionsAJAX.php
 *
 * Accions AJAX diverses.
 */
 
require_once('Config.php');

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'MatriculaUF') {
		$nom = $_REQUEST['nom'];
		$check = $_REQUEST['check'];
		$Baixa = ($check == 'true') ? 0 : 1; // Si estava actiu, ara el donem de baixa
		$NotaId = str_replace('chbNotaId_', '', $nom);	
		$SQL = 'UPDATE NOTES SET baixa='.$Baixa.' WHERE notes_id='.$NotaId;	
		$conn->query($SQL);
		print $SQL;
	}
	else
        print "Acció no suportada.";
}
else 
    print "ERROR. No hi ha POST o no hi ha acció.";

?>