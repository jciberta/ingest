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
	const Host       = 'localhost';
	const BaseDades  = 'InGest';
	const Usuari     = 'root';
	const Password   = 'root';
	const Debug      = True; // Si esta activat mostrara més informació.
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

?>
