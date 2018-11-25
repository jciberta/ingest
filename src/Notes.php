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
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 

CreaIniciHTML('Notes cicle/nivell');
echo '<script language="javascript" src="js/Notes.js" type="text/javascript"></script>';
echo '<script language="javascript" type="text/javascript">let timerId = setInterval(ActualitzaTaulaNotes, 5000);</script>';

echo "<P><font color=blue>S'ha de sortir de la cel·la per que quedi desada.</font></P>";

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
//	echo "<TR><TD width=200></TD>";
	for($j = 0; $j < count($Notes->UF[0]); $j++) {
		$row = $Notes->UF[0][$j];
		$aModuls[$j] = utf8_encode($row["CodiMP"]);
//		echo "<TD width=25>".utf8_encode($row["CodiMP"])."</TD>";
	}
	$aOcurrenciesModuls = Ocurrencies($aModuls);
//print_r($aOcurrenciesModuls);

	echo "<TR><TD width=200></TD>";
	for($i = 0; $i < count($aOcurrenciesModuls); $i++) {
		$iOcurrencies = $aOcurrenciesModuls[$i][1];
		echo "<TD width=".($iOcurrencies*25)." colspan=".$iOcurrencies.">".utf8_encode($aOcurrenciesModuls[$i][0])."</TD>";
	}
	
	echo "<TD></TD></TR>";
	echo "<TR><TD width=200></TD>";
	for($j = 0; $j < count($Notes->UF[0]); $j++) {
		$row = $Notes->UF[0][$j];
		echo "<TD width=20>".utf8_encode($row["CodiUF"])."</TD>";
	}
	echo "<TD></TD></TR>";

	for($i = 0; $i < count($Notes->Alumne); $i++) {
		echo "<TR>";
		$row = $Notes->Alumne[$i];
		echo "<TD width=200>".utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"])."</TD>";
		for($j = 0; $j < count($Notes->UF[$i]); $j++) {
			$row = $Notes->UF[$i][$j];
			$ValorNota = NumeroANota($row["nota".$row["Convocatoria"]]);
			$Deshabilitat = ($row["baixa"] == 1)? ' disabled ' : '';
			echo "<TD width=2><input type=text name=txtNotaId_".$row["NotaId"]."_".$row["Convocatoria"]." value='".$ValorNota."' size=1 ".$Deshabilitat." onfocus='ObteNota(this);' onBlur='ActualitzaNota(this);'></TD>";
		}
		echo "<TD></TD></TR>";
	}
	echo "</TABLE>";
	echo "<input type=hidden name=TempNota value=''>";
	echo "</FORM>";
	
	
//echo '<form> <label for="ccnum">CC Number</label><br> <input size="16" name="ccnum" id="ccnum">
//<br> <label for="ccv">CCV</label> <input id="ccv" name="ccv" size="4"> </form>';
	
	

}

/*
echo "<script>function test() {var s='input[name=txtNotaId_1_1]'; $(s).val('XXX'); var s='input[id=txt]'; $(s).val('XXX');}</script>";
echo '<form id=form2 method="post" action="">';
echo '<input maxlength=6 size=6 id=txtNotaId_100_100 value=#EEEEEE type=text onBlur="ActualitzaNota(this);">';
echo '</form>';
echo '<button onclick="test()">Test</button>';*/

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$ResultSet->close();

$conn->close(); 
 
 ?>
