<?php

/** 
 * LibMatricula.ajax.php
 *
 * Accions AJAX per a la llibreria d'usuaris.
 *
 * Accés:
 *   - Administrador, direcció, cap d'estudis
 * Accions:
 *   - MatriculaUF
 *   - ConvalidaUF
 *   - EliminaMatriculaCurs
 *   - EliminaMatriculaAlumne
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibCripto.php');
require_once(ROOT.'/lib/LibUsuari.php');
require_once(ROOT.'/lib/LibMatricula.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor)
	header("Location: ../Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'MatriculaUF') {
		$nom = $_REQUEST['nom'];
		$check = $_REQUEST['check'];
		$Baixa = ($check == 'true') ? 0 : 1; // Si estava actiu, ara el donem de baixa
		$NotaId = str_replace('chbNotaId_', '', $nom);	
		$SQL = 'UPDATE NOTES SET baixa='.$Baixa.' WHERE notes_id='.$NotaId;	
		$conn->query($SQL);
//		print $SQL;
	}
	else if ($_REQUEST['accio'] == 'ConvalidaUF') {
		$nom = $_REQUEST['nom'];
		$AlumneId = $_REQUEST['alumne'];
		//$check = $_REQUEST['check'];
		//$Baixa = ($check == 'true') ? 0 : 1; // Si estava actiu, ara el donem de baixa
		$NotaId = str_replace('chbConvalidaUFNotaId_', '', $nom);	

		$Matricula = new Matricula($conn, $Usuari, $Sistema);
		$Matricula->ConvalidaUF($NotaId);
		
		//header("Location: MatriculaAlumne.php?AlumneId=".$AlumneId); -> No funciona!

//		print 'Id nota convalidada: '.$NotaId;
	}
	else if ($_REQUEST['accio'] == 'EliminaMatriculaCurs') {
		if (!$Usuari->es_admin)
			header("Location: ../Surt.php");
		print "EliminaMatriculaCurs<hr>";
		$CursId = $_REQUEST['id'];
		
		// S'ha d'executar de forma atòmica
		$conn->query('START TRANSACTION');
		try {
			// https://stackoverflow.com/questions/4429319/you-cant-specify-target-table-for-update-in-from-clause
			$SQL1 = 'DELETE FROM NOTES WHERE matricula_id IN ( '.
				' 	SELECT Temp.matricula_id FROM ( '.
				' 		SELECT DISTINCT N.matricula_id '.
				' 		FROM NOTES N '.
				' 		LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
				' 		WHERE curs_id='.$CursId.	
				' 	) AS Temp '.
				' ) ';
			if (!$conn->query($SQL1))
				throw new Exception($conn->error.'. SQL: '.$SQL1);
			$SQL2 = 'DELETE FROM MATRICULA WHERE curs_id='.$CursId;	
			if (!$conn->query($SQL2))
				throw new Exception($conn->error.'. SQL: '.$SQL2);
			$conn->query('COMMIT');
		} catch (Exception $e) {
			$conn->query('ROLLBACK');
			die("ERROR EliminaMatriculaCurs. Causa: ".$e->getMessage());
		}
		print '<P>'.$SQL1.'<P>'.$SQL2;
	}
	else if ($_REQUEST['accio'] == 'EliminaMatriculaAlumne') {
		if (!$Usuari->es_admin)
			header("Location: ../Surt.php");
		print "EliminaMatriculaAlumne<hr>";
		$MatriculaId = $_REQUEST['id'];
		
		// S'ha d'executar de forma atòmica
		$conn->query('START TRANSACTION');
		try {
			$SQL1 = 'DELETE FROM NOTES WHERE matricula_id='.$MatriculaId;
			if (!$conn->query($SQL1))
				throw new Exception($conn->error.'. SQL: '.$SQL1);
			$SQL2 = 'DELETE FROM MATRICULA WHERE matricula_id='.$MatriculaId;	
			if (!$conn->query($SQL2))
				throw new Exception($conn->error.'. SQL: '.$SQL2);
			$conn->query('COMMIT');
		} catch (Exception $e) {
			$conn->query('ROLLBACK');
			die("ERROR EliminaMatriculaAlumne. Causa: ".$e->getMessage());
		}
		print '<P>'.$SQL1.'<P>'.$SQL2;
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