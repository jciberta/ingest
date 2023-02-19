<?php

/** 
 * LibExpedient.ajax.php
 *
 * Accions AJAX per a l'expedient.
 *
 * Accés:
 *   - Administrador, direcció, cap d'estudis, professor
 * Accions:
 *   - ActualitzaComentari
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibSeguretat.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'ActualitzaComentari') {
        $MatriculaId = $_REQUEST['MatriculaId'];
        $Valor = $_REQUEST['valor'];
        $SQL = "UPDATE MATRICULA SET comentari_matricula_seguent=? WHERE matricula_id=?";
		$stmt = $conn->prepare($SQL);
		$stmt->bind_param("si", $Valor, $MatriculaId);
		$stmt->execute();
	}
	else if ($_REQUEST['accio'] == 'ActualitzaCasellaUF2n') {
        $MatriculaId = $_REQUEST['MatriculaId'];
        $UFId = $_REQUEST['UFId'];
        $check = ($_REQUEST['check'] == 'true') ? 0 : 1;
        $SQL = "UPDATE PROPOSTA_MATRICULA SET baixa=? WHERE matricula_id=? AND unitat_formativa_id=?";
		$stmt = $conn->prepare($SQL);
		$stmt->bind_param("iii", $check, $MatriculaId, $UFId);
		$stmt->execute();
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