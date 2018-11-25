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

session_start();

if (!empty($_POST)) 
{
	//print_r($_POST);
	if (isset($_POST['usuari']) && isset($_POST['password'])) 
	{
		try
		{
			$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
			if ($conn->connect_error) {
				die("ERROR: Unable to connect: " . $conn->connect_error);
			} 

			$SQL = "SELECT * FROM USUARI WHERE username='". $_POST['usuari']."'";
			$ResultSet = $conn->query($SQL);
			if ($ResultSet->num_rows > 0) {
				$user = $ResultSet->fetch_object();
				//echo "Password: ". $user->password;
				if (password_verify($_POST['password'], $user->password)) 
				{
					$_SESSION['usuari_id'] = $user->usuari_id;
					header('Location: Menu.php');
				}
				else 
				{
					PaginaHTMLMissatge("Error", "El password no és correcte.");
				}
			}
			else 
			{
				PaginaHTMLMissatge("Error", "Usuari inexistent.");
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
		// NO isset(POST['username']) ni isset(POST['password'])
		PaginaHTMLMissatge("Error", "Accés incorrecte a aquesta pàgina.");
	} 
}
else 
{
	PaginaHTMLMissatge("Error", "Accés incorrecte a aquesta pàgina.");
} 

?>





?>

