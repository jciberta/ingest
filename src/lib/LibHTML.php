<?php

/** 
 * LibHTML.php
 *
 * Llibreria d'HTML.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */
 
require_once(ROOT.'/lib/LibURL.php');


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
	echo CreaIniciHTML_BootstrapStarterTemplate($Usuari, $Titol, $bMenu, $bSaga);
}

 /**
 * Genera l'inici del document HTML.
 * @param object $Usuari Usuari autenticat.
 * @param string $Titol Títol de la pàgina.
 * @param boolean $bMenu Indica si el menú ha d'haver menú a la capçalera o no.
 * @param boolean $bSaga Indica si usem els estil de SAGA.
 * @param boolean $bDataTables Indica si usem la llibreria DataTables.
 * @return string Codi HTML de la pàgina.
 */
function GeneraIniciHTML($Usuari, $Titol, $bMenu = True, $bSaga = False, $bDataTables = False)
{
	return CreaIniciHTML_BootstrapStarterTemplate($Usuari, $Titol, $bMenu, $bSaga, $bDataTables);
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
 * @param boolean $bDataTables Indica si usem la llibreria DataTables.
 * @return string Codi HTML de la pàgina.
 */
function CreaIniciHTML_BootstrapStarterTemplate($Usuari, $Titol, $bMenu = True, $bSaga = False, $bDataTables = True)
{
//var_dump($bDataTables);	
//exit;
	$Retorn = '<HTML>';
	$Retorn .= '<HEAD>';
	$Retorn .= '	<META charset=UTF8>';
	$Retorn .= '	<TITLE>InGest</TITLE>';
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap-submenu/dist/css/bootstrap-submenu.min.css">';
//	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">';
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">';
	$Retorn .= '	<link rel="stylesheet" href="vendor/summernote/summernote-bs4.min.css">';
	$Retorn .= '	<link rel="stylesheet" href="css/InGest.css?v1.1">';
	if ($bSaga)
		$Retorn .= '	<link rel="stylesheet" href="css/saga.css">';
	if ($bDataTables) {
		$Retorn .= '	<link rel="stylesheet" href="vendor/DataTables/datatables.bootstrap4.min.css">';
		$Retorn .= '	<link rel="stylesheet" href="vendor/DataTables/fixedColumns.bootstrap4.min.css">';
	}
	$Retorn .= '	<script src="vendor/jquery.min.js"></script>';
	$Retorn .= '	<script src="vendor/popper.min.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap-submenu/dist/js/bootstrap-submenu.min.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap-submenu/bootstrap-submenu.fix.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap-datepicker/locales/bootstrap-datepicker.ca.min.js" charset="UTF-8"></script>';
	$Retorn .= '	<script src="vendor/summernote/summernote-bs4.min.js" charset="UTF-8"></script>';
	$Retorn .= '	<script src="vendor/bootbox.min.js"></script>';
	$Retorn .= '	<script src="js/Util.js"></script>';
	if ($bDataTables) {
		$Retorn .= '	<script src="vendor/DataTables/jquery.dataTables.min.js"></script>';
		$Retorn .= '	<script src="vendor/DataTables/datatables.bootstrap4.min.js"></script>';
		$Retorn .= '	<script src="vendor/DataTables/dataTables.fixedColumns.min.js"></script>';
	}
	$Retorn .= '</HEAD>';
	if (Config::Demo)
		$Retorn .= '<BODY STYLE="background-color:#ffa70570">';
	else
		$Retorn .= '<BODY BGCOLOR=yellow>';
//		$Retorn .= '<BODY>';
	if ($bMenu) {
		$Retorn .= Menu::Crea($Usuari);
	}
	$Retorn .= '      <div class="starter-template" style="padding:20px">';
//	$Retorn .= '<H1>'.utf8_encode($Titol).'</H1>';
	$Retorn .= '<H1>'.$Titol.'</H1>';
	return $Retorn;
}

/**
 * CreaIniciHTML_Notes
 * @param object $Usuari Usuari autenticat.
 * @param string $Titol Títol de la pàgina.
 * @return string Codi HTML de la pàgina.
 */
function CreaIniciHTML_Notes($Usuari, $Titol)
{
	$Retorn = '<HTML>';
	$Retorn .= '<HEAD>';
	$Retorn .= '	<META charset=UTF8>';
	$Retorn .= '	<TITLE>InGest</TITLE>';
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap-submenu/dist/css/bootstrap-submenu.min.css">';
//	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">';
//	$Retorn .= '	<link rel="stylesheet" href="vendor/summernote/summernote-bs4.min.css">';
	$Retorn .= '	<link rel="stylesheet" href="css/InGest.css?v1.1">';
	if (Config::UsaDataTables) {
		$Retorn .= '	<link rel="stylesheet" href="vendor/DataTables/datatables.bootstrap4.min.css">';
		$Retorn .= '	<link rel="stylesheet" href="vendor/DataTables/fixedColumns.bootstrap4.min.css">';
	}
	$Retorn .= '	<script src="vendor/jquery.min.js"></script>';
	$Retorn .= '	<script src="vendor/popper.min.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap-submenu/dist/js/bootstrap-submenu.min.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap-submenu/bootstrap-submenu.fix.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>';
	$Retorn .= '	<script src="vendor/bootstrap-datepicker/locales/bootstrap-datepicker.ca.min.js" charset="UTF-8"></script>';
//	$Retorn .= '	<script src="vendor/summernote/summernote-bs4.min.js" charset="UTF-8"></script>';
	$Retorn .= '	<script src="vendor/bootbox.min.js"></script>';
	$Retorn .= '	<script src="js/Util.js"></script>';
	if (Config::UsaDataTables) {
		$Retorn .= '	<script src="vendor/DataTables/jquery.dataTables.min.js"></script>';
		$Retorn .= '	<script src="vendor/DataTables/datatables.bootstrap4.min.js"></script>';
		$Retorn .= '	<script src="vendor/DataTables/dataTables.fixedColumns.min.js"></script>';
	}
	$Retorn .= '</HEAD>';
	
	if (Config::UsaDataTables) {
		$Retorn .= "<style>";
		$Retorn .= "    th, td { white-space: nowrap; } ";
		$Retorn .= "    div.dataTables_wrapper {";
		$Retorn .= "        width: 99%;";
		//$Retorn .= "        width: 1800px;";
		$Retorn .= "        margin: 0 auto;";
		$Retorn .= "    }";
		$Retorn .= "</style>";
	}	

	$Retorn .= '<BODY>';
	$Retorn .= Menu::Crea($Usuari);
	$Retorn .= '      <div class="starter-template" style="padding:20px">';
	$Retorn .= '<H1>'.$Titol.'</H1>';
	echo $Retorn;
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
	echo "<script>";
	echo "$(document).ready(function() {";
  	echo "	$('.summernote').summernote({lang:'ca-ES',height:100});";
	echo "});";
	echo "</script>";

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
 
function CreaTargeta($Titol, $Text, $URL): string
{
	$Retorn = '  <div class="card">';
	$Retorn .= '    <div class="card-body">';
	$Retorn .= '      <h5 class="card-title">'.utf8_encode($Titol).'</h5>';
	$Retorn .= '      <p class="card-text">'.utf8_encode($Text).'.</p>';
	$Retorn .= '      <a href="'.$URL.'" class="btn btn-primary btn-sm">Ves-hi</a>';
	$Retorn .= '    </div>';
	$Retorn .= '  </div>';
	return $Retorn;			
}
 
 
/**
 * Classe que encapsula les utilitats per a la pàgina d'entrada.
 */
class Portal
{
	/**
	* Codi per incloure fitxers JavaScript.
	* @var string
	*/    
    public $JavaScript = ''; 

	/**
	 * Escriu la capçalera de la pàgina web.
	 */				
	public function EscriuCapcalera() {
		echo '<html>';
		echo '<head>';
		echo '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
		echo '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">';
		echo '	<script src="vendor/jquery.min.js"></script>';
		echo '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>';
		echo $this->JavaScript;
		echo '</head>';
		echo '<body>';
		echo '<div class="container">';
		echo '	<div class="header clearfix">';
		echo '		<nav>';
		echo '		<ul class="nav nav-pills float-right">';
		echo '			<li class="nav-item"><a class="nav-link" href="http://inspalamos.cat" target="_blank">Web</a></li>';
		echo '		</ul>';
		echo '		</nav>';
		echo '	</div>';
	}

	/**
	 * Escriu el peu de la pàgina web.
	 */				
	public function EscriuPeu(bool $bRecuperaContrasenya = True) {
		echo '	<footer class="footer">';
		echo '	<p style="text-align:left;">Institut de Palamós';
		if ($bRecuperaContrasenya)
			echo '		<span style="float:right;"><a href="RecuperaPassword.html">Recupera contrasenya</a></span>';
		echo '	</p>';
		echo '	</footer>';
		echo '</div>';
		echo "<div id=debug></div>";
		echo '</body> ';
		echo '</html>';
	}
}

/**
 * Classe base per a la realització de menús.
 */
class Menu
{
	static public function Obre(string $Text): string {
		$Retorn = '<li class="nav-item dropdown">';
		$Retorn .= '<a class="nav-link dropdown-toggle" href="#" id="dd'.$Text.'" data-toggle="dropdown" data-submenu="" aria-haspopup="true" aria-expanded="false">'.$Text.'</a>';
		$Retorn .= '<div class="dropdown-menu" aria-labelledby="dd'.$Text.'">';
		return $Retorn;
	}

	static public function Tanca(): string {
		return '</div></li>';
	}

	static public function Separador(): string {
		return '<div class="dropdown-divider"></div>';
	}
	
	static public function Opcio(string $Text, string $URL): string {
		return '<a class="dropdown-item" href="'.GeneraURL($URL).'">'.$Text.'</a>';
	}
	
	static public function Crea($Usuari): string {
		$Retorn = '<nav class="navbar navbar-dark bg-dark navbar-expand-sm fixed-top">';
		if ($Usuari->es_admin) 
			$Retorn .= '	<span class="navbar-brand">inGest '.Config::Versio.'</span>';
		else
			$Retorn .= '	<span class="navbar-brand">inGest</span>';
		$Retorn .= '	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar-collapse">';
		$Retorn .= '		<span class="navbar-toggler-icon"></span>';
		$Retorn .= '	</button>';
		$Retorn .= '	<div class="collapse navbar-collapse">';
		$Retorn .= '		<ul class="navbar-nav mr-auto">';
		$Retorn .= '			<li class="nav-item active">';
		$Retorn .= '				<a class="nav-link" href="'.GeneraURL('Escriptori.php').'">Inici</a>';
		$Retorn .= '			</li>';
		if (($Usuari->es_admin) || ($Usuari->es_direccio) || ($Usuari->es_cap_estudis)) {
			// Menú Alumnes
			$Retorn .= Menu::Obre('Alumnes');
			$Retorn .= Menu::Opcio('Alumnes', 'UsuariRecerca.php?accio=Alumnes');
			$Retorn .= Menu::Opcio('Alumnes/pares', 'UsuariRecerca.php?accio=AlumnesPares');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Matrícules', 'UsuariRecerca.php?accio=Matricules');
			$Retorn .= Menu::Opcio('Matriculació alumnes', 'FormMatricula.php');
			$Retorn .= Menu::Tanca();

			// Menú Professors
			$Retorn .= Menu::Obre('Professors');
			$Retorn .= Menu::Opcio('Professors', 'UsuariRecerca.php?accio=Professors');
			$Retorn .= Menu::Opcio('Professors per UF', 'AssignaUFs.php?accio=ProfessorsUF');
			$Retorn .= Menu::Opcio('Assignació UF', 'AssignaUFs.php?accio=GrupAssignaUF');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Tutors', 'UsuariRecerca.php?accio=Tutors');
			//$Retorn .= Menu::Opcio('Guàrdies', 'Guardia.php');
			$Retorn .= Menu::Tanca();

			// Menú FP
			$Retorn .= Menu::Obre('FP');
			$Retorn .= Menu::Opcio('Famílies', 'FPRecerca.php?accio=Families');
			$Retorn .= Menu::Opcio('Cicles formatius', 'FPRecerca.php?accio=CiclesFormatius');
			$Retorn .= Menu::Opcio('Mòduls professionals', 'FPRecerca.php?accio=ModulsProfessionals');
			$Retorn .= Menu::Opcio('Unitats formatives', 'FPRecerca.php?accio=UnitatsFormativesCF');
			$Retorn .= Menu::Separador();
			$Retorn .= '<div class="dropdown dropright dropdown-submenu">';
			$Retorn .= "	<button class='dropdown-item dropdown-toggle' type='button'>Pla d'estudis</button>";
			$Retorn .= '	<div class="dropdown-menu">';
			$Retorn .= "		<a class='dropdown-item' href='".GeneraURL('FPRecerca.php?accio=PlaEstudisAny')."'>Pla d'estudis per any</a>";
			$Retorn .= "		<a class='dropdown-item' href='".GeneraURL('FPRecerca.php?accio=PlaEstudisCicle')."'>Pla d'estudis per cicle</a>";
			$Retorn .= "		<a class='dropdown-item' href='".GeneraURL('FPRecerca.php?accio=PlaEstudisUnitat')."'>Unitats formatives del pla d'estudis</a>";
			$Retorn .= '	</div>';
			$Retorn .= '</div>';
			$Retorn .= '<div class="dropdown dropright dropdown-submenu">';
			$Retorn .= "	<button class='dropdown-item dropdown-toggle' type='button'>Programació didàctica</button>";
			$Retorn .= '	<div class="dropdown-menu">';
			$Retorn .= "		<a class='dropdown-item' href='".GeneraURL('FPRecerca.php?accio=ResultatsAprenentatge')."'>Resultats d'aprenentatge</a>";
			$Retorn .= '	</div>';
			$Retorn .= '</div>';
			//$Retorn .= Menu::Opcio('Programació didàctica', 'FPRecerca.php?accio=UnitatsFormativesCF');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Cursos', 'Escriptori.php');
			$Retorn .= Menu::Opcio('Avaluacions', 'Recerca.php?accio=Avaluacio');
			$Retorn .= Menu::Tanca();

			// Menú Centre
			$Retorn .= Menu::Obre('Centre');
			$Retorn .= Menu::Opcio('Usuaris', 'UsuariRecerca.php');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Any acadèmic', 'Recerca.php?accio=AnyAcademic');
			$Retorn .= Menu::Opcio('Equips', 'Recerca.php?accio=Equip');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Importa usuaris', 'ImportaUsuarisDialeg.php');
			//$Retorn .= Menu::Opcio('Importa passwords iEduca', 'ImportaPasswordsDialeg.php');
			$Retorn .= Menu::Opcio('Importa matrícules', 'ImportaMatriculaDialeg.php');
			$Retorn .= Menu::Tanca();

			// Menú Informes
			$Retorn .= Menu::Obre('Informes');
			$Retorn .= Menu::Opcio('Darrers accessos', 'UsuariRecerca.php?accio=UltimLogin');
			$Retorn .= Menu::Opcio('Estadístiques notes', 'Estadistiques.php?accio=EstadistiquesNotes');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Promoció alumnes 1r', 'UsuariRecerca.php?accio=AlumnesPromocio1r');
			$Retorn .= Menu::Opcio('Graduació alumnes 2n', 'UsuariRecerca.php?accio=AlumnesGraduacio2n');
			$Retorn .= Menu::Tanca();
		}	
		$Retorn .= '		</ul>';

		// Menú usuari
		$NomComplet = utf8_encode(trim($Usuari->nom.' '.$Usuari->cognom1.' '.$Usuari->cognom2));
		$Retorn .= '		<ul class="navbar-nav">';
		$Retorn .= '		  <li class="nav-item dropdown">';
	//		$Retorn .= '		  <a class="nav-link dropdown-toggle" tabindex="0" data-toggle="dropdown" data-submenu="" aria-haspopup="true">'.$NomComplet.'</a>';
		$Retorn .= '          <a class="nav-link dropdown-toggle" href="#" id="ddUsuari" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$NomComplet.'</a>';
		$Retorn .= '			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="ddUsuari">';
		$Retorn .= Menu::Opcio('Canvia password', 'CanviPassword.html');
		$Retorn .= Menu::Separador();
		if ($Usuari->es_cap_estudis) {
			$Retorn .= Menu::Opcio('Canvia a professor', 'CanviaRol.php');
			$Retorn .= Menu::Separador();
		}
		if ($Usuari->es_admin) {
			$Retorn .= Menu::Opcio('Administra', 'Administra.php');
			$Retorn .= Menu::Opcio('Consola SQL', 'ConsolaSQL.php');
			$Retorn .= Menu::Opcio('Registres', 'Recerca.php?accio=Registre');
			$Retorn .= Menu::Separador();
		}
		$Retorn .= Menu::Opcio('Surt', 'Surt.php');
		$Retorn .= Menu::Tanca();
		$Retorn .= '		</ul>';

		$Retorn .= '	</div>';
		$Retorn .= '</nav>';
		$Retorn .= '<BR><BR>'; // Per donar espai al menú
	
		return $Retorn;
	}	
}

?>