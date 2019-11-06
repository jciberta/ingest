<?php

/** 
 * Recerca.php
 *
 * Formularis de recerques per les taules generals:
 *  - Any acadèmic
 *  - 
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
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (empty($_GET))
	header("Location: index.html");

$accio = (array_key_exists('accio', $_GET)) ? $_GET['accio'] : ''; 

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");

// Obtenció de la modalitat del formulari
$Modalitat = FormRecerca::mfLLISTA;
if (isset($_GET) && array_key_exists('Modalitat', $_GET) && $_GET['Modalitat']=='mfBusca') 
	$Modalitat = FormRecerca::mfBUSCA;

// Destruim l'objecte per si estava ja creat
unset($frm);

switch ($accio) {
    case "AnyAcademic":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Any acadèmic';
		$frm->SQL = 'SELECT * FROM ANY_ACADEMIC';
		$frm->Taula = 'ANY_ACADEMIC';
		$frm->ClauPrimaria = 'any_academic_id';
		$frm->Camps = 'nom, any_inici, any_final, data_inici, data_final, bool:actual';
		$frm->Descripcions = 'Nom, Any inicial, Any final, Data inici, Data final, Actual';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=AnyAcademic';
		$frm->PermetAfegir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->PermetSuprimir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->EscriuHTML();
        break;
    case "Departament":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Departaments';
		$SQL = ' SELECT D.departament_id, D.nom AS NomDepartament, '.
			' U.usuari_id, U.nom AS NomProfessor, U.cognom1 AS Cognom1Professor, U.cognom2 AS Cognom2Professor, U.username '.
			' FROM DEPARTAMENT D '.
			' LEFT JOIN USUARI U ON (D.cap=U.usuari_id) ';
		$frm->SQL = $SQL;
		$frm->Taula = 'DEPARTAMENT';
		$frm->ClauPrimaria = 'departament_id';
		$frm->Camps = 'NomDepartament, NomProfessor, Cognom1Professor, Cognom2Professor';
		$frm->Descripcions = 'Departament, Nom, 1r cognom, 2n cognom';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=Departament';
		$frm->PermetAfegir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->PermetSuprimir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->EscriuHTML();
        break;
}

?>
