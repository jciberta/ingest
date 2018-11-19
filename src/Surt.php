<?php

/** 
 * Surt.php
 *
 * Surt de la sessió i torna a mostrar la pàgina principal.
 */
 
session_start();
session_destroy();
header('Location: index.html');

?>