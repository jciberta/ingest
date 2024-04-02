<?php

/**
 * EnviaCorreu.php
 *
 * Utilitat per enviar un correu electrònic.
 * Ús:
 *  php EnviaCorreu.php from to subject body attachment
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

//define('PHPMAILER_DIR', 'D:\CASA\Xiber\ingest\src\vendor\PHPMailer');
define('PHPMAILER_DIR', '/home/inspalamos/www/ingest/ingest/src/vendor/PHPMailer');

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once(PHPMAILER_DIR.'/PHPMailer.php');
require_once(PHPMAILER_DIR.'/Exception.php');
require_once(PHPMAILER_DIR.'/SMTP.php');

//var_dump($argv);

if (count($argv) != 6)
	die('Ús: php EnviaCorreu.php from to subject body attachment');

$from = $argv[1];
$to = $argv[2];
$subject = $argv[3];
$body = $argv[4];
$attachment = $argv[5];

$mail = new PHPMailer();
$mail->IsSMTP();
//$mail->SMTPDebug = 2;
$mail->SMTPAuth = true;

// Amb relay
$mail->SMTPSecure = 'ssl';
$mail->Port = 465;
//$mail->SMTPSecure = 'tls';
//$mail->Port = 587;
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'no.contesteu@inspalamos.cat';
$mail->Password = '****';

$mail->IsHTML(true);
$mail->From = $from;
$mail->FromName = "No contesteu";
$mail->Sender = $mail->Username;
$mail->Subject = $subject;
$mail->Body = $body;
$mail->AddAddress($to);
$mail->AddAttachment($attachment, $attachment);

if (!$mail->Send()) {
	echo "Mailer Error: " . $mail->ErrorInfo;
} else {
	echo "Correu enviat correctament.";
}

?>
