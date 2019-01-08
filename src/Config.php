<?php  

/** 
 * Config.php
 *
 * Configuraci� general de l'aplicaci�.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

class Config {
	const Host       = 'localhost';
	const BaseDades  = 'InGest';
	const Usuari     = 'root';
	const Password   = 'root';
	const Debug      = True; // Si est� activat mostrara m�s informaci�.
	const Secret     = '736563726574'; // Clau per a les funcions d'encriptaci� (hexadecimal).
}

unset($CFG);
global $CFG;

$CFG = new stdClass();

$CFG->Host       = Config::Host;
$CFG->BaseDades  = Config::BaseDades;
$CFG->Usuari     = Config::Usuari;
$CFG->Password   = Config::Password;
$CFG->Debug      = Config::Debug;
$CFG->Secret     = hex2bin(Config::Secret); // Clau per a les funcions d'encriptaci�.

// Definici� de l'arrel de l'aplicaci�.
if (defined('STDIN')) 
	// Execuci� de PHP via CLI.
	define('ROOT', '/var/www/html/ingest/src');
else 
	// Execuci� de PHP via web.
	define('ROOT', __DIR__);
//	define('ROOT', 'http://localhost/ingest/src');

?>
