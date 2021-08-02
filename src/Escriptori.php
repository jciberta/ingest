<?php

/** 
 * Escriptori.php
 *
 * Pàgina principal un cop autenticats.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibCurs.php');
require_once(ROOT.'/lib/LibUsuari.php');
require_once(ROOT.'/lib/LibProfessor.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
 

// L'escriptori està format per una llista de targetes (Bootstrap cards) depenent dels rols assignats.
//  - admin 
//  - direcció
//  - cap d'estudis: Notes (tots els nivells).
//  - cap departament 
//  - tutor 
//  - professor: Notes (nivells que imparteix).
//  - alumne: Expedient.
//  - pare: Expedient fills.

if (($Usuari->es_admin) || ($Usuari->es_cap_estudis)) {
	$curs = new Curs($conn, $Usuari);
	$curs->EscriuFormulariRecera();
}
else if ($Usuari->es_professor) {
	CreaIniciHTML($Usuari, '');
	/*$SQL = ' SELECT DISTINCT CF.cicle_formatiu_id, UF.nivell, CF.codi AS CodiCF, CF.nom AS NomCF, C.curs_id, C.estat, C.grups_tutoria '.
		' FROM PROFESSOR_UF PUF '.
		' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=PUF.uf_id) '.
		' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
		' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
		' LEFT JOIN CURS C ON (C.cicle_formatiu_id=CF.cicle_formatiu_id AND C.nivell=UF.nivell) '.
		' WHERE C.estat<>"T" AND professor_id='.$Usuari->usuari_id .
		' ORDER BY CF.codi, UF.nivell ';*/

	$SQL = ' SELECT DISTINCT CPE.cicle_formatiu_id, UPE.nivell, CPE.codi AS CodiCF, CPE.nom AS NomCF, C.curs_id, C.estat, C.grups_tutoria '.
		' FROM PROFESSOR_UF PUF '.
		' LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (PUF.uf_id=UPE.unitat_pla_estudi_id) '.
		' LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) '.
		' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) '.
		' LEFT JOIN CURS C ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id AND UPE.nivell=C.nivell) '.
		' WHERE C.estat<>"T" AND professor_id='.$Usuari->usuari_id .
		' ORDER BY CPE.codi, UPE.nivell ';
		
