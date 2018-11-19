<?php

/** 
 * LibHTML.php
 *
 * Llibreria d'HTML.
 */

 /**
 * CreaIniciHTML
 *
 * Crea l'inici del document HTML.
 * Ús: 
 *
 * @param string $Titol Títol de la pàgina.
 */
function CreaIniciHTML($Titol)
{
	echo "<HTML>";
	echo "<HEAD>";
	echo "	<META charset=UTF8>";
	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">';
	echo '	<script src="vendor/jquery-3.3.1.min.js"></script>';
	echo '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>';
	echo "</HEAD>";
	echo '<BODY>';
	echo '	<div class="header clearfix">';
	echo '		<nav>';
	echo '		<ul class="nav nav-pills float-right">';
	echo '			<li class="nav-item"><a class="nav-link" href="Surt.php">Surt</a></li>';
	echo '		</ul>';
	echo '		</nav>';
	echo '	</div>';
	echo '<H1>'.utf8_encode($Titol).'</H1>';
}
 
/**
 * CreaDesplegable
 *
 * Crea un desplegable (combobox) HTML.
 * Ús: CreaDesplegable(array(1, 2, 3, 4), array("foo", "bar", "hello", "world"));
 *
 * @param string $Titol Títol del desplegable.
 * @param string $Nom Nom del desplegable.
 * @param array $Codi Codis de la llista.
 * @param array $Valor Valors de la llista.
 * @return void
 */
function CreaDesplegable($Titol, $Nom, $Codi, $Valor)
{
	echo $Titol.':';
	echo '<select name="'.$Nom.'">';
	
//  <option value="" selected disabled hidden>Escull...</option>	
	
	$LongitudCodi = count($Codi); 
	for ($i = 0; $i < $LongitudCodi; $i++)
	{
    echo '<option value="'.$Codi[$i].'">'.utf8_encode($Valor[$i]).'</option>';
	} 	
	echo "</select>";
	echo '<BR>';
}

/**
 * PaginaHTMLMissatge
 *
 * Crea una pàgina HTML amb un missatge i un link a la pàgina principal.
 *
 * @param string $Titol Títol de la pàgina.
 * @param string $Missatge Missatge a mostrar.
 * @return void
 */
function PaginaHTMLMissatge($Titol, $Missatge)
{
	echo "<HTML>";
	echo "<HEAD>";
	echo "	<META charset=UTF8>";
	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">';
	echo '	<script src="vendor/jquery-3.3.1.min.js"></script>';
	echo '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>';
	echo "</HEAD>";
	echo '<BODY>';
	echo '<div class="container">';
	echo '<div class="alert alert-success" role="alert">';
	echo utf8_encode('<h4 class="alert-heading">'.$Titol.'</h4>');
	echo utf8_encode('<p>'.$Missatge.'</p>');
	echo '<hr>';
	echo utf8_encode('<p>Retorna a la <a href="index.html" class="alert-link">pàgina principal</a>.</p>');
	echo '</div>';	
	echo '</div>';	
	echo '</BODY>';	
}
 
 ?>
 
 