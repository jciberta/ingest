<?php

/** 
 * Canvia.php
 *
 * Utilitat per canviar d'usuari (administrador) o de rol (direcció, cap d'estudis).
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

if (!$Usuari->es_admin && !$Usuari->era_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);
$accio = (array_key_exists('accio', $_GET)) ? $_GET['accio'] : ''; 

switch ($accio) {
    case "CanviaRolAProfessor":
		!$Usuari->es_direccio = False;
		!$Usuari->es_cap_estudis = False;
		$_SESSION['USUARI'] = serialize($Usuari);
		header("Location: Escriptori.php");
		break;
	case "SeleccionaUsuari":
		if (!$Usuari->es_admin)
			header("Location: Surt.php");
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Titol = "Usuaris";
		$frm->SQL = 'SELECT usuari_id, username, nom, cognom1, cognom2, email, email_ins, codi, es_alumne, es_professor, es_pare, usuari_bloquejat '.
			' FROM USUARI ORDER BY cognom1, cognom2, nom';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'nom, cognom1, cognom2, username, email, email_ins, codi, bool:es_alumne, bool:es_professor, bool:es_pare';
		$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari, Correu, Correu INS, Codi, Alumne, Professor, Pare';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'UsuariFitxa.php';
		$frm->AfegeixOpcio('Canvia', 'Canvia.php?accio=CanviaAUsuari&Id=');
		
		// Repensar!
/*		$frm->Filtre->AfegeixCheckBox('es_professor', 'Professors', True);
		$frm->Filtre->AfegeixCheckBox('es_alumne', 'Alumnes', True);
		$frm->Filtre->AfegeixCheckBox('es_pare', 'Pares', True);
		$frm->Filtre->AfegeixCheckBox('es_administratiu', 'Administratius', True);
*/
		
		$frm->Filtre->AfegeixLlista('usuari_bloquejat', 'Bloquejat', 30, array('', '0', '1'), array('Tots', 'No bloquejat', 'Bloquejat'));
		$frm->EscriuHTML();
        break;	
	case "CanviaAUsuari":
		if (!$Usuari->es_admin)
			header("Location: Surt.php");
		$Id = $_GET['Id'];
		$SQL = "SELECT * FROM USUARI WHERE usuari_id=$Id";
		$ResultSet = $conn->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$user = $ResultSet->fetch_object();
			$user->era_admin = ($Usuari->es_admin == 1) ? 1 : 0;
			if ($user->era_admin) 
				$user->admin = $Usuari;
			$_SESSION['USUARI'] = serialize($user);
			header("Location: Escriptori.php");
		}
		else 
			header("Location: Surt.php");
		break;
	case "TornaAAdmin":
		if (!$Usuari->era_admin)
			header("Location: Surt.php");
		$user = $Usuari->admin;
		$_SESSION['USUARI'] = serialize($user);
		header("Location: Escriptori.php");
		break;
}

?>
