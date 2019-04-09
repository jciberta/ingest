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
	const Versio     = '0.2';
	const Host       = 'localhost';
	const BaseDades  = 'InGest';
	const Usuari     = 'root';
	const Password   = 'root';
	const Debug      = True; // Si està activat mostrara més informació.
	const Secret     = '736563726574'; // Clau per a les funcions d'encriptació (hexadecimal).
}

unset($CFG);
global $CFG;

$CFG = new stdClass();

$CFG->Host       = Config::Host;
$CFG->BaseDades  = Config::BaseDades;
$CFG->Usuari     = Config::Usuari;
$CFG->Password   = Config::Password;
$CFG->Debug      = Config::Debug;
$CFG->Secret     = hex2bin(Config::Secret); // Clau per a les funcions d'encriptació.

// Definició de l'arrel de l'aplicació.
if (defined('STDIN')) {
	// Execució de PHP via CLI.
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { 
		define('ROOT', 'D:\CASA\Xiber\ingest\src');
	}
	else if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
		define('ROOT', '/var/www/html/ingest/src');
	}
}
else 
	// Execució de PHP via web.
	define('ROOT', __DIR__);
//	define('ROOT', 'http://localhost/ingest/src');

?>