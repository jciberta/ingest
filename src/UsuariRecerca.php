<?php

/** 
 * UsuariRecerca.php
 *
 * Formulari de la recerca de l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once('lib/LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

// Obtenció de la modalitat del formulari.
$Modalitat = (!empty($_GET) && $_GET['Modalitat']=='mfBusca') ? FormRecerca::mfBUSCA : FormRecerca::mfLLISTA;
/*if (!empty($_GET) && $_GET['Modalitat']=='mfBusca')
	$Modalitat = FormRecerca::mfBUSCA;
else
	$Modalitat = FormRecerca::mfLLISTA;*/

$frm = new FormRecerca($conn, $Usuari);
$frm->Modalitat = $Modalitat;
$frm->Titol = 'Usuaris';
$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2 FROM USUARI';
$frm->ClauPrimaria = 'usuari_id';
$frm->Camps = 'nom, cognom1, cognom2, username';
$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari';
$frm->PermetEditar = True;
$frm->URLEdicio = 'UsuariFitxa.php';
$frm->PermetSuprimir = True;
$frm->EscriuHTML();

?>
