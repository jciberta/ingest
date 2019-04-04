<?php

/** 
 * UsuariRecerca.php
 *
 * Formulari de la recerca de l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

// Obtenció de la modalitat del formulari.
$Modalitat = FormRecerca::mfLLISTA;
if (isset($_GET) && array_key_exists('Modalitat', $_GET) && $_GET['Modalitat']=='mfBusca') 
	$Modalitat = FormRecerca::mfBUSCA;

$CursId = -1;
if (isset($_GET) && array_key_exists('CursId', $_GET)) 
	$CursId = $_GET['CursId'];

$Accio = (isset($_GET) && array_key_exists('accio', $_GET)) ? $_GET['accio'] : '';

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

switch ($Accio) {
    case "Professors":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Professors";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2, codi FROM USUARI WHERE es_professor=1 ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'nom, cognom1, cognom2, username, codi';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari, Codi';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'UsuariFitxa.php';
		$frm->PermetSuprimir = True;
		$frm->AfegeixOpcio('Assigna UFs', 'AssignaUFs.php?accio=AssignaUF&ProfessorId=');
		$frm->EscriuHTML();
        break;
    case "Alumnes":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->AfegeixJavaScript('Matricula.js?v1.2');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Alumnes";
		$Where = ($CursId > 0) ? ' AND C.curs_id='.$CursId : '';
		
		$SQL = ' SELECT '.
			' U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.username, '.
			' M.matricula_id, '.
			' C.nom AS NomCurs, C.nivell, '.
			' CASE WHEN M.baixa=1 THEN "'.utf8_decode('Sí').'" ELSE "" END AS baixa '.
			' FROM USUARI U '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
			' WHERE es_alumne=1 '.$Where.
			' ORDER BY C.nom, C.nivell, U.cognom1, U.cognom2, U.nom ';

		$frm->SQL = $SQL;
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'NomAlumne, Cognom1Alumne, Cognom2Alumne, username, NomCurs, nivell, baixa';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari, Curs, Nivell, Baixa';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'UsuariFitxa.php';
		$frm->PermetSuprimir = True;
		$frm->AfegeixOpcio('Matrícula', 'MatriculaAlumne.php?AlumneId=');
		$frm->AfegeixOpcio('Expedient', 'MatriculaAlumne.php?accio=MostraExpedient&AlumneId=');
		$frm->AfegeixOpcio('Expedient PDF', 'ExpedientPDF.php?AlumneId=');
		$frm->AfegeixOpcioAJAX('Baixa', 'BaixaMatricula', 'matricula_id');
		$frm->EscriuHTML();
        break;
    case "Pares":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Pares";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2 FROM USUARI WHERE es_pare=1 ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'nom, cognom1, cognom2, username';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'UsuariFitxa.php';
		$frm->PermetSuprimir = True;
		$frm->EscriuHTML();
        break;
    case "":
		// Tots
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Usuaris";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2 FROM USUARI ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'nom, cognom1, cognom2, username';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'UsuariFitxa.php';
		$frm->PermetSuprimir = True;
		$frm->EscriuHTML();
        break;
}

$conn->close();

?>
