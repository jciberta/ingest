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
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibDB.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);
//print_r($_GET);
//exit;

// Obtenció de la modalitat del formulari.
$Modalitat = FormRecerca::mfLLISTA;
if (isset($_GET) && array_key_exists('Modalitat', $_GET) && $_GET['Modalitat']=='mfBusca') 
	$Modalitat = FormRecerca::mfBUSCA;

$CursId = -1;
if (isset($_GET) && array_key_exists('CursId', $_GET)) 
	$CursId = $_GET['CursId'];

$Accio = (isset($_GET) && array_key_exists('accio', $_GET)) ? $_GET['accio'] : '';

switch ($Accio) {
    case "Professors":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->AfegeixJavaScript('Matricula.js?v1.4');
		$frm->AfegeixJavaScript('CanviPassword.js?v1.0');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Professors";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2, codi, usuari_bloquejat '.
			' FROM USUARI WHERE es_professor=1 ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'nom, cognom1, cognom2, username, codi';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari, Codi';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'UsuariFitxa.php';
		$frm->PermetSuprimir = True;
		$frm->AfegeixOpcio('Assigna UFs', 'AssignaUFs.php?accio=AssignaUF&ProfessorId=');
		$frm->AfegeixOpcioAJAX('Bloquejat', 'BloquejaUsuari', 'usuari_id', [FormRecerca::ofrCHECK], 'usuari_bloquejat');
		if ($Usuari->es_admin)
			$frm->AfegeixOpcioAJAX('Password', 'CanviPassword', 'usuari_id');

		$frm->Filtre->AfegeixLlista('usuari_bloquejat', 'Bloquejat', 30, array('', '0', '1'), array('Tots', 'No bloquejat', 'Bloquejat'));

		$frm->EscriuHTML();
        break;
    case "Tutors":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Tutors";
		$SQL = ' SELECT C.curs_id, C.codi AS CodiCurs, C.nom AS NomCurs, C.nivell, '.
			' U.usuari_id, U.nom AS NomProfessor, U.cognom1 AS Cognom1Professor, U.cognom2 AS Cognom2Professor, U.username, '.
			' TUT.tutor_id, TUT.grup_tutoria '.
			' FROM CURS C '.
			' LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id) '.
			' RIGHT JOIN TUTOR TUT ON (C.curs_id=TUT.curs_id) '.
			' LEFT JOIN USUARI U ON (TUT.professor_id=U.usuari_id) '.
			' WHERE AA.actual=1 ';
//print '<BR><BR><BR>'.$SQL;
		$frm->SQL = $SQL;
		$frm->Taula = 'TUTOR';
		$frm->ClauPrimaria = 'tutor_id';
		$frm->Camps = 'CodiCurs, NomCurs, nivell, NomProfessor, Cognom1Professor, Cognom2Professor, grup_tutoria';
		$frm->Descripcions = 'Codi, Nom, Nivell, Nom, 1r cognom, 2n cognom, Grup tutoria';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=Tutor';
		$frm->PermetSuprimir = True;
		$frm->PermetAfegir = True;
		$frm->EscriuHTML();
        break;
    case "Alumnes":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->AfegeixJavaScript('Matricula.js?v1.4');
		$frm->AfegeixJavaScript('CanviPassword.js?v1.0');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Alumnes";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2, codi, FormataData(data_naixement) AS data_naixement, Edat(data_naixement) AS edat, usuari_bloquejat '.
			' FROM USUARI WHERE es_alumne=1 ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'nom, cognom1, cognom2, username, data_naixement, edat, codi';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari, Data naixement, Edat, IDALU';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'UsuariFitxa.php';
		$frm->PermetSuprimir = True;
		$frm->PermetAfegir = True;
		$frm->AfegeixOpcioAJAX('Bloquejat', 'BloquejaUsuari', 'usuari_id', [FormRecerca::ofrCHECK], 'usuari_bloquejat');
		if ($Usuari->es_admin)
			$frm->AfegeixOpcioAJAX('Password', 'CanviPassword', 'usuari_id');
		$frm->Filtre->AfegeixLlista('usuari_bloquejat', 'Bloquejat', 30, array('', '0', '1'), array('Tots', 'No bloquejat', 'Bloquejat'));
		$frm->EscriuHTML();
        break;
    case "Matricules":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->AfegeixJavaScript('Matricula.js?v1.4');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Matrícules";
		$Where = ($CursId > 0) ? ' AND C.curs_id='.$CursId : '';
		
		$SQL = ' SELECT '.
			' U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.username, '.
			' Edat(U.data_naixement) AS edat, U.usuari_bloquejat, '.
			' M.matricula_id, M.grup, '.
			' C.curs_id AS CursId, C.nom AS NomCurs, C.nivell, M.baixa '.
			' FROM USUARI U '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
			' WHERE es_alumne=1 '.$Where.' AND M.matricula_id IS NOT NULL '.
			' ORDER BY C.nom, C.nivell, U.cognom1, U.cognom2, U.nom ';

		$frm->SQL = $SQL;
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'NomAlumne, Cognom1Alumne, Cognom2Alumne, username, edat, NomCurs, nivell, grup';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari, Edat, Curs, Nivell, Grup';
		//$frm->PermetEditar = True;
		//$frm->URLEdicio = 'UsuariFitxa.php';
		//$frm->PermetSuprimir = True;
		$frm->AfegeixOpcioAJAX('Baixa', 'BaixaMatricula', 'matricula_id', [FormRecerca::ofrNOMES_CHECK], 'baixa');
//		$frm->AfegeixOpcioAJAX('Suprimeix', 'SuprimeixMatricula', 'matricula_id', [FormRecerca::ofrNOMES_CHECK]);
		$frm->AfegeixOpcio('Matrícula', 'MatriculaAlumne.php?MatriculaId=', 'matricula_id');
		$frm->AfegeixOpcio('Expedient', 'MatriculaAlumne.php?accio=MostraExpedient&MatriculaId=', 'matricula_id');
		$frm->AfegeixOpcio('Expedient PDF', 'ExpedientPDF.php?MatriculaId=', 'matricula_id');
		$frm->AfegeixOpcioAJAX('Bloquejat', 'BloquejaUsuari', 'usuari_id', [FormRecerca::ofrCHECK], 'usuari_bloquejat');
		if ($Usuari->es_admin)
			$frm->AfegeixOpcioAJAX('[Elimina]', 'EliminaMatriculaAlumne', 'matricula_id');

		// Filtre
		if ($CursId < 0) {
			$aCurs = ObteCodiValorDesDeSQL($conn, "SELECT curs_id, nom FROM CURS_ACTUAL", "curs_id", "nom");
			array_unshift($aCurs[0], '');
			array_unshift($aCurs[1], '');
			$frm->Filtre->AfegeixLlista('CursId', 'Curs', 100, $aCurs[0], $aCurs[1]);
		}
		$frm->Filtre->AfegeixLlista('grup', 'Grup', 30, array('', 'A', 'B', 'C'), array('', 'A', 'B', 'C'));

		$frm->EscriuHTML();
        break;
    case "AlumnesPares":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Alumnes";
		$frm->SQL = ' SELECT '.
			' 	U.usuari_id, U.username AS NIFAlumne, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
			' 	UP.username AS NIFPare, UP.nom AS NomPare, UP.cognom1 AS Cognom1Pare, UP.cognom2 AS Cognom2Pare, '.
			' 	UM.username AS NIFMare, UM.nom AS NomMare, UM.cognom1 AS Cognom1Mare, UM.cognom2 AS Cognom2Mare '.
			' FROM USUARI U '.
			' LEFT JOIN USUARI UP ON (UP.usuari_id=U.pare_id) '.
			' LEFT JOIN USUARI UM ON (UM.usuari_id=U.mare_id) '.
			' WHERE U.es_alumne=1 ORDER BY U.cognom1, U.cognom2, U.nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'NIFAlumne, NomAlumne, Cognom1Alumne, Cognom2Alumne, NIFPare, NomPare, Cognom1Pare, Cognom2Pare, NIFMare, NomMare, Cognom1Mare, Cognom2Mare';
		$frm->Descripcions = 'Usuari, Nom, 1r cognom, 2n cognom, NIF resp1, Nom resp1, 1r cognom resp1, 2n cognom resp1, NIF resp2, Nom resp2, 1r cognom resp2, 2n cognom resp2';
		//$frm->PermetEditar = True;
		//$frm->URLEdicio = 'UsuariFitxa.php';
		//$frm->PermetSuprimir = True;
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
		$frm->AfegeixJavaScript('Matricula.js?v1.4');
		$frm->AfegeixJavaScript('CanviPassword.js?v1.0');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Usuaris";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2, es_alumne, es_professor, es_pare, usuari_bloquejat FROM USUARI ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		//$frm->Camps = 'nom, cognom1, cognom2, username, bool:es_alumne, bool:es_professor, bool:es_pare';
		$frm->Camps = 'nom, cognom1, cognom2, username';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'UsuariFitxa.php';
		$frm->PermetAfegir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->PermetSuprimir = True;
		$frm->AfegeixOpcioAJAX('Alumne', '', 'usuari_id', [FormRecerca::ofrCHECK, FormRecerca::ofrNOMES_LECTURA], 'es_alumne');
		$frm->AfegeixOpcioAJAX('Professor', '', 'usuari_id', [FormRecerca::ofrCHECK, FormRecerca::ofrNOMES_LECTURA], 'es_professor');
		$frm->AfegeixOpcioAJAX('Pare', '', 'usuari_id', [FormRecerca::ofrCHECK, FormRecerca::ofrNOMES_LECTURA], 'es_pare');
		$frm->AfegeixOpcioAJAX('Password', 'CanviPassword', 'usuari_id');
		$frm->AfegeixOpcioAJAX('Bloquejat', 'BloquejaUsuari', 'usuari_id', [FormRecerca::ofrCHECK], 'usuari_bloquejat');
		$frm->EscriuHTML();
        break;
    case "UltimLogin":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->AfegeixJavaScript('Matricula.js?v1.4');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Últims logins";
		$Where = ($CursId > 0) ? ' AND C.curs_id='.$CursId : '';
		
		$SQL = ' SELECT '.
			' 	U.usuari_id AS UsuariId, FormataCognom1Cognom2Nom(U.nom, U.cognom1, U.cognom2) AS NomAlumne, U.username, '.
			' 	Edat(U.data_naixement) AS edat, FormataData(U.data_ultim_login) AS UltimLoginAlumne, '.
			' 	UP.username AS NIFPare, FormataCognom1Cognom2Nom(UP.nom, UP.cognom1, UP.cognom2) AS NomResp1, FormataData(UP.data_ultim_login) AS UltimLoginPare,'.
			' 	UM.username AS NIFMare, FormataCognom1Cognom2Nom(UM.nom, UM.cognom1, UM.cognom2) AS NomResp2, FormataData(UM.data_ultim_login) AS UltimLoginMare,'.
			' 	M.matricula_id, M.grup, '.
			' 	C.codi, C.curs_id AS CursId, C.nom AS NomCurs, C.nivell, M.baixa '.
			' FROM USUARI U '.
			' LEFT JOIN USUARI UP ON (UP.usuari_id=U.pare_id) '.
			' LEFT JOIN USUARI UM ON (UM.usuari_id=U.mare_id) '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
			' WHERE U.es_alumne=1 '.$Where.
			' AND AA.actual=1 '.
			' ORDER BY C.nom, C.nivell, U.cognom1, U.cognom2, U.nom ';
//print "<br><br><br><br><br>SQL: $SQL";
		$frm->SQL = $SQL;
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'UsuariId';
		$frm->Camps = 'NomAlumne, UltimLoginAlumne, edat, codi, nivell, grup, NomResp1, UltimLoginPare, NomResp2, UltimLoginMare';
		$frm->Descripcions = 'Alumne, Últim login, Edat, Curs, Nivell, Grup, Nom resp1, Últim login, Nom resp2, Últim login';

		// Filtre
		if ($CursId < 0) {
			$aCurs = ObteCodiValorDesDeSQL($conn, "SELECT curs_id, nom FROM CURS_ACTUAL", "curs_id", "nom");
			array_unshift($aCurs[0], '');
			array_unshift($aCurs[1], '');
			$frm->Filtre->AfegeixLlista('CursId', 'Curs', 100, $aCurs[0], $aCurs[1]);
		}
		$frm->Filtre->AfegeixLlista('grup', 'Grup', 30, array('', 'A', 'B', 'C'), array('', 'A', 'B', 'C'));

		$frm->EscriuHTML();
        break;
}

$conn->close();

?>