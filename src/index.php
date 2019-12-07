<?php

/** 
 * index.php
 *
 * Pàgina principal.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibHTML.php');

if (Config::Manteniment)
	die("<h1>Disculpeu les molèsties. Pàgina web en manteniment.</h1>");

$Portal = new Portal();

$Portal->EscriuCapcalera();
echo '	<div class="jumbotron">';
echo '		<div class="d-flex justify-content-center">';
echo '			<form action="Autenticacio.php" method="post">';
echo '				<h2 class="d-flex justify-content-center">inGest</h2>';
echo '				<div class="form-group">';
echo '					<label for="usuari">Usuari</label>';
echo '					<input type="text" class="form-control" name="usuari" required>';
echo '				</div>';
echo '				<div class="form-group">';
echo '					<label for="password">Password</label>';
echo '					<input type="password" class="form-control" name="password" required>';
echo '				</div>';
echo '				<button type="submit" class="btn btn-primary">Inicia sessió</button>';
echo '			</form>';
echo '		</div>';
echo '	</div>';
$Portal->EscriuPeu();

?>