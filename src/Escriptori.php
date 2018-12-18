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

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

CreaIniciHTML($Usuari, '');

// L'escriptori està format per una llista de targetes (Bootstrap cards) depenent dels rols assignats.
//  - admin 
//  - direcció
//  - cap d'estudis: Notes (tots els nivells).
//  - cap departament 
//  - tutor 
//  - professor: Notes (nivells que imparteix).
//  - alumne: Expedient.
//  - pare: Expedient fills.

echo '<div class="card-columns" style="column-count:6">';

if ($Usuari->es_alumne) {
	echo '  <div class="card">';
	echo '    <div class="card-body">';
	echo '      <h5 class="card-title">Expedient</h5>';
	echo '      <p class="card-text">Visualitza el teu expedient.</p>';
	echo '      <a href="MatriculaAlumne.php?accio=MostraExpedient&AlumneId='.$Usuari->usuari_id.'" class="btn btn-primary btn-sm">Ves-hi</a>';
	echo '    </div>';
	echo '  </div>';
}

if ($Usuari->es_professor) {
	$SQL = ' SELECT DISTINCT M.cicle_formatiu_id, M.nivell, M.grup_tutoria, CF.codi AS CodiCF, CF.nom AS NomCF '.
		' FROM MATRICULA M '.
		' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
		' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=M.cicle_formatiu_id) '.
		' WHERE C.actual=1 '.
		' ORDER BY CF.codi, M.nivell, M.grup_tutoria ';
	$ResultSet = $conn->query($SQL);
	if ($ResultSet->num_rows > 0) {
		$row = $ResultSet->fetch_assoc();
		while($row) {
			echo '  <div class="card">';
			echo '    <div class="card-body">';
			echo '      <h5 class="card-title">Alumnes '.$row['CodiCF'].$row['nivell'].' '.$row['grup_tutoria'].'</h5>';
			echo '      <p class="card-text">'.utf8_encode($row['NomCF']).'.</p>';
			echo '      <a href="#" class="btn btn-primary btn-sm">Ves-hi</a>';
			echo '    </div>';
			echo '  </div>';
			$row = $ResultSet->fetch_assoc();
		}
	}
	$ResultSet->close();
}

echo '</div>';

echo "<DIV id=debug></DIV>";

$conn->close();

?>