//print $SQL;
	echo '<h3>Cursos</h3>';
	echo '<div class="card-columns" style="column-count:6">';
	$ResultSet = $conn->query($SQL);
	if ($ResultSet->num_rows > 0) {
		$row = $ResultSet->fetch_assoc();
		while($row) {
			if ($row['estat'] == Curs::Junta) {
				$GrupsTutoria = $row['grups_tutoria'];
				if ($GrupsTutoria == '') {
					// Una sola línia
					$URL = GeneraURL('Fitxa.php?accio=ExpedientSagaAvaluacio&Id='.$row['curs_id']);
					echo CreaTargeta($row['CodiCF'].$row['nivell'], $row['NomCF'], $URL);
				}
				else {
					// Vàries línies
					$aGrupsTutoria = explode(',', $GrupsTutoria);
					foreach($aGrupsTutoria as $Grup) {
						$URL = GeneraURL('Fitxa.php?accio=ExpedientSagaAvaluacio&Id='.$row['curs_id'].','.$Grup);
						echo CreaTargeta($row['CodiCF'].$row['nivell'].' '.$Grup, $row['NomCF'], $URL);
					}
				}
			}
			else {
				$URL = GeneraURL('Notes.php?CursId='.$row['curs_id']);
				echo CreaTargeta($row['CodiCF'].$row['nivell'], $row['NomCF'], $URL);
			}
			$row = $ResultSet->fetch_assoc();
		}
	}
	$ResultSet->close();

	echo '</div>';
	echo '<h3>Gestió</h3>';
	echo '<div class="card-columns" style="column-count:6">';
	
	// Grups tutoria
	$Professor = new Professor($conn, $Usuari);
	$CursId = $Professor->ObteCursTutorId();
	if ($CursId > 0) {
		$URL1 = GeneraURL('Grups.php?CursId='.$CursId);
		$URL2 = GeneraURL('UsuariRecerca.php?accio=UltimLogin');
		echo '  <div class="card">';
		echo '    <div class="card-body">';
		echo '      <h5 class="card-title">Tutoria</h5>';
		echo '<table style="border-collapse: separate;border-spacing: 0px 6px ">';
		echo '<tr>';
		echo '      <td width=100><p class="card-text">Grups</p></td>';
		echo '      <td><p class="card-text">Darrers accessos</p></td>';
		echo '</tr>';
		echo '<tr>';
		echo '      <td><a href="'.$URL1.'" class="btn btn-primary btn-sm">Ves-hi</a></td>';
		echo '      <td><a href="'.$URL2.'" class="btn btn-primary btn-sm">Ves-hi</a></td>';
		echo '</tr>';
		echo '</table>';
		echo '    </div>';
		echo '  </div>';
	}
	
	// Les meves UF
	$URL = GeneraURL('FPRecerca.php?accio=PlaEstudisUnitat&ProfId='.$Usuari->usuari_id);
	echo '  <div class="card">';
	echo '    <div class="card-body">';
	echo '      <h5 class="card-title">Unitats formatives</h5>';
	echo '      <p class="card-text">Les meves UF</p>';
	echo '      <a href="'.$URL.'" class="btn btn-primary btn-sm">Ves-hi</a>';
	echo '    </div>';
	echo '  </div>';

	echo '</div>';
	echo '<h3>Informes</h3>';
	echo '<div class="card-columns" style="column-count:6">';

	// Històric
	$URL = GeneraURL('Recerca.php?accio=HistoricCurs');
	echo '  <div class="card">';
	echo '    <div class="card-body">';
	echo '      <h5 class="card-title">Històric</h5>';
	echo '      <p class="card-text">Notes FP</p>';
	echo '      <a href="'.$URL.'" class="btn btn-primary btn-sm">Ves-hi</a>';
	echo '    </div>';
	echo '  </div>';

	// Promoció 1r
	$URL = GeneraURL('UsuariRecerca.php?accio=AlumnesPromocio1r');
	echo '  <div class="card">';
	echo '    <div class="card-body">';
	echo '      <h5 class="card-title">Promocions 1r</h5>';
	echo "      <p class='card-text'>Alumnes amb 60% d'hores o més</p>";
	echo '      <a href="'.$URL.'" class="btn btn-primary btn-sm">Ves-hi</a>';
	echo '    </div>';
	echo '  </div>';
	
	// Graduació 2n
	$URL = GeneraURL('UsuariRecerca.php?accio=AlumnesGraduacio2n');
	echo '  <div class="card">';
	echo '    <div class="card-body">';
	echo '      <h5 class="card-title">Graduacions 2n</h5>';
	echo "      <p class='card-text'>Alumnes amb 100% d'hores</p>";
	echo '      <a href="'.$URL.'" class="btn btn-primary btn-sm">Ves-hi</a>';
	echo '    </div>';
	echo '  </div>';

	// Estadístiques FP
	$URL = GeneraURL('Estadistiques.php?accio=EstadistiquesNotes');
	echo '  <div class="card">';
	echo '    <div class="card-body">';
	echo '      <h5 class="card-title">Estadístiques</h5>';
	echo '      <p class="card-text">Aprovats per UF</p>';
	echo '      <a href="'.$URL.'" class="btn btn-primary btn-sm">Ves-hi</a>';
	echo '    </div>';
	echo '  </div>';
}
else if ($Usuari->es_alumne) {
	CreaIniciHTML($Usuari, '');
	$Alumne	= new Alumne($conn, $Usuari);
	$MatriculaId = $Alumne->ObteMatriculaActiva($Usuari->usuari_id);
	if ($MatriculaId > 0) {
		$URL = GeneraURL('MatriculaAlumne.php?accio=MostraExpedient&MatriculaId='.$MatriculaId);
		echo '<div class="card-columns" style="column-count:6">';
		echo '  <div class="card">';
		echo '    <div class="card-body">';
		echo '      <h5 class="card-title">Expedient</h5>';
		echo '      <p class="card-text">Visualitza el teu expedient.</p>';
		echo '      <a href="'.$URL.'" class="btn btn-primary btn-sm">Ves-hi</a>';
		echo '    </div>';
		echo '  </div>';
	}
}
else if ($Usuari->es_pare) {
	// Els pares només poden veure el PDF de les notes dels seus fills
	CreaIniciHTML($Usuari, '');
	$SQL = ' SELECT '.
		' 	U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
		'   M.matricula_id AS MatriculaId'.
		' FROM USUARI U '.
		' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
		' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
		' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
		' WHERE (U.pare_id='.$Usuari->usuari_id.' OR U.mare_id='.$Usuari->usuari_id.') '.
		' AND (Edat(U.data_naixement)<18 OR U.permet_tutor=1) AND AA.actual=1 ';
//print $SQL;
	echo '<div class="card-columns" style="column-count:6">';
	$ResultSet = $conn->query($SQL);
	if ($ResultSet->num_rows > 0) {
		$row = $ResultSet->fetch_assoc();
		while($row) {
			$URL = GeneraURL('ExpedientPDF.php?MatriculaId='.$row['MatriculaId']);
			echo '  <div class="card">';
			echo '    <div class="card-body">';
			echo '      <h5 class="card-title">Expedient</h5>';
			$NomComplet = trim(trim($row['NomAlumne']).' '.trim($row['Cognom1Alumne']).' '.trim($row['Cognom2Alumne']));
			echo '      <p class="card-text">'.utf8_encode($NomComplet).'</p>';
			echo '      <a href="'.$URL.'" class="btn btn-primary btn-sm">Ves-hi</a>';
			echo '    </div>';
			echo '  </div>';
			$row = $ResultSet->fetch_assoc();
		}
	}
	else
		echo 'No hi ha dades a mostrar.';
	$ResultSet->close();
}

echo '</div>';

echo "<DIV id=debug></DIV>";

$conn->close();

?>