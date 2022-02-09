<?php  

/** 
 * Config.php
 *
 * Configuració general de l'aplicació.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

class Config {
	const Versio         = '1.4';
	const Host           = 'localhost';
	const BaseDades      = 'InGest';
	const Usuari         = 'root';
	const Password       = 'root';
	const Debug          = True; // Si està activat mostrara més informació.
	const Demo           = False;
	const Manteniment    = False; 
	const Secret         = '736563726574'; // Clau per a les funcions d'encriptació (hexadecimal).
	const EncriptaURL    = True; // Si està actiu només passarà un paràmetre anomenat clau (que contindrà els paràmetres originals encriptats).
	const Correu         = 'no.contesteu@inspalamos.cat';
	const PasswordCorreu = Config::Password;
	const UsaDataTables  = True;
	const AutenticacioGoogle = False;
}

// Credencials Google
define('GOOGLE_CLIENT_ID', '***');
define('GOOGLE_CLIENT_SECRET', '***');
define('GOOGLE_REDIRECT_URI', 'http://'.$_SERVER['HTTP_HOST'].'/AutenticacioOath2Google.php');

unset($CFG);
global $CFG;

$CFG = new stdClass();

$CFG->Host           = Config::Host;
$CFG->BaseDades      = Config::BaseDades;
$CFG->Usuari         = Config::Usuari;
$CFG->Password       = Config::Password;
$CFG->Debug          = Config::Debug;
$CFG->Manteniment    = Config::Manteniment;
$CFG->Secret         = hex2bin(Config::Secret); // Clau per a les funcions d'encriptació.
$CFG->Correu         = Config::Correu;
$CFG->PasswordCorreu = Config::PasswordCorreu;

// Definició de l'arrel de l'aplicació.
if (defined('STDIN')) {
	// Execució de PHP via CLI.
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { 
		define('ROOT', 'D:\CASA\Xiber\ingest\src');
		define('INGEST_DATA', 'D:\CASA\Xiber\ingest-data');
		define('UNITAT_XAMPP', 'D');
		//define('ROOT', 'D:/jciberta/ingest/src');
		//define('INGEST_DATA', 'D:/jciberta/ingest-data');
	}
	else if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
		define('ROOT', '/var/www/html/ingest');
		define('INGEST_DATA', '/var/www/ingest-data');
	}
}
else {
	// Execució de PHP via web.
	define('ROOT', __DIR__);
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { 
		// Windows
		define('INGEST_DATA', 'D:/CASA/Xiber/ingest-data');
		define('UNITAT_XAMPP', 'D');
		//define('INGEST_DATA', 'D:/jciberta/ingest-data');
		define('FONT_FILENAME_ARIAL', 'C:\Windows\Fonts\arial.ttf');
	}
	else if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
		// Linux
		define('INGEST_DATA', '/var/www/ingest-data');
		// Cals instal·lar les fonts
		// sudo apt-get install msttcorefonts
		define('FONT_FILENAME_ARIAL', '/usr/share/fonts/truetype/msttcorefonts/Arial.ttf');
	}	
}

?>