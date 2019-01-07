<?php

/** 
 * Autenticacio.php
 *
 * Autenticació de l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once('lib/LibHTML.php');
require_once('lib/LibInet.php');

session_start();

if (!empty($_POST)) 
{
	//print_r($_POST);
	if (isset($_POST['usuari']) && isset($_POST['password'])) 
	{
		// Prevenim injecció SQL a l'usuari. Només pot contenir lletres o números.
		// No cal amb el password ja que es compara amb un hash i no forma part de la sentència SQL.
		if (ctype_alnum($_POST['usuari'])) {
			try
			{
				$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
				if ($conn->connect_error) {
					die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
				} 

				$SQL = "SELECT * FROM USUARI WHERE username='". $_POST['usuari']."' AND NOT usuari_bloquejat=1 ";
				$ResultSet = $conn->query($SQL);
				if ($ResultSet->num_rows > 0) {
					$user = $ResultSet->fetch_object();
					//echo "Password: ". $user->password;
					if (password_verify($_POST['password'], $user->password)) 
					{
						$SQL = "SELECT * FROM SISTEMA";
						$ResultSet = $conn->query($SQL);
						if ($ResultSet->num_rows == 0) 
							die("El sistema no ha estat configurat.");
						$sistema = $ResultSet->fetch_object();

						$_SESSION['usuari_id'] = $user->usuari_id;
						// ToDo: Seguretat a la sessió
						// https://stackoverflow.com/questions/1442177/storing-objects-in-php-session
						// https://stackoverflow.com/questions/12233406/preventing-session-hijacking
						// https://meta.stackexchange.com/questions/69171/why-doesnt-the-stack-overflow-team-fix-the-firesheep-style-cookie-theft
						$_SESSION['USUARI'] = serialize($user);
						$_SESSION['SISTEMA'] = serialize($sistema);

						if ($user->imposa_canvi_password)
							header('Location: CanviPassword.html');
						else {
							$SQL = "UPDATE USUARI SET data_ultim_login='".date('Y-m-d H:i:s')."', ip_ultim_login='".getUserIP()."' WHERE usuari_id=".$user->usuari_id;
							$conn->query($SQL);	
							header('Location: Escriptori.php');
						}
					}
					else 
					{
						PaginaHTMLMissatge("Error", "El password no és correcte.");
					}
				}
				else 
				{
					PaginaHTMLMissatge("Error", "Usuari inexistent o bloquejat.");
				}
			}
			catch (Exception $e) 
			{
				$Text = "[File: ".getFile().", line ".$e->getLine()."]: ".$e->getMessage();
				PaginaHTMLMissatge("Error", $Text);
			}
		}
		else 
		{
			PaginaHTMLMissatge("Error", "L'usuari només pot contenir lletres o números.");
		} 
	}
	else 
	{
		// NO isset(POST['username']) ni isset(POST['password'])
		PaginaHTMLMissatge("Error", "Accés incorrecte a aquesta pàgina.");
	} 
}
else 
{
	PaginaHTMLMissatge("Error", "Accés incorrecte a aquesta pàgina.");
} 

?>

