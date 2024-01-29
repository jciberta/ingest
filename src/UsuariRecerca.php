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
require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibUsuari.php');
require_once(ROOT.'/lib/LibCurs.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR', 'AD']);

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
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->AfegeixJavaScript('Matricula.js?v1.4');
		$frm->AfegeixJavaScript('CanviPassword.js?v1.0');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Professors";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2, email, email_ins, codi, titol_angles, usuari_bloquejat '.
			' FROM USUARI WHERE es_professor=1 ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'nom, cognom1, cognom2, username, email, email_ins, codi, titol_angles';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari, Correu, Correu INS, Codi, Anglès';
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
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Tutors";
		$SQL = ' SELECT C.curs_id, C.codi AS CodiCurs, C.nom AS NomCurs, C.nivell, CPE.any_academic_id, '.
			' U.usuari_id, U.nom AS NomProfessor, U.cognom1 AS Cognom1Professor, U.cognom2 AS Cognom2Professor, U.username, U.email, U.email_ins, '.
			' TUT.tutor_id, TUT.grup_tutoria '.
			' FROM CURS C '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' RIGHT JOIN TUTOR TUT ON (C.curs_id=TUT.curs_id) '.
			' LEFT JOIN USUARI U ON (TUT.professor_id=U.usuari_id) ';
