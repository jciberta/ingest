<?php

/** 
 * AccionsAJAX.php
 *
 * Accions AJAX diverses.
 */
 
require_once('Config.php');
require_once('lib/LibNotes.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");

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
	else if ($_REQUEST['accio'] == 'ActualitzaNota') {
		$nom = $_REQUEST['nom'];
		$data = explode("_", $nom);
		$valor = $_REQUEST['valor'];
		if (EsNotaValida($valor)) {
			$NotaNumerica = NotaANumero($valor);
			$SQL = 'UPDATE NOTES SET nota'.$data[2].'='.$NotaNumerica.' WHERE notes_id='.$data[1];	
			$conn->query($SQL);
			print $SQL;
		} 
		else
			print "Valor no vàlid: ".$valor;
	}
	else if ($_REQUEST['accio'] == 'AssignaUF') {
		$nom = $_REQUEST['nom'];
		$check = $_REQUEST['check'];
		$data = explode("_", $nom);
		if ($check) {
			// Assignem UF
			$SQL = 'INSERT INTO PROFESSOR_UF (professor_id, uf_id) VALUES ('.$data[2].', '.$data[1].')';	
			$conn->query($SQL);
			print $SQL;
		}
		else {
			// Desassignem UF
			$SQL = 'DELETE FROM PROFESSOR_UF WHERE professor_id='.$data[2].' AND uf_id='.$data[1];	
			$conn->query($SQL);
			print $SQL;
		}
	}
	else if ($_REQUEST['accio'] == 'ActualitzaTaulaNotes') {
		$CicleId = $_REQUEST['CicleId'];
		$Nivell = $_REQUEST['Nivell'];
		print ObteTaulaNotesJSON($conn, $CicleId, $Nivell);
	}
	else
        print "Acció no suportada.";
}
else 
    print "ERROR. No hi ha POST o no hi ha acció.";

?>
