<?php

/** 
 * LibNotes.ajax.php
 *
 * Accions AJAX per a la llibreria de notes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibNotes.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");
$Festiu = unserialize($_SESSION['FESTIU']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'MarcaComNotaAnterior') {
		$nom = $_REQUEST['nom'];
		$data = explode("_", $nom);
		$SQL = 'UPDATE NOTES SET convocatoria=0 WHERE notes_id='.$data[1];	

		try {
			if (!$conn->query($SQL))
				throw new Exception($conn->error.'. SQL: '.$SQL);
			print $SQL;
		} 
		catch (Exception $e) {
			print "ERROR MarcaComNotaAnterior. Causa: ".$e->getMessage();
		}	
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
	else if ($_REQUEST['accio'] == 'ActualitzaTaulaNotes') {
		$CicleId = $_REQUEST['CicleId'];
		$Nivell = $_REQUEST['Nivell'];
		print ObteTaulaNotesJSON($conn, $CicleId, $Nivell);
	}
	else if ($_REQUEST['accio'] == 'ActualitzaNotaRecuperacio') {
		$nom = $_REQUEST['nom'];
//print $nom;
		$data = explode("_", $nom);
		$valor = $_REQUEST['valor'];
		if (EsNotaValida($valor)) {
			$NotaNumerica = NotaANumero($valor);
			$SQL = 'UPDATE NOTES SET nota'.($data[2]+1).'='.$NotaNumerica.' WHERE notes_id='.$data[1];	
			$conn->query($SQL);
			print $SQL;
		} 
		else
			print "Valor no vàlid: ".$valor;
	}
	else if ($_REQUEST['accio'] == 'Convalida') {
		// Convalida una UF: Posa el camp convalidat de NOTES a cert, posa una nota de 5 i el camp convocatòria a 0.
		$nom = $_REQUEST['nom'];
		$data = explode("_", $nom); // Nom_Id_Convocatòria
		$SQL = 'UPDATE NOTES SET convalidat=1, convocatoria=0, nota'.$data[2].'=5 WHERE notes_id='.$data[1];	
		try {
			if (!$conn->query($SQL))
				throw new Exception($conn->error.'. SQL: '.$SQL);
			print $SQL;
		} 
		catch (Exception $e) {
			print "ERROR Convalida. Causa: ".$e->getMessage();
		}	
	}
	else if ($_REQUEST['accio'] == 'AugmentaConvocatoriaFila') {
		$NotesFila = $_REQUEST['dades'];
print_r($NotesFila);
		$aNotesFila = json_decode($NotesFila, true);
		foreach ($aNotesFila as $key => $value) {
			if ($value != '') {
				$data = explode("_", $key); // Nom_Id_Convocatòria
				switch ($value) {
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					case 10:
					case 'A':
						$SQL = 'UPDATE NOTES SET convocatoria=0 WHERE notes_id='.$data[1];	
						break;
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 'NA';
						$SQL = 'UPDATE NOTES SET convocatoria=convocatoria+1 WHERE notes_id='.$data[1];	
						break;
				}				
				if (!$conn->query($SQL))
					throw new Exception($conn->error.'. SQL: '.$SQL);
				print $SQL;
			}
		}
	}
	else {
		if ($CFG->Debug)
			print "Acció no suportada. Valor de $_POST: ".json_encode($_POST);
		else
			print "Acció no suportada.";
	}
}
else 
    print "ERROR. No hi ha POST o no hi ha acció.";

?>
