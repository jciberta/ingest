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
require_once('lib/LibDB.php');
require_once('lib/LibHTML.php');
require_once('lib/LibCurs.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

// L'escriptori està format per una llista de targetes (Bootstrap cards) depenent dels rols assignats.
//  - admin 
//  - direcció
//  - cap d'estudis: Notes (tots els nivells).
//  - cap departament 
//  - tutor 
//  - professor: Notes (nivells que imparteix).
//  - alumne: Expedient.
//  - pare: Expedient fills.

if ($Usuari->es_alumne) {
	CreaIniciHTML($Usuari, '');
	echo '<div class="card-columns" style="column-count:6">';
	echo '  <div class="card">';
	echo '    <div class="card-body">';
	echo '      <h5 class="card-title">Expedient</h5>';
	echo '      <p class="card-text">Visualitza el teu expedient.</p>';
	echo '      <a href="MatriculaAlumne.php?accio=MostraExpedient&AlumneId='.$Usuari->usuari_id.'" class="btn btn-primary btn-sm">Ves-hi</a>';
	echo '    </div>';
	echo '  </div>';
}

if ($Usuari->es_professor) {
	CreaIniciHTML($Usuari, '');
	echo '<div class="card-columns" style="column-count:6">';
	$SQL = ' SELECT DISTINCT CF.cicle_formatiu_id, UF.nivell, CF.codi AS CodiCF, CF.nom AS NomCF '.
		' FROM PROFESSOR_UF PUF '.
		' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=PUF.uf_id) '.
		' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
		' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
		' WHERE professor_id='.$Usuari->usuari_id .
		' ORDER BY CF.codi, UF.nivell ';

	$ResultSet = $conn->query($SQL);
	if ($ResultSet->num_rows > 0) {
		$row = $ResultSet->fetch_assoc();
		while($row) {
			echo '  <div class="card">';
			echo '    <div class="card-body">';
			echo '      <h5 class="card-title">Notes '.$row['CodiCF'].$row['nivell'].'</h5>';
			echo '      <p class="card-text">'.utf8_encode($row['NomCF']).'.</p>';
			echo '      <a href="Notes.php?CicleId='.$row['cicle_formatiu_id'].'&Nivell='.$row['nivell'].'" class="btn btn-primary btn-sm">Ves-hi</a>';
			echo '    </div>';
			echo '  </div>';
			$row = $ResultSet->fetch_assoc();
		}
	}
	$ResultSet->close();
}

if (($Usuari->es_admin) || ($Usuari->es_cap_estudis)) {
	$curs = new Curs($conn, $Usuari);
	$curs->EscriuFormulariRecera();
}

echo '</div>';

echo "<DIV id=debug></DIV>";

$conn->close();

?>















