<?php

/** 
 * RecuperaPassword.php
 *
 * Recuperació de la contrasenya del pare, mare o tutor.
 * Busca un dels fills i demana el DNI i la data de naixement.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibPassword.php');

if (!empty($_POST)) 
{
	//print_r($_POST);
	if (isset($_POST['dni']))
	{
		try
		{
			$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
			if ($conn->connect_error)
				die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
			
			$DNI = $_POST['dni'];
			$Mode = $_POST['Mode'];
			$DNI = filter_var($DNI, FILTER_SANITIZE_STRING);

			if ($Mode == 'Tutor') {
				$rp = new RecuperaPasswordTutor($conn);
				$SQL = $rp->CreaSQL($DNI);
				$ResultSet = $conn->query($SQL);
				if ($ResultSet->num_rows > 0) {
					$row = $ResultSet->fetch_object();
					$rp->EscriuCapcalera();
					$rp->EscriuFormulari(utf8_encode($row->nom), $DNI);
					$rp->EscriuMissatges();
					$rp->EscriuPeu(False);
				}
				else 
					PaginaHTMLMissatge("Error", "Usuari incorrecte o no li és permès recuperar la contrasenya amb aquest mètode.");
			}
			else if ($Mode == 'Alumne') {
				$rp = new RecuperaPasswordAlumne($conn);
				$rp->EscriuCapcalera();
				$rp->EscriuFormulari($DNI);
				$rp->EscriuMissatges();
				$rp->EscriuPeu(False);
			}
			else
				die("Accés incorrecte a la pàgina.");
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

