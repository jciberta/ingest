<?php

/** 
 * Matricula.php
 *
 * Matriculació d'un alumne.
 *
 * Quan es crea la matrícula d'un alumne:
 * 1. Pel nivell que sigui, es creen les notes, una per cada UF d'aquell cicle
 * 2. Si l'alumne és a 2n, l'aplicació ha de buscar les que li han quedar de primer per afegir-les
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibMatricula.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);

CreaIniciHTML($Usuari, 'Matrícula');

$alumne = mysqli_real_escape_string($conn, $_POST['lkh_alumne']);
$curs = mysqli_real_escape_string($conn, $_POST['cmb_curs']);
$grup = mysqli_real_escape_string($conn, $_POST['cmb_grup']);
$GrupTutoria = mysqli_real_escape_string($conn, $_POST['cmb_grup_tutoria']);

if (($alumne == '') || ($curs == '')) {
	echo '<div class="alert alert-danger" id="MissatgeError" role="alert">';
	echo "Error en els paràmetres!";
	echo '</div>';
	exit;
}

$Mat = new Matricula($conn, $Usuari, $Sistema);

if ($Mat->CreaMatricula($curs, $alumne, $grup, $GrupTutoria) == -1) {
	echo '<div class="alert alert-danger" id="MissatgeError" role="alert">';
	echo "L'alumne ja està matriculat!";
	echo '</div>';
}
else {
	echo '<div class="alert alert-success" id="MissatgeCorrecte" role="alert">';
	echo "La matrícula s'ha creat correctament.";
	echo '</div>';
	
	// Llistem les UF del cicle/nivell
	$SQL = '
		SELECT 
			UPE.nom AS NomUF, UPE.hores AS HoresUF, MPE.codi AS CodiMP, MPE.nom AS NomMP, CPE.nom AS NomCF, UPE.*, MPE.*, CPE.* 
		FROM UNITAT_PLA_ESTUDI UPE
		LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
		LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
		LEFT JOIN CURS C ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id) 
		WHERE C.curs_id=? AND C.nivell=UPE.nivell 
		ORDER BY MPE.codi, UPE.codi;
	';

	$stmt = $conn->prepare($SQL);
	$stmt->bind_param("i", $curs);
	$stmt->execute();

	$ResultSet = $stmt->get_result();
	if ($ResultSet->num_rows > 0) {
		echo '<TABLE class="table table-sm table-striped">';
		echo '<THEAD class="thead-dark">';
		echo "<TH>Cicle</TH>";
		echo "<TH>Mòdul</TH>";
		echo "<TH>UF</TH>";
		echo "<TH>Hores</TH>";
		echo '</THEAD>';
		while($row = $ResultSet->fetch_assoc()) {
			echo "<TR>";
			echo utf8_encodeX("<TD>".$row["NomCF"]."</TD>");
			echo utf8_encodeX("<TD>".$row["CodiMP"].'. '.$row["NomMP"]."</TD>");
			echo utf8_encodeX("<TD>".$row["NomUF"]."</TD>");
			echo "<TD>".$row["HoresUF"]."</TD>";
			echo "</TR>";
		}
		echo "</TABLE>";
	};	
	$ResultSet->close();
}

$conn->close();