//print '<BR><BR><BR>'.$SQL;
		$frm->SQL = $SQL;
		$frm->Taula = 'TUTOR';
		$frm->ClauPrimaria = 'tutor_id';
		$frm->Camps = 'CodiCurs, NomCurs, nivell, NomProfessor, Cognom1Professor, Cognom2Professor, email, email_ins, grup_tutoria';
		$frm->Descripcions = 'Codi, Nom, Nivell, Nom, 1r cognom, 2n cognom, Correu, Correu INS, Grup tutoria';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=Tutor';
		$frm->PermetSuprimir = True;
		$frm->PermetAfegir = True;
		$aAnys = ObteCodiValorDesDeSQL($conn, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('CPE.any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1], [], $Sistema->any_academic_id);
		$frm->EscriuHTML();
        break;
    case "Alumnes":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->AfegeixJavaScript('Matricula.js?v1.4');
		$frm->AfegeixJavaScript('CanviPassword.js?v1.0');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Alumnes";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2, codi, FormataData(data_naixement) AS data_naixement, Edat(data_naixement) AS edat, telefon, email, email_ins, usuari_bloquejat '.
			' FROM USUARI WHERE es_alumne=1 ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'nom, cognom1, cognom2, username, data_naixement, edat, telefon, email, email_ins, codi';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari, Data naixement, Edat, Telèfon, Correu, Correu INS, IDALU';

		$frm->MaximRegistres = 10;

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
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'AD']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->AfegeixJavaScript('Forms.js?v1.2');
		$frm->AfegeixJavaScript('Matricula.js?v1.6');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Matrícules";
		$Where = ($CursId > 0) ? ' AND C.curs_id='.$CursId : '';
		$SQL = ' SELECT '.
			' U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.username, '.
			' FormataCognom1Cognom2Nom(U.nom, U.cognom1, U.cognom2) AS Alumne, '.
			' U.data_naixement, Edat(U.data_naixement) AS edat, U.telefon, U.municipi, U.usuari_bloquejat, '.
			' M.matricula_id, M.grup, '.
			' C.curs_id AS CursId, C.nom AS NomCurs, C.codi AS CodiCurs, C.nivell, M.baixa, '.
			' CF.cicle_formatiu_id AS CicleFormatiuId, '.
			' AA.any_academic_id AS AnyAcademicId '.
			' FROM USUARI U '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' WHERE es_alumne=1 '.$Where.' AND M.matricula_id IS NOT NULL '.
			' ORDER BY C.nom, C.nivell, U.cognom1, U.cognom2, U.nom ';
//print '<br><br><br>'.$SQL;
		$frm->SQL = $SQL;
//		$frm->Taula = 'USUARI';
//		$frm->ClauPrimaria = 'usuari_id';
		$frm->Taula = 'MATRICULA';
		$frm->ClauPrimaria = 'matricula_id';
		$frm->Camps = 'Alumne, username, data_naixement, edat, telefon, municipi, CodiCurs, grup';
		$frm->Descripcions = 'Nom, Usuari, Data naixement, Edat, Telèfon, Municipi, Curs, Grup';
//		if ($Usuari->es_admin) {
//			$frm->Camps = 'matricula_id, '.$frm->Camps;
//			$frm->Descripcions = 'Id, '.$frm->Descripcions;
//		}
		//$frm->PermetEditar = True;
		//$frm->URLEdicio = 'UsuariFitxa.php';
		//$frm->PermetSuprimir = True;
		$frm->AfegeixOpcioAJAX('Baixa', 'BaixaMatricula', 'matricula_id', [FormRecerca::ofrNOMES_CHECK], 'baixa');
		$frm->AfegeixOpcio('Matrícula', 'MatriculaAlumne.php?MatriculaId=', 'matricula_id');
		$frm->AfegeixOpcio('Expedient', 'MatriculaAlumne.php?accio=MostraExpedient&MatriculaId=', 'matricula_id');
		$frm->AfegeixOpcio('Butlletí PDF', 'ExpedientPDF.php?MatriculaId=', 'matricula_id', 'pdf.png');
		if ($Usuari->es_admin) {
			$frm->AfegeixOpcioAJAX('Bloquejat', 'BloquejaUsuari', 'usuari_id', [FormRecerca::ofrCHECK], 'usuari_bloquejat');
			$frm->AfegeixOpcioAJAX('[Elimina]', 'EliminaMatriculaAlumne', 'matricula_id');
		}
		
		// Filtre
		if ($CursId < 0) {
			$aAnys = ObteCodiValorDesDeSQL($conn, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
//print_h($aAnys);
			$AnyAcademicId = $aAnys[0][0]; 
			$frm->Filtre->AfegeixLlista('AnyAcademicId', 'Any', 30, $aAnys[0], $aAnys[1], [], $Sistema->any_academic_id);

//			$aCurs = ObteCodiValorDesDeSQL($conn, "SELECT curs_id, nom FROM CURS_ACTUAL", "curs_id", "nom");
//			array_unshift($aCurs[0], '');
//			array_unshift($aCurs[1], '');
//			$frm->Filtre->AfegeixLlista('CursId', 'Curs', 100, $aCurs[0], $aCurs[1]);
			
			$aCurs = ObteCodiValorDesDeSQL($conn, "SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU", "cicle_formatiu_id", "nom");
			array_unshift($aCurs[0], '');
			array_unshift($aCurs[1], '');
			$frm->Filtre->AfegeixLlista('CicleFormatiuId', 'Cicle', 100, $aCurs[0], $aCurs[1]);
			$frm->Filtre->AfegeixLlista('nivell', 'Nivell', 30, array('', '1', '2'), array('', '1', '2'));
		}
		$frm->Filtre->AfegeixLlista('grup', 'Grup', 30, array('', 'A', 'B', 'C', 'D', 'E'), array('', 'A', 'B', 'C', 'D', 'E'));

		$frm->EscriuHTML();
        break;
    case "AlumnesPares":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
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
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Pares";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2 FROM USUARI WHERE es_pare=1 ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'nom, cognom1, cognom2, username';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari';
		if ($Usuari->es_admin) {
			$frm->Camps = 'usuari_id, '.$frm->Camps;
			$frm->Descripcions = 'Id, '.$frm->Descripcions;
		}
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'UsuariFitxa.php';
		$frm->PermetSuprimir = True;
		$frm->EscriuHTML();
        break;
    case "":
		// Tots
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->AfegeixJavaScript('Matricula.js?v1.4');
		$frm->AfegeixJavaScript('CanviPassword.js?v1.0');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Usuaris";
//		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2, es_alumne, es_professor, es_pare, usuari_bloquejat FROM USUARI ORDER BY cognom1, cognom2, nom';
		$frm->SQL = 'SELECT *, Edat(data_naixement) AS Edat FROM USUARI ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		//$frm->Camps = 'nom, cognom1, cognom2, username, bool:es_alumne, bool:es_professor, bool:es_pare';
		$frm->Camps = 'nom, cognom1, cognom2, username';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari';
		if ($Usuari->es_admin) {
			$frm->Camps = 'nom, cognom1, cognom2, username, data_naixement, Edat, telefon, email, poblacio ';
			$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari, Data naixement, Edat, Telèfon, Correu, Població ';
		}
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
		// Visible per: admin, direcció, cap d'estudis i tutors (només el seu curs)
		$NomesProfessor = ($Usuari->es_professor && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis);			
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->AfegeixJavaScript('Matricula.js?v1.4');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = "Últims logins";
		$Where = ($CursId > 0) ? ' AND C.curs_id='.$CursId : '';
		if ($NomesProfessor)			
			$Where .= ' AND C.curs_id IN ( '.
			' SELECT DISTINCT C.curs_id FROM TUTOR T '.
			' LEFT JOIN CURS C ON (C.curs_id=T.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' WHERE T.professor_id='.$Usuari->usuari_id.
			' AND CPE.any_academic_id='.$Sistema->any_academic_id.
			' ) ';
/*			$Where .= ' AND C.curs_id IN ( '.
			' SELECT DISTINCT C.curs_id FROM PROFESSOR_UF PUF '.
			' LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (PUF.uf_id=UPE.unitat_pla_estudi_id) '.
			' LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) '.
			' LEFT JOIN CURS C ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id AND UPE.nivell=C.nivell) '.
			' WHERE professor_id='.$Usuari->usuari_id.		
			' ) '; */
		$SQL = ' SELECT '.
			' 	U.usuari_id AS UsuariId, FormataCognom1Cognom2Nom(U.nom, U.cognom1, U.cognom2) AS NomAlumne, U.username, '.
			' 	Edat(U.data_naixement) AS edat, U.data_ultim_login AS UltimLoginAlumne, '.
			' 	UP.username AS NIFPare, FormataCognom1Cognom2Nom(UP.nom, UP.cognom1, UP.cognom2) AS NomResp1, UP.data_ultim_login AS UltimLoginPare,'.
			' 	UM.username AS NIFMare, FormataCognom1Cognom2Nom(UM.nom, UM.cognom1, UM.cognom2) AS NomResp2, UM.data_ultim_login AS UltimLoginMare,'.
			' 	M.matricula_id, M.grup_tutoria, '.
			' 	C.codi, C.curs_id AS CursId, C.nom AS NomCurs, C.nivell, M.baixa '.
			' FROM USUARI U '.
			' LEFT JOIN USUARI UP ON (UP.usuari_id=U.pare_id) '.
			' LEFT JOIN USUARI UM ON (UM.usuari_id=U.mare_id) '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' WHERE U.es_alumne=1 '.$Where.
//			' AND AA.actual=1 '.
			' AND AA.any_academic_id='.$Sistema->any_academic_id.
			' ORDER BY C.nom, C.nivell, U.cognom1, U.cognom2, U.nom ';
//print "<br><br><br><br><br>SQL: $SQL";
		$frm->SQL = $SQL;
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'UsuariId';
		$frm->Camps = 'NomAlumne, UltimLoginAlumne, edat, codi, nivell, grup_tutoria, NomResp1, UltimLoginPare, NomResp2, UltimLoginMare';
		$frm->Descripcions = 'Alumne, Últim login, Edat, Curs, Nivell, Grup, Nom resp1, Últim login, Nom resp2, Últim login';

		// Filtre
		if (!$NomesProfessor) {
			if ($CursId < 0) {
				$aCurs = ObteCodiValorDesDeSQL($conn, "SELECT curs_id, nom FROM CURS_ACTUAL", "curs_id", "nom");
				array_unshift($aCurs[0], '');
				array_unshift($aCurs[1], '');
				$frm->Filtre->AfegeixLlista('CursId', 'Curs', 100, $aCurs[0], $aCurs[1]);
			}
		}
		$GrupTutoria = new GrupTutoria($conn, $Usuari, $Sistema);
		$aGrupTutoria = $GrupTutoria->ObteGrupsAnyActual(); 
		array_unshift($aGrupTutoria, '');
		$frm->Filtre->AfegeixLlista('grup_tutoria', 'Grup', 30, $aGrupTutoria, $aGrupTutoria);

		$frm->EscriuHTML();
        break;
    case "AlumnesPromocio1r":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR']);
		$frm = new AlumnesPromocio1r($conn, $Usuari, $Sistema);
		$frm->EscriuHTML();
        break;
    case "AlumnesGraduacio2n":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR']);
		$frm = new AlumnesGraduacio2n($conn, $Usuari, $Sistema);
		$frm->EscriuHTML();
        break;
	case "Orla":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR']);
		$frm = new Orla($conn, $Usuari, $Sistema);
		$frm->EscriuHTML();
        break;
}

$conn->close();

?>