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
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibRegistre.php');

// En el cas que canviem la contrasenya a través d'un email, l'usuari no pot estar identificat
if (!empty($_POST) && isset($_POST['contrasenya_actual'])) {
	session_start();
	if (!isset($_SESSION['usuari_id'])) 
		header("Location: Surt.php");
	$Usuari = unserialize($_SESSION['USUARI']);
}

if (!empty($_POST)) 
{
	//print_r($_POST);
	if ((isset($_POST['contrasenya_actual']) || isset($_POST['clau'])) && isset($_POST['contrasenya1']) && isset($_POST['contrasenya2'])) 
	{
		try
		{
			$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
			if ($conn->connect_error) 
				die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
			
			if (isset($_POST['contrasenya_actual'])) {
				if (password_verify($_POST['contrasenya_actual'], $Usuari->password)) {
					// Canvi de la contrasenya a través de la contrasenya actual
					if (($_POST['contrasenya1'] == $_POST['contrasenya2']) && ($_POST['contrasenya1'] != '')) {
						$errors = [];
						//print_r($_POST['contrase nya1']);
						if (ComprovaFortalesaPassword($_POST['contrasenya1'], $errors)) {
							$SQL = "UPDATE USUARI SET password = ?, imposa_canvi_password = 0 WHERE usuari_id = ?;";
							$stmt = $conn->prepare($SQL);
							$Password = password_hash($_POST['contrasenya1'], PASSWORD_DEFAULT); // Evita "Notice: Only variables should be passed by reference"
							$stmt->bind_param("si", $Password, $Usuari->usuari_id);
//							$stmt->bind_param("si", password_hash($_POST['contrasenya1'], PASSWORD_DEFAULT), $Usuari->usuari_id);
							$stmt->execute();
							$stmt->close();
							//print_r($SQL);
							PaginaHTMLMissatge("Informació", "La contrasenya s'ha desat correctament.");
							$log = new Registre($conn, $Usuari);
							$log->Escriu(Registre::AUTH, 'Canvi de contrasenya');
						}
						else {
							PaginaHTMLMissatge("Error", "La contrasenya no és prou segura. Ha de tenir una longitud mínima de 8 caràcters, i ha de contenir números i lletres.");
						}
					}
					else {
						PaginaHTMLMissatge("Error", "Les noves contrasenyes no coincideixen o alguna està en blanc.");
					}
				}
				else {
					PaginaHTMLMissatge("Error", "Contrasenya actual incorrecta.");
				}
			} 
			else {
				// Canvi de la contrasenya a través d'un email
				$key = mysqli_real_escape_string($conn, $_POST['clau']);
				$email = mysqli_real_escape_string($conn, $_POST['email']);
				
				$SQL = "SELECT * FROM PASSWORD_RESET_TEMP WHERE clau = ? and email = ?;";
				$stmt = $conn->prepare($SQL);
				$stmt->bind_param("ss", $key, $email);
				$stmt->execute();
//echo "SQL: $SQL<br>";

				$ResultSet = $stmt->get_result();
//print_r($ResultSet);
				if ($ResultSet->num_rows < 1) {
					PaginaHTMLMissatge("Enllaç invàlid", "L'enllaç és invàlid o ha caducat.");
				}
				else {
					$row = $ResultSet->fetch_assoc();
					$expDate = $row['data_expiracio'];
//echo "expDate: $expDate<br>";
//echo "curDate: $curDate<br>";
					if ($expDate >= $curDate) {
						if (($_POST['contrasenya1'] == $_POST['contrasenya2']) && ($_POST['contrasenya1'] != '')) {
							$errors = [];
							if (ComprovaFortalesaPassword($_POST['contrasenya1'], $errors)) {
								// Compte! Un email diferent per a cada usuari
								$SQL = "UPDATE USUARI SET password = ?, imposa_canvi_password = 0 WHERE email = ?;";
								$stmt = $conn->prepare($SQL);
								$stmt->bind_param("ss", password_hash($_POST['contrasenya1'], PASSWORD_DEFAULT), $email);
								$stmt->execute();
								
								$SQL = "DELETE FROM PASSWORD_RESET_TEMP WHERE email = ?;";
								$stmt = $conn->prepare($SQL);
								$stmt->bind_param("s", $email);
								$stmt->execute();
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
					else {
						PaginaHTMLMissatge("Enllaç caducat", "L'enllaç ha caducat. Esteu intentant usar un enllaç que només és vàlid 24 hores.");
					}
				}
			}
		}
		catch (Exception $e) 
		{
			$Text = "[File: ".$e->getFile().", line ".$e->getLine()."]: ".$e->getMessage();
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

