<?php

/** 
 * CanviaRol.php
 *
 * Canvia de rol a professor (només per cap d'estudis).
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

if (!$Usuari->es_cap_estudis)
	header("Location: Surt.php");

!$Usuari->es_cap_estudis = False;

$_SESSION['USUARI'] = serialize($Usuari);
header("Location: Escriptori.php");

?>
