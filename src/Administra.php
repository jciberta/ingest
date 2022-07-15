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
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibAdministracio.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!$Usuari->es_admin)
	header("Location: Surt.php");

RecuperaGET($_GET);

// Prevalen les dades passades per POST.
if (isset($_POST) && !empty($_POST))
	$PARAM = $_POST;
else
	$PARAM = $_GET;
echo '<br>';
//print_h($PARAM);
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
				$URL = GeneraURL('Administra.php?accio=Taula&taula='.$row[$keys[0]]);
				echo "<TD><A HREF=$URL>".$row[$keys[0]]."</A></TD>";
//				echo "<TD><A HREF='Administra.php?accio=Taula&taula=".$row[$keys[0]]."'>".$row[$keys[0]]."</A></TD>";
				echo "</TR>";
			}
			echo "</TABLE>";
		}
		$ResultSet->close();

		break;
    case "Taula":
		$Taula = $PARAM['taula'];
		
		// Metadades
		$SubTitol = "Metadades taula <B>$Taula</B><BR><BR>";
		$Metadades = $Administracio->ObteMetadades($Taula);
//print_h($Metadades);
//exit;
		$ClauPrimaria = $Administracio->ClauPrimariaDesDeMetadades($Metadades);
		$aClauPrimaria = explode(",", $ClauPrimaria);
		$SubTitol .= $Administracio->CreaTaulaMetadades($Metadades);

		// Dades
		$SubTitol .= "<BR>Taula <B>$Taula</B><BR><BR>";
		$SQL = 'SELECT * FROM '.$Taula.' WHERE (0=0) ';

		$frm = new FormRecercaQBE($conn, $Usuari, $Sistema);
		$frm->Titol = "Administració";
		$frm->SubTitol = $SubTitol;
		$frm->SQL = $SQL;
		$frm->Taula = $Taula;
		$frm->ClauPrimaria = $ClauPrimaria;
		$frm->PermetEditar = True;
		$frm->URLEdicio = "Administra.php?accio=EditaTaula&Taula=$Taula&Clau=$ClauPrimaria&Valor=";
		$frm->PermetSuprimir = True;
		$frm->EscriuHTML();
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
