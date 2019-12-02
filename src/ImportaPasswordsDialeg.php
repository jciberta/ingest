<?php

/** 
 * ImportaPasswordsDialeg.php
 *
 * Diàleg per importar els usuaris.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibImporta.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

CreaIniciHTML($Usuari, "Importació de contrasenyes iEduca");

echo "<P><font color=blue>El fitxer a importar ha d'estar en UTF-8.</font><BR>";
echo "Només s'importaran els alumnes i pares, se'ls desbloquejarà i s'imposarà una nova contrasenya.</P>";

echo '<form action="ImportaPasswords.php" method="post" enctype="multipart/form-data">';
echo '	<div class="form-group">';
echo '		<input class="form-control-file" type="file" name="FitxerCSV" id="FitxerCSV" accept=".csv">';
echo '	</div>';
echo '	<input type="submit" name="submit" value="Importa" class="btn btn-primary">';
echo '</form>';
 
?>