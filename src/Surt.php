<?php

/** 
 * Surt.php
 *
 * Surt de la sessió i torna a mostrar la pàgina principal.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */
 
session_start();
session_destroy();
header('Location: index.html');

?>
