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

if (Config::Manteniment)
	die("<h1>Disculpeu les molèsties. Pàgina web en manteniment.</h1>");

echo '<html>';
echo '<head>';
echo '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
echo '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">';
echo '	<script src="vendor/jquery.min.js"></script>';
echo '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>';
echo '</head>';
echo '<body>';
echo '<div class="container">';
echo '	<div class="header clearfix">';
echo '		<nav>';
echo '		<ul class="nav nav-pills float-right">';
echo '			<li class="nav-item"><a class="nav-link" href="http://inspalamos.cat" target="_blank">Web</a></li>';
echo '			<li class="nav-item"><a class="nav-link" href="https://inspalamos.ieduca.com" target="_blank">iEduca</a></li>';
echo '		</ul>';
echo '		</nav>';
echo '	</div>';
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
echo '	<footer class="footer">';
echo '	<p>Institut de Palamós</p>';
echo '	</footer>';
echo '</div>';
echo '</body> ';
echo '</html>';
?>