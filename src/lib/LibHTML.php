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
 * @param object $Usuari Usuari autenticat.
 * @param string $Titol Títol de la pàgina.
 * @param boolean $bMenu Indica si el menú ha d'haver menú a la capçalera o no.
 * @param boolean $bSaga Indica si usem els estil de SAGA.
 */
function CreaIniciHTML($Usuari, $Titol, $bMenu = True, $bSaga = False)
{
	CreaIniciHTML_BootstrapStarterTemplate($Usuari, $Titol, $bMenu, $bSaga);
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
 * @param object $Usuari Usuari autenticat.
 * @param string $Titol Títol de la pàgina.
 * @param boolean $bMenu Indica si el menú ha d'haver menú a la capalera o no.
 * @param boolean $bSaga Indica si usem els estil de SAGA.
 */
function CreaIniciHTML_BootstrapStarterTemplate($Usuari, $Titol, $bMenu = True, $bSaga = False)
{
	echo '<HTML>';
	echo '<HEAD>';
	echo '	<META charset=UTF8>';
	echo '	<TITLE>InGest</TITLE>';
	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
	echo '	<link rel="stylesheet" href="vendor/bootstrap-submenu/dist/css/bootstrap-submenu.min.css">';
//	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">';
	echo '	<link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">';
	echo '	<link rel="stylesheet" href="css/InGest.css?v1.0">';
	if ($bSaga)
		echo '	<link rel="stylesheet" href="css/saga.css">';
	echo '	<script src="vendor/jquery.min.js"></script>';
	echo '	<script src="vendor/popper.min.js"></script>';
	echo '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>';
	echo '	<script src="vendor/bootstrap-submenu/dist/js/bootstrap-submenu.min.js"></script>';
	echo '	<script src="vendor/bootstrap-submenu/bootstrap-submenu.fix.js"></script>';
	echo '	<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>';
	echo '	<script src="vendor/bootstrap-datepicker/locales/bootstrap-datepicker.ca.min.js" charset="UTF-8"></script>';
	echo '	<script src="vendor/bootbox.min.js"></script>';
	echo '	<script src="js/Util.js"></script>';
	echo '</HEAD>';
	echo '<BODY>';
	
	if ($bMenu) {
		echo '<nav class="navbar navbar-dark bg-dark navbar-expand-sm fixed-top">';
		if ($Usuari->es_admin) 
			echo '	<span class="navbar-brand">inGest '.Config::Versio.'</span>';
		else
			echo '	<span class="navbar-brand">inGest</span>';
		echo '	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar-collapse">';
		echo '		<span class="navbar-toggler-icon"></span>';
		echo '	</button>';
		echo '	<div class="collapse navbar-collapse">';
		echo '		<ul class="navbar-nav mr-auto">';
		echo '			<li class="nav-item active">';
		echo '				<a class="nav-link" href="Escriptori.php">Inici</a>';
		echo '			</li>';
			
		if (($Usuari->es_admin) || ($Usuari->es_direccio) || ($Usuari->es_cap_estudis)) {
			// Menú alumnes
			echo '          <li class="nav-item dropdown">';
			echo '            <a class="nav-link dropdown-toggle" href="#" id="ddAlumnes" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Alumnes</a>';
			echo '            <div class="dropdown-menu" aria-labelledby="ddAlumnes">';
			echo '              <a class="dropdown-item" href="UsuariRecerca.php?accio=Alumnes">Alumnes</a>';
			echo '              <a class="dropdown-item" href="UsuariRecerca.php?accio=AlumnesPares">Alumnes/pares</a>';
			echo '              <div class="dropdown-divider"></div>';
			echo '              <a class="dropdown-item" href="UsuariRecerca.php?accio=Matricules">Matrícules</a>';
			echo '              <a class="dropdown-item" href="FormMatricula.php">Matriculació alumnes</a>';
			echo '            </div>';
			echo '          </li>';

			// Menú Professors
			echo '          <li class="nav-item dropdown">';
			echo '            <a class="nav-link dropdown-toggle" href="#" id="ddProfessors" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Professors</a>';
			echo '            <div class="dropdown-menu" aria-labelledby="ddProfessors">';
			echo '              <a class="dropdown-item" href="UsuariRecerca.php?accio=Professors">Professors</a>';
			echo '              <a class="dropdown-item" href="AssignaUFs.php?accio=ProfessorsUF">Professors per UF</a>';
			echo '              <div class="dropdown-divider"></div>';
			echo '              <a class="dropdown-item" href="UsuariRecerca.php?accio=Tutors">Tutors</a>';
			//echo '              <a class="dropdown-item" href="Guardia.php">Guàrdies</a>';
			echo '            </div>';
			echo '          </li>';

			// Menú FP
			echo '			<li class="nav-item dropdown">';
			echo '				<a class="nav-link dropdown-toggle" href="#" id="ddFP" data-toggle="dropdown" data-submenu="" aria-haspopup="true" aria-expanded="false">FP</a>';
			echo '				<div class="dropdown-menu" aria-labelledby="ddFP">';
			echo '					<a class="dropdown-item" href="FPRecerca.php?accio=Families">Famílies</a>';
			echo '					<a class="dropdown-item" href="FPRecerca.php?accio=CiclesFormatius">Cicles formatius</a>';
			echo '					<a class="dropdown-item" href="FPRecerca.php?accio=ModulsProfessionals">Mòduls professionals</a>';
			echo '					<div class="dropdown dropright dropdown-submenu">';
			echo '						<button class="dropdown-item dropdown-toggle" type="button">Unitats formatives</button>';
			echo '						<div class="dropdown-menu">';
			echo '							<a class="dropdown-item" href="FPRecerca.php?accio=UnitatsFormativesCF">Unitats formatives/MP/CF</a>';
			echo '							<a class="dropdown-item" href="FPRecerca.php?accio=UnitatsFormativesDates">Unitats formatives/Dates</a>';
			echo '						</div>';
			echo '					<div class="dropdown-divider"></div>';
			echo '					<a class="dropdown-item" href="FormMatricula.php">Matriculació alumnes</a>';
			echo '					<div class="dropdown-divider"></div>';
			echo '					<a class="dropdown-item" href="Escriptori.php">Cursos</a>';
			echo '				</div>';
			echo '			</li>';

			// Menú Centre
			echo '			<li class="nav-item dropdown">';
			echo '				<a class="nav-link dropdown-toggle" href="#" id="ddCentre" data-toggle="dropdown" data-submenu="" aria-haspopup="true" aria-expanded="false">Centre</a>';
			echo '				<div class="dropdown-menu" aria-labelledby="ddCentre">';
			echo '              	<a class="dropdown-item" href="UsuariRecerca.php">Usuaris</a>';
			echo '					<div class="dropdown-divider"></div>';
			echo '              	<a class="dropdown-item" href="Recerca.php?accio=AnyAcademic">Any acadèmic</a>';
			echo '              	<a class="dropdown-item" href="Recerca.php?accio=Departament">Departaments</a>';
			echo '					<div class="dropdown-divider"></div>';
			echo '	                <a class="dropdown-item" href="ImportaUsuarisDialeg.php">Importa usuaris</a>';
			echo '	                <a class="dropdown-item" href="ImportaPasswordsDialeg.php">Importa passwords iEduca</a>';
			echo '	                <a class="dropdown-item" href="ImportaMatriculaDialeg.php">Importa matrícules</a>';
			echo '				</div>';
			echo '			</li>';
		}	
		echo '		</ul>';
	
		// Menú usuari
		$NomComplet = utf8_encode(trim($Usuari->nom.' '.$Usuari->cognom1.' '.$Usuari->cognom2));
		echo '		<ul class="navbar-nav">';
		echo '		  <li class="nav-item dropdown">';
//		echo '			<a class="nav-link dropdown-toggle" tabindex="0" data-toggle="dropdown" data-submenu="" aria-haspopup="true">'.$NomComplet.'</a>';
		echo '          <a class="nav-link dropdown-toggle" href="#" id="ddUsuari" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$NomComplet.'</a>';
		echo '			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="ddUsuari">';
		echo '				<a class="dropdown-item" href="CanviPassword.html">Canvia password</a>';
		echo '				<div class="dropdown-divider"></div>';
		if ($Usuari->es_cap_estudis) {
			echo '				<a class="dropdown-item" href="CanviaRol.php">Canvia a professor</a>';
			echo '				<div class="dropdown-divider"></div>';
		}
		if ($Usuari->es_admin) {
			echo '				<a class="dropdown-item" href="Administra.php">Administra</a>';
			echo '				<div class="dropdown-divider"></div>';
		}
		echo '				<a class="dropdown-item" href="Surt.php">Surt</a>';
		echo '			</div>';
		echo '		  </li>';
		echo '		</ul>';

		echo '	</div>';
		echo '</nav>';
		echo '<BR><BR>'; // Per donar espai al menú
	}
	echo '      <div class="starter-template" style="padding:20px">';
//	echo '<H1>'.utf8_encode($Titol).'</H1>';
	echo '<H1>'.$Titol.'</H1>';
}

function CreaIniciHTML_BootstrapStarterTemplate2($Usuari, $Titol, $bMenu = True)
{
	echo "<HTML>";
	echo "<HEAD>";
	echo "	<META charset=UTF8>";
	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
	echo '	<link rel="stylesheet" href="vendor/bootstrap-submenu/dist/css/bootstrap-submenu.min.css">';
	echo '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">';
	echo '	<link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">';
	echo '	<script src="vendor/jquery.min.js"></script>';
	echo '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>';
	echo '	<script src="vendor/bootstrap-submenu/dist/js/bootstrap-submenu.min.js"></script>';
//	echo '	<script src="vendor/bootstrap-submenu/bootstrap-submenu.fix.js"></script>';
	echo '	<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>';
	echo '	<script src="vendor/bootstrap-datepicker/locales/bootstrap-datepicker.ca.min.js" charset="UTF-8"></script>';
	echo '	<script src="vendor/popper.min.js"></script>';
	echo '	<script src="vendor/bootbox.min.js"></script>';
	echo "</HEAD>";
	echo '<BODY>';
	if ($bMenu) {
		// Enable Bootstrap-submenu via JavaScript
//		echo "<script>$('[data-submenu]').submenupicker();</script>";

		echo '    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">';
		echo '      <span class="navbar-brand">inGest</span>';
		echo '      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">';
		echo '        <span class="navbar-toggler-icon"></span>';
		echo '      </button>';

		echo '      <div class="collapse navbar-collapse" id="navbarsExampleDefault">';
		echo '        <ul class="navbar-nav mr-auto">';
		echo '          <li class="nav-item active">';
		echo '            <a class="nav-link" href="Escriptori.php">Inici <span class="sr-only">(current)</span></a>';
		echo '          </li>';
//		if (($Usuari->es_admin) || ($Usuari->es_direccio) || ($Usuari->es_cap_estudis) || ($Usuari->es_cap_departament) || ($Usuari->es_tutor) || ($Usuari->es_professor)) {
		if (($Usuari->es_admin) || ($Usuari->es_direccio) || ($Usuari->es_cap_estudis)) {
			echo '          <li class="nav-item dropdown">';
			echo '            <a class="nav-link dropdown-toggle" href="#" id="ddAlumnes" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Alumnes</a>';
			echo '            <div class="dropdown-menu" aria-labelledby="ddAlumnes">';
			echo '              <a class="dropdown-item" href="UsuariRecerca.php?accio=Alumnes">Alumnes</a>';
			echo '              <a class="dropdown-item" href="UsuariRecerca.php?accio=Pares">Pares</a>';
			echo '              <div class="dropdown-divider"></div>';
			echo '              <a class="dropdown-item" href="FormMatricula.php">Matriculació alumnes</a>';
			echo '            </div>';
			echo '          </li>';

			// Menú Professors
			echo '          <li class="nav-item dropdown">';
			echo '            <a class="nav-link dropdown-toggle" href="#" id="ddProfessors" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Professors</a>';
			echo '            <div class="dropdown-menu" aria-labelledby="ddProfessors">';
			echo '              <a class="dropdown-item" href="UsuariRecerca.php?accio=Professors">Professors</a>';
			echo '              <a class="dropdown-item" href="AssignaUFs.php?accio=ProfessorsUF">Professors per UF</a>';
//			echo '              <div class="dropdown-divider"></div>';
//			echo '              <a class="dropdown-item" href="Guardia.php">Guàrdies</a>';
//			echo '              <a class="dropdown-item" href="Guardia.php?Dia=1">Guàrdies dilluns</a>';
//			echo '              <a class="dropdown-item" href="Guardia.php?Dia=2">Guàrdies dimarts</a>';
//			echo '              <a class="dropdown-item" href="Guardia.php?Dia=3">Guàrdies dimecres</a>';
//			echo '              <a class="dropdown-item" href="Guardia.php?Dia=4">Guàrdies dijous</a>';
//			echo '              <a class="dropdown-item" href="Guardia.php?Dia=5">Guàrdies divendres</a>';
			echo '            </div>';
			echo '          </li>';
			
			// Menú FP
			echo '          <li class="nav-item dropdown">';
			echo '            <a class="nav-link dropdown-toggle" href="#" id="ddFP" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">FP</a>';
//			echo '            <a class="nav-link dropdown-toggle" tabindex="0" data-toggle="dropdown" data-submenu>FP</a>';
			echo '            <div class="dropdown-menu" aria-labelledby="ddFP">';
			echo '              <a class="dropdown-item" href="FPRecerca.php?accio=Families">Famílies</a>';
			echo '              <a class="dropdown-item" href="FPRecerca.php?accio=CiclesFormatius">Cicles formatius</a>';
			echo '              <a class="dropdown-item" href="FPRecerca.php?accio=ModulsProfessionals">Mòduls professionals</a>';
			echo '              <a class="dropdown-item" href="FPRecerca.php?accio=UnitatsFormativesCF">Unitats formatives/MP/CF</a>';
			echo '              <a class="dropdown-item" href="FPRecerca.php?accio=UnitatsFormativesDates">Unitats formatives/Dates</a>';
			echo '              <div class="dropdown-divider"></div>';
			echo '              <a class="dropdown-item" href="FormMatricula.php">Matriculació alumnes</a>';
			echo '              <div class="dropdown-divider"></div>';
			echo '              <a class="dropdown-item" href="Escriptori.php">Cursos</a>';
			echo '              <div class="dropdown-divider"></div>';
			

//echo ' <li class="nav-item dropdown">';
//echo '        <a class="nav-link dropdown-toggle" tabindex="0" data-toggle="dropdown" data-submenu>';
//echo '          Dropdown';
//echo '        </a>';
			
//echo '      <div class="dropdown-menu">';
echo '        <div class="dropdown dropright dropdown-submenu">';
echo '          <button class="dropdown-item dropdown-toggle" type="button" data-toggle="dropdown">Unitats formatives</button>';
echo '          <div class="dropdown-menu">';
echo '            <button class="dropdown-item" type="button">Sub action</button>';
echo '            <button class="dropdown-item" type="button">Another sub action</button>';
echo '            <button class="dropdown-item" type="button">Something else here</button>';
echo '          </div>';
echo '        </div>';
//echo '      </div>';


/*
echo '      <div class="dropdown-menu">';
echo '        <button class="dropdown-item" type="button">Sub action</button>';

echo '        <div class="dropdown dropright dropdown-submenu">';
echo '          <button class="dropdown-item dropdown-toggle" type="button">Another sub action</button>';



echo '        </div>';

echo '        <button class="dropdown-item" type="button">Something else here</button>';
echo '        <button class="dropdown-item" type="button" disabled>Disabled action</button>';

    echo '    <div class="dropdown dropright dropdown-submenu">';
    echo '      <button class="dropdown-item dropdown-toggle" type="button">Another action</button>';

echo '          <div class="dropdown-menu">';
echo '            <button class="dropdown-item" type="button">Sub action</button>';
echo '            <button class="dropdown-item" type="button">Another sub action</button>';
echo '            <button class="dropdown-item" type="button">Something else here</button>';
echo '          </div>';
echo '        </div>';
echo '      </div>		';	
	*/		
			
/*			
echo '    <div class="dropdown dropright dropdown-submenu">';
echo '      <button class="dropdown-item dropdown-toggle" type="button">Submenú</button>';
echo '      <div class="dropdown-menu">';
echo '        <button class="dropdown-item" type="button">Sub action</button>';
echo '        <button class="dropdown-item" type="button">Another sub action</button>';
echo '        <button class="dropdown-item" type="button">Something else here</button>';
echo '      </div>';
echo '    </div>			';
			*/
	
			
			
			
			echo '            </div>';
			echo '          </li>';
		}
		echo '        </ul>';

		$NomComplet = utf8_encode(trim($Usuari->nom.' '.$Usuari->cognom1.' '.$Usuari->cognom2));
//		echo '        <form class="form-inline my-2 my-lg-0" action="Surt.php">';
//		echo '          <span class="navbar-brand">'.$NomComplet.'</span>';
//		echo '          <button class="btn btn-primary my-2 my-sm-0" type="submit">Surt</button>';

		echo '        <ul class="navbar-nav ml-auto">';
		echo '          <li class="nav-item dropdown">';
		echo '            <a class="nav-link dropdown-toggle" href="#" id="ddUsuari" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$NomComplet.'</a>';
		echo '            <div class="dropdown-menu" aria-labelledby="ddUsuari">';
		echo '              <a class="dropdown-item" href="CanviPassword.html">Canvia password</a>';
		echo '              <div class="dropdown-divider"></div>';
		echo '              <a class="dropdown-item" href="Surt.php">Surt</a>';
		echo '            </div>';
		echo '          </li>';
		echo '        </ul>';

//		echo '        </form>';

		echo '      </div>';
		echo '    </nav>';
		echo '<BR>'; // Pedaç!
	}
	echo '      <div class="starter-template" style="padding:20px">';
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
/*	
echo '  <a class="js-scroll-top scroll-top btn btn-primary btn-sm hidden" href="https://vsn4ik.github.io/bootstrap-submenu/#container">';
echo '    <span class="fas fa-caret-up fa-2x"></span>';
echo '  </a>';
*/	


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
/*function CreaDesplegable($Titol, $Nom, $Codi, $Valor)
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
}*/

/**
 * CreaLookup
 *
 * Crea un "lookup" (element INPUT + BUTTON per cercar les dades en una altra finestra).
 * Conté:
 *  - Camp amagat on hi haurà el identificador (camp lkh_).
 *  - Camp amagat on hi haurà els camps a mostrar dels retornats (camp lkh_X_camps).
 *  - Camp text on hi haurà la descripció (camp lkp_).
 *  - Botó per fer la recerca.
 *
 * @param string $Nom Nom del lookup.
 * @param string $URL Pàgina web de recerca.
 * @param string $Id Identificador del registre que es mostra.
 * @param string $Camps Camps a mostrar al lookup separats per comes.
 * @return string Codi HTML del lookup.
 */
/*function CreaLookup($Nom, $URL, $Id, $Camps)
{
	$sRetorn = '<div class="input-group mb-3">';
	$sRetorn .= "  <input type=hidden name=lkh_".$Nom." value=''>";
	$sRetorn .= "  <input type=hidden name=lkh_".$Nom."_camps value='".$Camps."'>";
	$sRetorn .= '  <input type="text" class="form-control" name="lkp_'.$Nom.'">';
	$sRetorn .= '  <div class="input-group-append">';
	$onClick = "CercaLookup('lkh_".$Nom."', 'lkp_".$Nom."', '".$URL."', '".$Camps."');";
	$sRetorn .= '    <button class="btn btn-outline-secondary" type="button" onclick="'.$onClick.'">Cerca</button>';
	$sRetorn .= '  </div>';
	$sRetorn .= '</div>';
	return $sRetorn;
}*/

/**
 * PaginaHTMLMissatge
 *
 * Crea una pàgina HTML amb un missatge i un link a la pàgina principal.
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
	echo '<h4 class="alert-heading">'.$Titol.'</h4>';
	echo '<p>'.$Missatge.'</p>';
	echo '<hr>';
	echo '<p>Retorna a la <a href="index.php" class="alert-link">pàgina principal</a>.</p>';
	echo '</div>';	
	echo '</div>';	
	echo '</BODY>';	
}
 
 ?>