<?php

/** 
 * UsuariFitxa.php
 *
 * Formulari de la fitxa de l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

// Obtenció de l'identificador, sinó registre nou.
$Id = empty($_GET) ? -1 : $_GET['Id'];

$frm = new FormFitxa($conn, $Usuari);
$frm->Titol = 'Edició usuari';
$frm->Taula = 'USUARI';
$frm->ClauPrimaria = 'usuari_id';
$frm->Id = $Id;
$frm->AfegeixText('username', 'Usuari', True, 100);
$frm->AfegeixText('nom', 'Nom', True, 100);
$frm->AfegeixText('cognom1', '1r cognom', True, 100);
$frm->AfegeixText('cognom2', '2n cognom', False, 100);
$frm->AfegeixPassword('password', 'Contrasenya', True, 100);
$frm->AfegeixCheckBox('imposa_canvi_password', 'Imposa nova contrasenya?', False);
$frm->AfegeixCheckBox('usuari_bloquejat', "Bloqueja l'usuari?", False);

$frm->AfegeixCheckBox('es_direccio', 'És direcció?', False);
$frm->AfegeixCheckBox('es_cap_estudis', "És cap d'estudis?", False);
$frm->AfegeixCheckBox('es_cap_departament', "És cap de departament?", False);
$frm->AfegeixCheckBox('es_tutor', "És tutor?", False);
$frm->AfegeixCheckBox('es_professor', "És professor?", False);
$frm->AfegeixCheckBox('es_alumne', "És alumne?", False);
$frm->AfegeixCheckBox('es_pare', "És pare?", False);
$frm->EscriuHTML();


?>
