<?php

/** 
 * Notes.php
 *
 * Mostra les notes d'un cicle i un nivell.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once('lib/LibStr.php');
require_once('lib/LibHTML.php');
require_once('lib/LibNotes.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

CreaIniciHTML('Notes cicle/nivell');

// Pedaç per forçar el navegador a regarregar el JavaScript i no usar la caché.
// https://stackoverflow.com/questions/44456644/javascript-function-not-working-due-to-cached-js-file
// https://community.esri.com/thread/187211-how-to-force-a-browser-cache-refresh-after-updating-wab-app
echo '<script language="javascript" src="js/Notes.js?v1.0" type="text/javascript"></script>';
echo '<script language="javascript" type="text/javascript">let timerId = setInterval(ActualitzaTaulaNotes, 5000);</script>';

echo "<P><font color=blue>S'ha de sortir de la cel·la per que la nota quedi desada. Utilitza les fletxes per moure't lliurement per la graella.</font></P>";

$CicleId = $_GET['CicleId'];
$Nivell = $_GET['Nivell'];

$SQL = CreaSQLNotes($CicleId, $Nivell);
//print_r($SQL);

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
//print_r($ResultSet);	

	// Creem un objecte per administrar les notes
	$Notes = new stdClass();
	$i = -1; 
	$j = 0;
	$AlumneId = -1;
	$row = $ResultSet->fetch_assoc();
	while($row) {
//print_r($row);
		if ($row["AlumneId"] != $AlumneId) {
			$AlumneId = $row["AlumneId"];
			$i++;
			$Notes->Alumne[$i] = $row;
			$j = 0; 
		}	
		$Notes->UF[$i][$j] = $row;
		$j++;
		$row = $ResultSet->fetch_assoc();
	}
//print_r($Notes);


	// Formulari amb les notes
	echo '<FORM id=form method="post" action="">';
	echo '<input type=hidden id=CicleId value='.$CicleId.'>';
	echo '<input type=hidden id=Nivell value='.$Nivell.'>';
//	echo '<TABLE border=0 width="100%">';
	echo '<TABLE border=0>';

	// Capçalera de la taula
	$aModuls = [];
	for($j = 0; $j < count($Notes->UF[0]); $j++) {
		$row = $Notes->UF[0][$j];
		$aModuls[$j] = utf8_encode($row["CodiMP"]);
	}
	$aOcurrenciesModuls = Ocurrencies($aModuls);
//print_r($aOcurrenciesModuls);

	// Mòdul
	echo "<TR><TD></TD><TD></TD><TD></TD>";
	for($i = 0; $i < count($aOcurrenciesModuls); $i++) {
		$iOcurrencies = $aOcurrenciesModuls[$i][1];
		echo "<TD width=".($iOcurrencies*25)." colspan=".$iOcurrencies.">".utf8_encode($aOcurrenciesModuls[$i][0])."</TD>";
	}
	echo "<TD></TD></TR>";
	
	// Unitat formativa
	echo "<TR><TD></TD><TD></TD><TD></TD>";
	for($j = 0; $j < count($Notes->UF[0]); $j++) {
		$row = $Notes->UF[0][$j];
		echo "<TD width=20 style='text-align:center'>".utf8_encode($row["CodiUF"])."</TD>";
	}
	echo "<TD style='text-align:center' colspan=2>Hores</TD></TR>";

	// Hores
//	echo "<TD></TD></TR>";
	echo "<TR><TD></TD>";
	echo "<TD style='text-align:center'>Grup</TD>";
	echo "<TD style='text-align:center'>Tutoria</TD>";
	$TotalHores = 0;
	for($j = 0; $j < count($Notes->UF[0]); $j++) {
		$row = $Notes->UF[0][$j];
		$TotalHores += $row["Hores"];
		echo "<TD width=20 align=center>".$row["Hores"]."</TD>";
	}
	echo "<TD style='text-align:center'>".$TotalHores."</TD>";
	echo "<TD style='text-align:center'>&percnt;</TD></TR>";

	for($i = 0; $i < count($Notes->Alumne); $i++) {
		echo "<TR>";
		$row = $Notes->Alumne[$i];
//		echo "<TD width=200>".utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"])."</TD>";
		echo "<TD>".utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"])."</TD>";
		echo "<TD style='text-align:center'>".$row["Grup"]."</TD>";
		echo "<TD style='text-align:center'>".$row["GrupTutoria"]."</TD>";
		$Hores = 0;
		for($j = 0; $j < count($Notes->UF[$i]); $j++) {
			$row = $Notes->UF[$i][$j];
			$style = "text-align:center";
			$Baixa = (($row["BaixaUF"] == 1) || ($row["BaixaMatricula"] == 1));
			$Deshabilitat = ($Baixa) ? ' disabled ' : '';
			if ($row["Convocatoria"] == 0) {
				$Nota = UltimaNota($row);
				$Deshabilitat = " disabled ";
				$style .= ";background-color:black;color:white";
			}
			else {
				$Nota = $row["nota".$row["Convocatoria"]];
				if ($row["Orientativa"] && !$Baixa) {
					$style .= ";background-color:yellow";
				}
			}
			if ($Nota >= 5)
				$Hores += $row["Hores"];
			$ValorNota = NumeroANota($Nota);
			$Id = 'grd_'.$i.'_'.$j;
			echo "<TD width=2><input type=text ".$Deshabilitat." style='".$style."' name=txtNotaId_".$row["NotaId"]."_".$row["Convocatoria"]." id='".$Id."' value='".$ValorNota."' size=1 onfocus='ObteNota(this);' onBlur='ActualitzaNota(this);' onkeydown='NotaKeyDown(this, event);'></TD>";
		}
		echo "<TD style='text-align:center'>".$Hores."</TD>";
		echo "<TD style='text-align:center'>".number_format($Hores/$TotalHores*100, 2)."&percnt;</TD>";
		echo "<TD></TD></TR>";
	}
	echo "</TABLE>";
	echo "<input type=hidden name=TempNota value=''>";
	echo "</FORM>";
}

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$ResultSet->close();

$conn->close(); 
 
 ?>
