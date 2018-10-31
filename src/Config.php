<?php  

/** 
 * Config.php
 *
 * Configuració general de l'aplicació.
 */

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->Host       = 'localhost';
$CFG->BaseDades  = 'InGest';
$CFG->Usuari     = 'root';
$CFG->Password   = 'root';

?>