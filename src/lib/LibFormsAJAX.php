<?php

/** 
 * LibFormsAJAX.php
 *
 * Accions AJAX per a la llibreria de formularis.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */
 

require_once('../Config.php');
require_once('LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../index.html");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
}


// print 'AJAX';


if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'ActualitzaTaula') {
//		$nom = $_REQUEST['nom'];
//		$valor = $_REQUEST['valor'];
		$SQL = $_REQUEST['sql'];
		$cerca = $_REQUEST['cerca'];
		$camps = $_REQUEST['camps'];
		$descripcions = $_REQUEST['descripcions'];

//		print 'AJAX';
//		print $descripcions;

		$frm = new FormRecerca($conn);
//		$frm->Titol = 'Usuaris';
		$frm->SQL = $SQL;
		$frm->Camps = $camps;
		$frm->Filtre = $cerca;
		$frm->Descripcions = $descripcions;
		print $frm->GeneraTaula();


/*		
		$conn->query($SQL);
		print $SQL;*/

	}
	else
        print "Acció no suportada.";
}
else 
    print "ERROR. No hi ha POST o no hi ha acció.";

?>
