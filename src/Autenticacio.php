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
require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibInet.php');
require_once(ROOT.'/lib/LibClasses.php');
require_once(ROOT.'/lib/LibRegistre.php');

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

				$SQL = "SELECT * FROM USUARI WHERE username = ? AND NOT usuari_bloquejat=1;";
				$stmt = $conn->prepare($SQL);
				$stmt->bind_param("s", $_POST['usuari']);
				$stmt->execute();
				$ResultSet = $stmt->get_result();
				if ($ResultSet->num_rows > 0) {
					$user = $ResultSet->fetch_object();
					$log = new Registre($conn, $user);
					if (password_verify($_POST['password'], $user->password)) 
					{
						// Carreguem la configuració del sistema
						$SQL = "SELECT * FROM SISTEMA;";
						$ResultSet = $conn->query($SQL);
						if ($ResultSet->num_rows == 0) 
							die("El sistema no ha estat configurat.");
						$sistema = $ResultSet->fetch_object();

						// Carreguem els dies festius
						$SQL = "SELECT * FROM FESTIU ORDER BY data;";
						$ResultSet = $conn->query($SQL);
						$festiu = [];
						if ($ResultSet->num_rows > 0) {
							while($row = $ResultSet->fetch_object()) {
								array_push($festiu, MySQLAData($row->data));
							}
						}

						$_SESSION['usuari_id'] = $user->usuari_id;
						// ToDo: Seguretat a la sessió
						// https://stackoverflow.com/questions/1442177/storing-objects-in-php-session
						// https://stackoverflow.com/questions/12233406/preventing-session-hijacking
						// https://codebutler.com/2010/10/24/firesheep/
						$_SESSION['USUARI'] = serialize($user);
						$_SESSION['SISTEMA'] = serialize($sistema);
						$_SESSION['FESTIU'] = serialize($festiu);

						if ($user->imposa_canvi_password)
							header('Location: CanviPassword.html');
						else {
							$SQL = "UPDATE USUARI SET data_ultim_login = ?, ip_ultim_login = ? WHERE usuari_id = ?;";
							$stmt = $conn->prepare($SQL);
							$stmt->bind_param("ssi", date('Y-m-d H:i:s'), getUserIP(), $user->usuari_id);
							$stmt->execute();

							$log->Escriu(Registre::AUTH, 'Entrada al sistema');

							header('Location: Escriptori.php');
						}
					}
					else 
					{
						$log->Escriu(Registre::AUTH, 'Password incorrecte');
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
				$Text = "[File: ".$e->getFile().", line ".$e->getLine()."]: ".$e->getMessage();
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
