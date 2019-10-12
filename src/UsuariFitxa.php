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
	header("Location: Surt.php");
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
$frm->AfegeixText('username', 'Usuari', 100, [FormFitxa::offREQUERIT]);
$frm->AfegeixText('nom', 'Nom', 100, [FormFitxa::offREQUERIT]);
$frm->AfegeixText('cognom1', '1r cognom', 100, [FormFitxa::offREQUERIT]);
$frm->AfegeixText('cognom2', '2n cognom', 100, [FormFitxa::offAL_COSTAT]);

$frm->Pestanya('Dades');
$frm->AfegeixText('codi', 'Codi (codi professor, IDALU per alumne)', 100);
$frm->AfegeixLlista('sexe', 'Sexe', 30, array('H', 'D', 'N'), array('Home', 'Dona', 'Neutre'), [FormFitxa::offREQUERIT]);
$frm->AfegeixLlista('tipus_document', 'Tipus document', 30, array('D', 'N', 'P'), array('Dni', 'Nie', 'Passaport'), [FormFitxa::offREQUERIT]);
$frm->AfegeixText('document', 'Document', 100, [FormFitxa::offAL_COSTAT]);
$frm->AfegeixText('email', 'Correu electrònic', 100);
$frm->AfegeixData('data_naixement', 'Data naixement');
$frm->AfegeixText('municipi_naixement', 'Municipi naixement', 100);
$frm->AfegeixText('nacionalitat', 'Nacionalitat', 100);

//$frm->AfegeixPassword('password', 'Contrasenya', 100, [FormFitxa::offREQUERIT]);
$frm->AfegeixCheckBox('imposa_canvi_password', 'Imposa nova contrasenya?');
$frm->AfegeixCheckBox('usuari_bloquejat', "Bloqueja l'usuari?");

$frm->Pestanya('Contacte');
$frm->AfegeixText('telefon', 'Telèfons', 100);
$frm->AfegeixText('adreca', 'Adreça', 100);
$frm->AfegeixText('codi_postal', 'Codi postal', 100);
$frm->AfegeixText('poblacio', 'Població', 100);
$frm->AfegeixText('municipi', 'Municipi', 100);
$frm->AfegeixText('provincia', 'Província', 100);
$frm->AfegeixCheckBox('permet_tutor', "Permet tutor? (vàlid pels >=18 anys)");

$frm->Pestanya('Rols');
//$frm->IniciaColumnes();
$frm->AfegeixCheckBox('es_direccio', 'És direcció?');
$frm->AfegeixCheckBox('es_cap_estudis', "És cap d'estudis?", [FormFitxa::offAL_COSTAT]);
$frm->AfegeixCheckBox('es_cap_departament', "És cap de departament?", [FormFitxa::offAL_COSTAT]);
//$frm->SaltaColumna();
$frm->AfegeixCheckBox('es_tutor', "És tutor?");
$frm->AfegeixCheckBox('es_professor', "És professor?", [FormFitxa::offAL_COSTAT]);
$frm->AfegeixCheckBox('es_alumne', "És alumne?", [FormFitxa::offAL_COSTAT]);
//$frm->SaltaColumna();
$frm->AfegeixCheckBox('es_pare', "És pare?");
//$frm->FinalitzaColumnes();

$frm->Pestanya('Expedient');

$frm->EscriuHTML();

?>