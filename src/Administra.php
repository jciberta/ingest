<?php

/** 
 * Administra.php
 *
 * Utilitats d'administració.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibAdministracio.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!$Usuari->es_admin)
	header("Location: Surt.php");

// Prevalen les dades passades per POST.
if (isset($_POST) && !empty($_POST))
	$PARAM = $_POST;
else
	$PARAM = $_GET;
//print_r($PARAM);
//exit;

$accio = (isset($PARAM) && array_key_exists('accio', $PARAM)) ? $PARAM['accio'] : 'BaseDades';

$Administracio = new Administracio($conn, $Usuari);

switch ($accio) {
    case "BaseDades":
		CreaIniciHTML($Usuari, 'Administració');
		$SQL = 'SHOW TABLES';
		//print_r($SQL);

		$ResultSet = $conn->query($SQL);
		if ($ResultSet->num_rows > 0) {
			echo "<TABLE>";
			echo "<TH>Taula</TH>";
			while($row = $ResultSet->fetch_assoc()) {
				$keys = array_keys($row);
				
		//		$my_arr[$keys[1]]
		//print_r($row);		
				echo "<TR>";
				echo "<TD><A HREF='Administra.php?accio=Taula&taula=".$row[$keys[0]]."'>".$row[$keys[0]]."</A></TD>";
				echo "</TR>";
			}
			echo "</TABLE>";
		}
		$ResultSet->close();

		break;
    case "Taula":
		CreaIniciHTML($Usuari, 'Administració');
		$Taula = $PARAM['taula'];
		
		// Metadades
		echo "Metadades taula <B>$Taula</B><BR><BR>";
		$Metadades = $Administracio->ObteMetadades($Taula);
		$ClauPrimaria = $Administracio->ClauPrimariaDesDeMetadades($Metadades);
		$aClauPrimaria = explode(",", $ClauPrimaria);
		echo $Administracio->CreaTaulaMetadades($Metadades);

		// Dades
		echo "<BR>Taula <B>$Taula</B><BR><BR>";
		$Where = '';
		if (count($PARAM)>2) {
			$keys = array_keys($PARAM);
			for ($i=2; $i<count($PARAM); $i++) {
				if ($PARAM[$keys[$i]] != '')
					$Where .= " AND ".$keys[$i]."='".$PARAM[$keys[$i]]."'";
			}
		}
		$SQL = 'SELECT * FROM '.$Taula.' WHERE (0=0) '.$Where;
//print_r($SQL);	
		$ResultSet = $conn->query($SQL);
		if ($ResultSet->num_rows > 0) {
			echo '<form action="Administra.php" method="post" id="AdministraTaula">';
			echo '<input type=hidden id=accio name=accio value="'.$accio.'">';
			echo '<input type=hidden id=taula name=taula value="'.$Taula.'">';
			echo "<TABLE>";
			$PrimerCop = True;
			while($row = $ResultSet->fetch_assoc()) {
				$keys = array_keys($row);
				if ($PrimerCop) {
					// Capçalera de la taula
					echo "<THEAD>";
					for ($i=0; $i<count($keys); $i++) {
						echo "<TH>".$keys[$i]."</TH>";
					}
					echo "</THEAD>";
					// Camps per filtrar, estil QBE
					for ($i=0; $i<count($keys); $i++) {
						$Valor = (isset($PARAM) && array_key_exists($keys[$i], $PARAM)) ? $PARAM[$keys[$i]] : '';
						echo '<TD><input type="text" name="'.$keys[$i].'" value="'.$Valor.'" size="1px" style="width:100%"></TD>';
					}
					// Botó per filtrar
					echo '<TD><button class="btn btn-primary active" type="submit" form="AdministraTaula" value="Submit">Filtra</button></TD>';
					$PrimerCop = False;
				}
				echo "<TR>";
				for ($i=0; $i<count($keys); $i++) {
					echo "<TD>".utf8_encode($row[$keys[$i]])."</TD>";
				}
				$ClauPrimaria = implode(",", $aClauPrimaria);
				if ($ClauPrimaria != '') {
//print_r($ClauPrimaria);					
					// Si hi ha clau primària, es pot editar i esborrar
					echo "<TD>";
					$ClauPrimaria = implode(",", $aClauPrimaria);
					$aValor = [];
					for ($i=0; $i<count($aClauPrimaria); $i++)
						array_push($aValor, $row[$aClauPrimaria[$i]]);
					$Valor = implode(",", $aValor);
					
					echo "<A href='Administra.php?accio=EditaTaula&Taula=$Taula&Clau=".$ClauPrimaria."&Valor=".$Valor."'><IMG src=img/edit.svg></A>&nbsp&nbsp";
					//$Funcio = 'SuprimeixRegistre("'.$this->Taula.'", "'.$this->ClauPrimaria.'", '.$row[$this->ClauPrimaria].');';
					$Funcio = '';
					echo "<A href=# onClick='".$Funcio."' data-toggle='modal' data-target='#confirm-delete'><IMG src=img/delete.svg></A>&nbsp&nbsp";
					echo "</TD>";
				}
				echo "</TR>";
			}
			echo "</TABLE>";
			echo '</form>';
		}
		$ResultSet->close();
		break;
	case "EditaTaula":
		$Taula = $PARAM['Taula'];
		$Clau = $PARAM['Clau'];
		$Valor = $PARAM['Valor'];
		$Administracio->EscriuFitxaEdicioRegistre($Taula, $Clau, $Valor);
		break;
}

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$conn->close(); 
 
 ?>
