<?php  

/** 
 * Config.php
 *
 * Configuració general de l'aplicació.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->Host       = 'localhost';
$CFG->BaseDades  = 'InGest';
$CFG->Usuari     = 'root';
$CFG->Password   = 'root';

?>
