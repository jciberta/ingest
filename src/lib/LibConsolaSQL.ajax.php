<?php

/** 
 * LibConsolaSQL.ajax.php
 *
 * Accions AJAX per a la llibreria de la consola SQL.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibStr.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'ExecutaSQL') {
		$SQL = $_REQUEST['sql'];
		$Ordre = strtoupper(PrimeraParaula($SQL));
		if ($Ordre == 'SELECT' or $Ordre =='DESCRIBE') {
			$ResultSet = $conn->query($SQL);
			if ($ResultSet->num_rows > 0) {
				$Taula = "<BR><TABLE>";
				$PrimerCop = True;
				while($row = $ResultSet->fetch_assoc()) {
					$keys = array_keys($row);
					if ($PrimerCop) {
						// Capçalera de la taula
						$Taula .= "<THEAD>";
						for ($i=0; $i<count($keys); $i++) 
							$Taula .= "<TH>".$keys[$i]."</TH>";
						$Taula .= "</THEAD>";
						$PrimerCop = False;
					}
					$Taula .= "<TR>";
					for ($i=0; $i<count($keys); $i++)
						$Taula .= "<TD>".utf8_encode($row[$keys[$i]])."</TD>";
					$Taula .= "</TR>";
				}
				$Taula .= "</TABLE>";
			}
			$ResultSet->close();
			print $Taula;
		}
		else {	
			$Taula = '<BR>SQL executada amb èxit.';
			try {
				if (!$conn->query($SQL))
					throw new Exception($conn->error.'.<br>SQL: '.$SQL);
			} catch (Exception $e) {
				$Taula = "<BR><b>ERROR ExecutaSQL</b>. Causa: ".$e->getMessage();
			}	
			print $Taula;
		}
	}
	else {
		if ($CFG->Debug)
			print "Acció no suportada. Valor de $_POST: ".json_encode($_POST);
		else
			print "Acció no suportada.";
	}
}
else 
    print "ERROR. No hi ha POST o no hi ha acció.";

?>