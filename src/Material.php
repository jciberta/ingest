<?php

/** 
 * Material.php
 * Gestió del material: sortida i entrada.
 * 
 * POST:
 * - accio: SortidaMaterial|EntradaMaterial
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibHTML.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!isset($_POST))
	header("Location: Surt.php");

$accio = (array_key_exists('accio', $_POST)) ? $_POST['accio'] : ''; 

switch ($accio) {
    case "SortidaMaterial":
//print_h($_POST);  
        $UsuariId = mysqli_real_escape_string($conn, $_POST['cmb_usuari_id']);
        CreaIniciHTML($Usuari, 'Sortida de material');
        if ($UsuariId > 0) {
            // $UsuariId = mysqli_real_escape_string($conn, $_POST['lkh_usuari']);
            foreach ($_POST as $key => $value) {
                if (str_starts_with($key, 'chk_')) {
                    $MaterialId = str_replace('chk_', '', $key);
                    $SQL = "INSERT INTO PRESTEC_MATERIAL (material_id, usuari_id, responsable_id, data_sortida) VALUES (?, ?, ?, NOW())";
                    $stmt = $conn->prepare($SQL);
                    $stmt->bind_param("iii", $MaterialId, $UsuariId, $Usuari->usuari_id);
                    $stmt->execute();
                }
            }
            HTML::EscriuMissatge(HTML::tmSUCCES, "Préstec realitzat amb èxit.");
        }
        else 
            HTML::EscriuMissatge(HTML::tmERROR, "No s'ha especificat usuari per fer la sortida.");
        CreaFinalHTML();
        break;
    case "EntradaMaterial":
        CreaIniciHTML($Usuari, 'Entradade material');
//print_h($_POST);  
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'chk_')) {
                $PrestecMaterialId = str_replace('chk_', '', $key);
                $SQL = "UPDATE PRESTEC_MATERIAL SET data_entrada=NOW() WHERE prestec_material_id=?";
                $stmt = $conn->prepare($SQL);
                $stmt->bind_param("i", $PrestecMaterialId);
                $stmt->execute();
            }
        }
        HTML::EscriuMissatge(HTML::tmSUCCES, "Préstec retornat amb èxit.");
        CreaFinalHTML();
        break;
    }

$conn->close();
