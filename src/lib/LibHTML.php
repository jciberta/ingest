<?php

/** 
 * LibHTML.php
 *
 * Llibreria d'HTML.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

 /**
 * CreaIniciHTML
 *
 * Crea l'inici del document HTML.
 *
 * @param string $Titol Títol de la pagina.
 */
function CreaIniciHTML($Titol)
{
	CreaIniciHTML_BootstrapStarterTemplate($Titol);
/*	echo "<HTML>";
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
	echo '<H1>'.utf8_encode($Titol).'</H1>';*/
}

/**
 * CreaFinalHTML
 *
 * Crea el final del document HTML.
 */
function CreaFinalHTML()
{
	CreaFinalHTML_BootstrapStarterTemplate();
}

/**
 * CreaIniciHTML_BootstrapStarterTemplate
 *
 * Crea l'inici del document HTML amb el template "Bootstrap starter template".
 * https://getbootstrap.com/docs/4.0/examples/starter-template/
 *
 * @param string $Titol Títol de la pàgina.
 */
function CreaIniciHTML_BootstrapStarterTemplate($Titol)
{
	echo "<HTML>";
	echo "<HEAD>";
	echo "	<META charset=UTF8>";
	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">';
	echo '	<script src="vendor/jquery.min.js"></script>';
	echo '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>';
	echo '	<script src="vendor/popper.min.js"></script>';
//	echo '	<script src="vendor/bootstrap/css/starter-template.css"></script>';
	echo "</HEAD>";
	echo '<BODY>';
	echo '    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">';
	echo '      <span class="navbar-brand">inGest</span>';
	echo '      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">';
	echo '        <span class="navbar-toggler-icon"></span>';
	echo '      </button>';

	echo '      <div class="collapse navbar-collapse" id="navbarsExampleDefault">';
	echo '        <ul class="navbar-nav mr-auto">';
	echo '          <li class="nav-item active">';
	echo '            <a class="nav-link" href="Menu.php">Inici <span class="sr-only">(current)</span></a>';
	echo '          </li>';
//	echo '          <li class="nav-item"><a class="nav-link" href="#">Link</a></li>';
//	echo '          <li class="nav-item"><a class="nav-link disabled" href="#">Disabled</a></li>';
	echo '          <li class="nav-item dropdown">';
	echo '            <a class="nav-link dropdown-toggle" href="#" id="ddAlumnes" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Alumnes</a>';
	echo '            <div class="dropdown-menu" aria-labelledby="ddAlumnes">';
	echo '              <a class="dropdown-item" href="Alumnes.php">Alumnes</a>';
	echo '              <a class="dropdown-item" href="FormUsuari.php">Alumnes (formulari genèric)</a>';
	echo '            </div>';
	echo '          </li>';
	echo '          <li class="nav-item dropdown">';
	echo '            <a class="nav-link dropdown-toggle" href="#" id="ddProfessors" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Professors</a>';
	echo '            <div class="dropdown-menu" aria-labelledby="ddProfessors">';
	echo '              <a class="dropdown-item" href="Professors.php">Professors</a>';
	echo '              <a class="dropdown-item" href="AssignaUFs.php?accio=ProfessorsUF">Professors per UF</a>';
	echo '            </div>';
	echo '          </li>';
	echo '          <li class="nav-item dropdown">';
	echo '            <a class="nav-link dropdown-toggle" href="#" id="ddFP" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">FP</a>';
	echo '            <div class="dropdown-menu" aria-labelledby="ddFP">';
	echo '              <a class="dropdown-item" href="FPRecerca.php?accio=Families">Famílies</a>';
	echo '              <a class="dropdown-item" href="FPRecerca.php?accio=CiclesFormatius">Cicles formatius</a>';
	echo '              <a class="dropdown-item" href="FPRecerca.php?accio=ModulsProfessionals">Mòduls professionals</a>';
	echo '              <a class="dropdown-item" href="FPRecerca.php?accio=UnitatsFormatives">Unitats formatives</a>';
	echo '              <a class="dropdown-item" href="Cicles.php">Cicles formatius</a>';
	echo '            </div>';
	echo '          </li>';
	echo '        </ul>';

	echo '        <form class="form-inline my-2 my-lg-0" action="Surt.php">';
//	echo '          <input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">';
//	echo '          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>';
	echo '          <button class="btn btn-primary my-2 my-sm-0" type="submit">Surt</button>';
	echo '        </form>';
//	echo '		<ul class="nav nav-pills float-right">';
//	echo '			<li class="nav-item"><a class="nav-link" href="Surt.php">Surt</a></li>';
//	echo '		</ul>';

	echo '      </div>';
	echo '    </nav>';
	echo '      <div class="starter-template">';
	echo '<BR><BR>'; // Pedaç!
//	echo '<H1>'.utf8_encode($Titol).'</H1>';
	echo '<H1>'.$Titol.'</H1>';
}

/**
 * CreaFinalHTML_BootstrapStarterTemplate
 *
 * Crea el final del document HTML amb el template "Bootstrap starter template".
 * https://getbootstrap.com/docs/4.0/examples/starter-template/
 */
function CreaFinalHTML_BootstrapStarterTemplate()
{
	echo "</div>";
	echo "<DIV id=debug></DIV>";
	echo "<DIV id=debug2></DIV>";
	echo '</BODY>';
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
 * Crea una pagina HTML amb un missatge i un link a la pagina principal.
 *
 * @param string $Titol Títol de la pagina.
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
	echo utf8_encode('<p>Retorna a la <a href="index.html" class="alert-link">pagina principal</a>.</p>');
	echo '</div>';	
	echo '</div>';	
	echo '</BODY>';	
}
 
 ?>
