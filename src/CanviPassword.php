<?php

/** 
 * CanviPassword.php
 *
 * Canvi de contrasenya de l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once('lib/LibHTML.php');
require_once('lib/LibDB.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

if (!empty($_POST)) 
{
	//print_r($_POST);
	if (isset($_POST['contrasenya_actual']) && isset($_POST['contrasenya1']) && isset($_POST['contrasenya2'])) 
	{
		try
		{
			$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
			if ($conn->connect_error) {
				die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
			} 
			if (password_verify($_POST['contrasenya_actual'], $Usuari->password)) {
				if (($_POST['contrasenya1'] == $_POST['contrasenya2']) && ($_POST['contrasenya1'] != '')) {
					$errors = [];
//print_r($_POST['contrasenya1']);
					if (ComprovaFortalesaPassword($_POST['contrasenya1'], $errors)) {
						$SQL = "UPDATE USUARI SET password='".password_hash($_POST['contrasenya1'], PASSWORD_DEFAULT)."', imposa_canvi_password=0 WHERE usuari_id=". $Usuari->usuari_id;
//print_r($SQL);
						$conn->query($SQL);	
						PaginaHTMLMissatge("Informació", "La contrasenya s'ha desat correctament.");
					}
					else {
						PaginaHTMLMissatge("Error", "La contrasenya no és prou segura. Ha de tenir una longitud mínima de 8 caràcters, i ha de contenir números i lletres.");
					}
				}
				else {
					PaginaHTMLMissatge("Error", "Les noves contrasenyes no coincideixen o alguna està en blanc.");
				}
			}
			else 
			{
				PaginaHTMLMissatge("Error", "Contrasenya actual incorrecta.");
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
		// NO isset(POST[...
		PaginaHTMLMissatge("Error", "Accés incorrecte a aquesta pàgina.");
	} 
}
else 
{
	PaginaHTMLMissatge("Error", "Accés incorrecte a aquesta pàgina.");
} 

?>

