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

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once('Config.php');
require_once('vendor/PHPMailer/PHPMailer.php');
require_once('vendor/PHPMailer/Exception.php');
require_once('vendor/PHPMailer/SMTP.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibPassword.php');

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!empty($_POST)) 
{
	//print_r($_POST);
	if (isset($_POST['dni']) || isset($_POST['email']))
	{
		try
		{
			$Mode = $_POST['Mode'];

			if ($Mode == 'Tutor') {
				$DNI = $_POST['dni'];
				$DNI = filter_var($DNI, FILTER_SANITIZE_STRING);
				$rp = new RecuperaPasswordTutor($conn);
				$SQL = $rp->CreaSQL($DNI);
				$ResultSet = $conn->query($SQL);
				if ($ResultSet->num_rows > 0) {
					$row = $ResultSet->fetch_object();
					$rp->EscriuCapcalera();
					$rp->EscriuFormulari(utf8_encodeX($row->nom), $DNI);
					$rp->EscriuMissatges();
					$rp->EscriuPeu(False);
				}
				else 
					PaginaHTMLMissatge("Error", "Usuari incorrecte o no li és permès recuperar la contrasenya amb aquest mètode.");
			}
			else if ($Mode == 'Alumne') {
				$DNI = $_POST['dni'];
				$DNI = filter_var($DNI, FILTER_SANITIZE_STRING);
				$rp = new RecuperaPasswordAlumne($conn);
				$rp->EscriuCapcalera();
				$rp->EscriuFormulari($DNI);
				$rp->EscriuMissatges();
				$rp->EscriuPeu(False);
			}
			else if ($Mode == 'Professor') {
//print_r($_POST);				
				$email = $_POST['email'];
				$email = filter_var($email, FILTER_SANITIZE_EMAIL);
//print_r($email);				
				$email = filter_var($email, FILTER_VALIDATE_EMAIL);
				if ($email) {
					$rp = new RecuperaPasswordProfessor($conn);
					$SQL = $rp->CreaSQL($email);
//print_r($SQL);					
					$ResultSet = $conn->query($SQL);
					if ($ResultSet->num_rows > 0) {

//						$row = $ResultSet->fetch_object();
						$rp->EscriuCapcalera();
						
						// Data d'expiració: 1 dia
						// Extret de https://www.allphptricks.com/forgot-password-recovery-reset-using-php-and-mysql/					
						$expFormat = mktime(date("H"), date("i"), date("s"), date("m"), date("d")+1, date("Y"));
						$expDate = date("Y-m-d H:i:s",$expFormat);
						$key = md5($email);
						$addKey = substr(md5(uniqid(rand(),1)),3,10);
						$key = $key . $addKey;

						$SQL = " INSERT INTO PASSWORD_RESET_TEMP (email, clau, data_expiracio) ". 
							" VALUES ('$email', '$key', '$expDate') ";
//print_r($SQL);
						if (!$conn->query($SQL))
							throw new Exception($conn->error.'. SQL: '.$SQL);

						$output ="<p>Cliqueu a l'enllaç següent per reiniciar la vostra contrasenya.</p>";

						$link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
						$link .= '?key='.$key.'&email='.$email.'&action=reset';

						$output.="<p><a href='$link' target='_blank'>$link</a></p>";
						//$output.='<p><a href="https://www.allphptricks.com/forgot-password/reset-password.php?key='.$key.'&email='.$email.'&action=reset" target="_blank">https://www.allphptricks.com/forgot-password/reset-password.php?key='.$key.'&email='.$email.'&action=reset</a></p>';		
						//$output.='<br>';
						$output.="<p>Assegureu-vos que s'ha copiat la totalitat de l'enllaç en el vostre navegador.
						Per qüestions de seguretat, la diponibilitat d'aquest enllaç és de 24 hores.</p>";
						$output.='<p>Si no heu demanat de reiniciar la contrasenya, ignoreu aquest correu. No es realitzarà cap acció.</p>';   	
						$output.='<p>Atentament,</p>';
						$output.='<p>InGest</p>';
						$body = $output; 
						$subject = "Reinici de la contrasenya d'InGest";
						$email_to = $email;
						$fromserver = $CFG->Correu; 
				
						// https://blog.mailtrap.io/phpmailer/#How_to_use_PHPMailer_with_Gmail				
						$mail = new PHPMailer();
						$mail->IsSMTP();
						//$mail->SMTPDebug = 2;
						$mail->SMTPAuth = true;

						$mail->SMTPSecure = 'ssl';
						$mail->Port = 465;
						//$mail->SMTPSecure = 'tls';
						//$mail->Port = 587;
						$mail->Host = 'smtp.gmail.com';
						$mail->Username = $CFG->Correu;
						$mail->Password = $CFG->PasswordCorreu;

						$mail->IsHTML(true);
						$mail->From = $CFG->Correu;
						$mail->FromName = "No contesteu";
						$mail->Sender = $fromserver; 
						$mail->Subject = $subject;
						$mail->Body = $body;
						$mail->AddAddress($email_to);
//echo "<pre>";
//print_r($mail);
//echo "</pre>";
						if (!$mail->Send()) {
							echo "Mailer Error: " . $mail->ErrorInfo;
						} else {
							echo "<br /><div>
							<p>Se us  ha enviat un correu electrònic amb les instruccions per reiniciar la contrasenya.</p>
							</div><br /><br />";
						}
						$rp->EscriuPeu(False);
					}
					else 
						PaginaHTMLMissatge("Error", "No hi ha cap usuari amb aquest correu.");
				}
				else 
					PaginaHTMLMissatge("Error", "Format del correu incorrecte.");
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
else if (!empty($_GET)) 
{
	// Reinici de la contrasenya
//print_r($_GET);
	if (isset($_GET["key"]) && isset($_GET["email"])
	&& isset($_GET["action"]) && ($_GET["action"]=="reset")
	&& !isset($_POST["action"])) {
		$key = $_GET["key"];
		$email = $_GET["email"];
		$curDate = date("Y-m-d H:i:s");
		$SQL = "SELECT * FROM PASSWORD_RESET_TEMP WHERE clau='".$key."' and email='".$email."'";
//echo "SQL: $SQL<br>";

		$ResultSet = $conn->query($SQL);
//print_r($ResultSet);
		if ($ResultSet->num_rows < 1) {
			$error .= "L'enllaç és invàlid o ha caducat. Assegureu-vos que heu copiat la URL del correu electrònic correctament, 
			o si ja heu utilitzat l'enllaç, aquest ha estat desactivat.";
			PaginaHTMLMissatge("Enllaç invàlid", $error);
		}
		else {
			$row = $ResultSet->fetch_assoc();
			$expDate = $row['data_expiracio'];
//echo "expDate: $expDate<br>";
//echo "curDate: $curDate<br>";
			if ($expDate >= $curDate) {
				$cp = new CanviPassword();
				$cp->EscriuCapcalera();
				$cp->EscriuFormulari(False, $email, $key);
				$cp->EscriuPeu(False);
			} 
			else {
				PaginaHTMLMissatge("Enllaç caducat", "L'enllaç ha caducat. Esteu intentant usar un enllaç que només és vàlid 24 hores.");
			}
		}
	} 
}
else 
	PaginaHTMLMissatge("Error", "Accés incorrecte a aquesta pàgina.");

?>

