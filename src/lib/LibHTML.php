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
	$Retorn = '<!DOCTYPE html>'.PHP_EOL;
	$Retorn .= '<HTML>'.PHP_EOL;
	$Retorn .= '<HEAD>'.PHP_EOL;
	$Retorn .= '	<META charset=UTF8>'.PHP_EOL;
	$Retorn .= '	<META name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no"> '.PHP_EOL;
	$Retorn .= '	<TITLE>InGest</TITLE>'.PHP_EOL;
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">'.PHP_EOL;
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap-submenu/dist/css/bootstrap-submenu.min.css">'.PHP_EOL;
//	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">';
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">'.PHP_EOL;
	$Retorn .= '	<link rel="stylesheet" href="vendor/summernote/summernote-bs4.min.css">'.PHP_EOL;
	$Retorn .= '	<link rel="stylesheet" href="css/InGest.css?v1.1">'.PHP_EOL;
	if ($bSaga)
		$Retorn .= '	<link rel="stylesheet" href="css/saga.css">'.PHP_EOL;
	if ($bDataTables) {
		$Retorn .= '	<link rel="stylesheet" href="vendor/DataTables/datatables.bootstrap4.min.css">'.PHP_EOL;
		$Retorn .= '	<link rel="stylesheet" href="vendor/DataTables/fixedColumns.bootstrap4.min.css">'.PHP_EOL;
	}
	$Retorn .= '	<script src="vendor/jquery.min.js"></script>'.PHP_EOL;
	$Retorn .= '	<script src="vendor/popper.min.js"></script>'.PHP_EOL;
	$Retorn .= '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>'.PHP_EOL;
	$Retorn .= '	<script src="vendor/bootstrap-submenu/dist/js/bootstrap-submenu.min.js"></script>'.PHP_EOL;
	$Retorn .= '	<script src="vendor/bootstrap-submenu/bootstrap-submenu.fix.js"></script>'.PHP_EOL;
	$Retorn .= '	<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>'.PHP_EOL;
	$Retorn .= '	<script src="vendor/bootstrap-datepicker/locales/bootstrap-datepicker.ca.min.js" charset="UTF-8"></script>'.PHP_EOL;
	$Retorn .= '	<script src="vendor/summernote/summernote-bs4.min.js" charset="UTF-8"></script>'.PHP_EOL;
	$Retorn .= '	<script src="vendor/bootbox.min.js"></script>'.PHP_EOL;
	$Retorn .= '	<script src="js/Util.js"></script>'.PHP_EOL;
	if ($bDataTables) {
		$Retorn .= '	<script src="vendor/DataTables/jquery.dataTables.min.js"></script>'.PHP_EOL;
		$Retorn .= '	<script src="vendor/DataTables/datatables.bootstrap4.min.js"></script>'.PHP_EOL;
		$Retorn .= '	<script src="vendor/DataTables/dataTables.fixedColumns.min.js"></script>'.PHP_EOL;
	}
	$Retorn .= '</HEAD>'.PHP_EOL;
	if (Config::Demo)
		$Retorn .= '<BODY STYLE="background-color:#ffa70570">'.PHP_EOL;
	else
		$Retorn .= '<BODY>'.PHP_EOL;
	if ($Usuari !== null && $bMenu) {
		if ($Usuari->aplicacio == 'InGest')
			$Retorn .= MenuInGest::Crea($Usuari);
		else if ($Usuari->aplicacio == 'CapGest')
			$Retorn .= MenuCapGest::Crea($Usuari);
	}
	$Retorn .= '    <div class="starter-template" style="padding:20px">'.PHP_EOL;
	$Retorn .= '        <H1>'.$Titol.'</H1>'.PHP_EOL;
	return $Retorn;
}

/**
 * CreaIniciHTML_Notes
 * @param object $Usuari Usuari autenticat.
 * @param string $Titol Títol de la pàgina.
 * @return string Codi HTML de la pàgina.
 */
function CreaIniciHTML_JS_CSS($Usuari, $Titol, $JS = '', $CSS = '')
{
	$Retorn = '<HTML>';
	$Retorn .= '<HEAD>';
	$Retorn .= '	<META charset=UTF8>';
	$Retorn .= '	<TITLE>InGest</TITLE>';
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">';
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap-submenu/dist/css/bootstrap-submenu.min.css">';
	$Retorn .= '	<link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">';
	$Retorn .= '	<link rel="stylesheet" href="vendor/summernote/summernote-bs4.min.css">';
	$Retorn .= '	<link rel="stylesheet" href="css/InGest.css?v1.1">';
	$Retorn .= $CSS;
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
	$Retorn .= $JS;
	$Retorn .= '</HEAD>';

	$Retorn .= '<BODY>';
	$Retorn .= MenuInGest::Crea($Usuari);
	$Retorn .= '      <div class="starter-template" style="padding:20px">';
	$Retorn .= '<H1>'.$Titol.'</H1>';
	echo $Retorn;
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
	$Retorn .= '</HEAD>';

	$Retorn .= '<BODY>';
	$Retorn .= MenuInGest::Crea($Usuari);
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
	echo "</div>".PHP_EOL;
	echo "    <DIV id=debug></DIV>".PHP_EOL;
	echo "    <DIV id=debug2></DIV>".PHP_EOL;
	echo '</BODY>'.PHP_EOL;
	echo '</HTML>'.PHP_EOL;
}

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
	echo "<HTML>".PHP_EOL;
	echo "<HEAD>".PHP_EOL;;
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
	$Retorn = '<div class="col-sm-2">';
	$Retorn .= '  <div class="card">';
	$Retorn .= '    <div class="card-body">';
	$Retorn .= '      <h5 class="card-title">'.$Titol.'</h5>';
	$Retorn .= '      <p class="card-text">'.$Text.'</p>';
	$Retorn .= '      <a href="'.$URL.'" class="btn btn-primary btn-sm">Ves-hi</a>';
	$Retorn .= '    </div>';
	$Retorn .= '  </div>';
	$Retorn .= '</div>';
	return $Retorn;			
}

function CreaTargeta2($Titol, $Text1, $URL1, $Text2, $URL2): string
{
	$Retorn = '<div class="col-sm-2">';
	$Retorn .= '<div class="card">';
	$Retorn .= '    <div class="card-body">';
	$Retorn .= '      <h5 class="card-title">'.$Titol.'</h5>';
	$Retorn .= '<table style="cellpadding:10px;border-collapse: separate;border-spacing: 0px 6px ">';
	$Retorn .= '<tr>';
	$Retorn .= '      <td><p class="card-text">'.$Text1.'</p></td>';
	$Retorn .= '      <td><p class="card-text">'.$Text2.'</p></td>';
	$Retorn .= '</tr>';
	$Retorn .= '<tr>';
	$Retorn .= '      <td><a href="'.$URL1.'" class="btn btn-primary btn-sm">Ves-hi</a></td>';
	$Retorn .= '      <td><a href="'.$URL2.'" class="btn btn-primary btn-sm">Ves-hi</a></td>';
	$Retorn .= '</tr>';
	$Retorn .= '</table>';
	$Retorn .= '    </div>';
	$Retorn .= '  </div>';
	$Retorn .= '</div>';
	return $Retorn;			
}

/**
 * Crea la taula 1. Layout:
 * +--------+-------------+
 * | Titol1 | Descripció1 |
 * +--------+-------------+
 * | Titol2 | Descripció2 |
 * +--------+-------------+
 * | ...    | ...         |
 * @param array $Dades Array associatiu.
 * @return string Codi HTML de la taula.
 */
function CreaTaula1(array $Dades): string {
	$Retorn = '<table border=0>';
	foreach ($Dades as $key => $value) {
		$Retorn .= "<tr>";
		$Retorn .= "<td style='padding-right:10px;'>$key:</td>";
		$Retorn .= "<td><b>".utf8_encodeX($value)."</b></td>";
		$Retorn .= "</tr>";
	}
	$Retorn .= '</table>';
	return $Retorn;
}

/**
 * Crea la taula 1 trasposada. Layout:
 * +-------------+-------------+------
 * |   Titol1    |   Titol2    |  ...
 * +-------------+-------------+------
 * | Descripció1 | Descripció2 |  ...
 * +-------------+-------------+------
 * @param array $Dades Array associatiu.
 * @return string Codi HTML de la taula.
 */
function CreaTaula1T(array $Dades): string {
	$Retorn = '<table border="1">';
	$Retorn .= "<tr>";
	foreach ($Dades as $key => $value) {
		$Retorn .= "<td style='text-align:center;padding-right:10px;padding-left:10px;'>$key</td>";
	}
	$Retorn .= "</tr>";
	$Retorn .= "<tr>";
	foreach ($Dades as $key => $value) {
		$Retorn .= "<td style='text-align:center;'><b>".utf8_encodeX($value)."</b></td>";
	}
	$Retorn .= "</tr>";
	$Retorn .= '</table>';
	return $Retorn;
}

/**
 * Classe que encapsula les utilitats HTML.
 */
class HTML
{
	// Tipus de missatges.
	const tmSUCCES = 1;
	const tmINFORMACIO = 2;
	const tmAVIS = 3;
	const tmERROR = 4;

	/**
	 * Escriu un missatge.
	 * @param int $Tipus Tipus: tmSUCCES, tmINFORMACIO, tmAVIS, tmERROR.
	 * @param string $Text Text del missatge.
	 * @param bool $Titol Opció de mostrar el títol.
	 */				
	public static function EscriuMissatge(int $Tipus, string $Text, bool $Titol = false) {
		echo '<div class="container">';
		switch ($Tipus) {
			case self::tmSUCCES:
				echo '<div class="alert alert-success" role="alert">';
				if ($Titol) 
					echo '<h4 class="alert-heading">Informació</h4>';
				break;
			case self::tmINFORMACIO:
				echo '<div class="alert alert-info" role="alert">';
				if ($Titol) 
					echo '<h4 class="alert-heading">Confirmació</h4>';
				break;
			case self::tmAVIS:
				echo '<div class="alert alert-warning" role="alert">';
				if ($Titol) 
					echo '<h4 class="alert-heading">Avís</h4>';
				break;
			case self::tmERROR:
				echo '<div class="alert alert-danger" role="alert">';
				if ($Titol) 
					echo '<h4 class="alert-heading">Error</h4>';
				break;
		}
		echo '<p>'.$Text.'</p>';
		echo '</div>';	
		echo '</div>';	
	}

	/**
	 * Crea un botó desplegable amb opcions.
	 * @param string $Titol Títol del botó.
	 * @param array $Opcions Opcions del desplegable en forma d'array associatiu (text, URL).
	 * @return string Codi HTML del botó.
	 */				
	public static function CreaBotoDesplegable(string $Titol, array $Opcions): string {
		$Retorn = '';
		$Retorn .= '<div class="btn-group" role="group">';
		$Retorn .= '<button id="btnGroupDrop1" type="button" class="btn btn-primary active dropdown-toggle" data-toggle="dropdown">';
		$Retorn .= $Titol;
		$Retorn .= '</button>';
		$Retorn .= '<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">';
		foreach ($Opcions as $Text => $URL) {
			$Retorn .= '<a id="'.Normalitza($Text).'" class="dropdown-item" href="'.$URL.'">'.$Text.'</a>';
		}
		$Retorn .= '</div>';
		$Retorn .= '</div>';		
		return $Retorn;
	}

	/**
	 * Crea un camp que conté una seqüència de fotografies (carousel).
	 * Fotografies apaisades i 800x600.
	 * https://getbootstrap.com/docs/4.0/components/carousel/
	 * @param string $Valor Valor que identifica la fotografia.
	 * @param string $Prefix Prefix del fitxer que s'afegirà a la carpeta pix/.
	 * @param string $Sufix Sufix que s'afegeix al valor per completar el fitxer de la fotografia.
	 * @return string Codi HTML del camp que conté una seqüència de fotografies.
	 */
	public static function CreaSequenciaFotografies(string $Valor, string $Prefix, string $Sufix): string {
		$Fitxer = 'img/pix/'.$Prefix.$Valor.'*'.$Sufix;
		$list = glob($Fitxer);

		if (count($list) === 0) {
			$sRetorn = 'No hi ha imatges';
//			$sRetorn = '<TD>No hi ha imatges</TD>';
		}
		else {
			$sRetorn = '
				<div id="carousel'.$Valor.'" class="carousel slide" data-ride="carousel" style="height:200px;width:400px;background-color:lightgrey;">
					<ol class="carousel-indicators">
			';
			for ($i=0; $i<count($list); $i++) {
				if ($i == 0)
					$sRetorn .= '<li data-target="#carousel'.$Valor.'" data-slide-to="'.$i.'" class="active"></li>';
				else
					$sRetorn .= '<li data-target="#carousel'.$Valor.'" data-slide-to="'.$i.'"></li>';
			}
			$sRetorn .= '
					</ol>
					<div class="carousel-inner">
			';
			for ($i=0; $i<count($list); $i++) {
				if ($i == 0)
					$sRetorn .= '
						<div class="carousel-item active" style="height:200px;width:400px">
							<img style="height:200px;width:400px;object-fit:contain;" class="d-block w-100 img-fluid" src="'.$list[$i].'">
						</div>
					';
				else
					$sRetorn .= '
						<div class="carousel-item" style="height:200px;width:400px">
							<img style="height:200px;width:400px;object-fit:contain;" class="d-block w-100 img-fluid" src="'.$list[$i].'">
						</div>
					';
			}
			$sRetorn .= '
				</div>
				<a class="carousel-control-prev" href="#carousel'.$Valor.'" role="button" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carousel'.$Valor.'" role="button" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
                </a>
				</div>		
			';
		}
		return $sRetorn;
	}		
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
	public function EscriuCapcalera(string $CapcaleraLogin = '') {
		echo '<html>'.PHP_EOL;
		echo '<head>'.PHP_EOL;
		echo '	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">'.PHP_EOL;
		echo '	<link rel="stylesheet" href="vendor/bootstrap/css/narrow-jumbotron.css">'.PHP_EOL;
		echo '	<meta name="viewport" content="width=device-width, initial-scale=1.0"> '.PHP_EOL;
		echo '	<script src="vendor/jquery.min.js"></script>'.PHP_EOL;
		echo '	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>'.PHP_EOL;
		echo $this->JavaScript.PHP_EOL;
		echo '</head>'.PHP_EOL;
		echo '<body>'.PHP_EOL;
		echo '<div class="container">'.PHP_EOL;
		echo '	<div class="header clearfix">'.PHP_EOL;
		echo '		<nav>'.PHP_EOL;
		echo $CapcaleraLogin;
		echo '		</nav>'.PHP_EOL;
		echo '	</div>'.PHP_EOL;
	}

	/**
	 * Escriu el peu de la pàgina web.
	 */				
	public function EscriuPeu(string $Nom, bool $bRecuperaContrasenya = True) {
		echo '	<footer class="footer">'.PHP_EOL;
		echo '	<p style="text-align:left;">'.$Nom.PHP_EOL;
		if ($bRecuperaContrasenya)
			echo '		<span style="float:right;"><a href="RecuperaPassword.html">Recupera contrasenya</a></span>'.PHP_EOL;
		echo '	</p>'.PHP_EOL;
		echo '	</footer>'.PHP_EOL;
		echo '</div>'.PHP_EOL;
		echo "<div id=debug></div>".PHP_EOL;
		echo '</body>'.PHP_EOL;
		echo '</html>'.PHP_EOL;
	}
}

/**
 * Classe base per a la realització de menús de l'aplicació.
 */
abstract class Menu
{
	static public function Obre(string $Text): string {
		$Retorn = '            <li class="nav-item dropdown">'.PHP_EOL;
		$Retorn .= '                <a class="nav-link dropdown-toggle" href="#" id="dd'.$Text.'" data-toggle="dropdown" data-submenu="" aria-haspopup="true" aria-expanded="false">'.$Text.'</a>'.PHP_EOL;
		$Retorn .= '                <div class="dropdown-menu" aria-labelledby="dd'.$Text.'">'.PHP_EOL;
		return $Retorn;
	}

	static public function Tanca(): string {
		return '                </div>'.PHP_EOL .'            </li>'.PHP_EOL;
	}

	static public function ObreSubMenu(string $Text): string {
		$Retorn = '<div class="dropdown dropright dropdown-submenu">';
		$Retorn .= "<button class='dropdown-item dropdown-toggle' type='button'>$Text</button>";
		$Retorn .= '<div class="dropdown-menu">';		
		return $Retorn;
	}

	static public function TancaSubMenu(): string {
		return '</div></div>';
	}

	static public function Separador(): string {
		return '<div class="dropdown-divider"></div>';
	}
	
	static public function Opcio(string $Text, string $URL): string {
		return '<a class="dropdown-item" href="'.GeneraURL($URL).'">'.$Text.'</a>'.PHP_EOL;
	}

	abstract static public function Crea($Usuari): string;
}


/**
 * Classe per a la realització de menús de l'aplicació InGest.
 */
class MenuInGest extends Menu
{
	static public function Crea($Usuari): string {
		$Retorn = PHP_EOL;
		$Retorn .= '<!-- INICI Menú -->'.PHP_EOL;
		$Retorn .= '<nav class="navbar navbar-dark bg-dark navbar-expand-sm fixed-top">'.PHP_EOL;
		if ($Usuari->es_admin) 
			$Retorn .= '	<span class="navbar-brand">'.$Usuari->aplicacio.' '.Config::Versio.'</span>'.PHP_EOL;
		else
			$Retorn .= '	<span class="navbar-brand">InGest</span>'.PHP_EOL;
		$Retorn .= '	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar-collapse">'.PHP_EOL;
		$Retorn .= '		<span class="navbar-toggler-icon"></span>'.PHP_EOL;
		$Retorn .= '	</button>'.PHP_EOL;
		$Retorn .= '	<div class="collapse navbar-collapse">'.PHP_EOL;
		$Retorn .= '		<ul class="navbar-nav mr-auto">'.PHP_EOL;
		$Retorn .= '			<li class="nav-item active">'.PHP_EOL;
		$Retorn .= '				<a class="nav-link" href="'.GeneraURL('Escriptori.php').'">Inici</a>'.PHP_EOL;
		$Retorn .= '			</li>'.PHP_EOL;
		if (($Usuari->es_admin) || ($Usuari->es_direccio) || ($Usuari->es_cap_estudis)) {
			// Menú Alumnes
			$Retorn .= Menu::Obre('Alumnes');
			$Retorn .= Menu::Opcio('Alumnes', 'UsuariRecerca.php?accio=Alumnes');
			$Retorn .= Menu::Opcio('Alumnes/pares', 'UsuariRecerca.php?accio=AlumnesPares');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Matrícules', 'UsuariRecerca.php?accio=Matricules');
			$Retorn .= Menu::Opcio('Matriculació alumnes', 'FormMatricula.php');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Orla', 'UsuariRecerca.php?accio=Orla');
			$Retorn .= Menu::Opcio('Borsa de treball', 'BorsaTreball.php');
			$Retorn .= Menu::Tanca();

			// Menú Professors
			$Retorn .= Menu::Obre('Professors');
			$Retorn .= Menu::Opcio('Professors', 'UsuariRecerca.php?accio=Professors');
			$Retorn .= Menu::Opcio('Professors per UF', 'AssignaUFs.php?accio=ProfessorsUF');
			$Retorn .= Menu::Opcio('Assignació UF', 'AssignaUFs.php?accio=GrupAssignaUF');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Tutors', 'UsuariRecerca.php?accio=Tutors');
			$Retorn .= Menu::Opcio('Equips', 'Recerca.php?accio=Equip');
			//$Retorn .= Menu::Opcio('Guàrdies', 'Guardia.php');
			$Retorn .= Menu::Tanca();

			// Menú FP
			$Retorn .= Menu::Obre('FP');
			$Retorn .= Menu::Opcio('Famílies', 'FPRecerca.php?accio=Families');
			$Retorn .= Menu::Opcio('Cicles formatius', 'FPRecerca.php?accio=CiclesFormatius');
			$Retorn .= Menu::Opcio('Mòduls professionals', 'FPRecerca.php?accio=ModulsProfessionals');
			$Retorn .= Menu::Opcio('Unitats formatives', 'FPRecerca.php?accio=UnitatsFormativesCF');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::ObreSubMenu("Plans d'estudis");
			$Retorn .= Menu::Opcio("Pla d'estudis", 'FPRecerca.php?accio=PlaEstudisCicleRecerca');
			$Retorn .= Menu::Opcio("Pla d'estudis per any", 'FPRecerca.php?accio=PlaEstudisAny');
			$Retorn .= Menu::Opcio("Pla d'estudis per cicle", 'FPRecerca.php?accio=PlaEstudisCicle');
			$Retorn .= Menu::Opcio("Unitats formatives del pla d'estudis", 'FPRecerca.php?accio=PlaEstudisUnitat');
			$Retorn .= Menu::TancaSubMenu();
			$Retorn .= Menu::ObreSubMenu('Programacions');
			$Retorn .= Menu::Opcio('Programacions didàctiques', 'FPRecerca.php?accio=ProgramacionsDidactiques');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio("Resultats d'aprenentatge (LOE)", 'FPRecerca.php?accio=ResultatsAprenentatge');
			$Retorn .= Menu::Opcio('Continguts (LOE)', 'FPRecerca.php?accio=ContingutsUF');
			$Retorn .= Menu::Opcio('Objectius i continguts (LOGSE)', 'FPRecerca.php?accio=ObjectiusContinguts');
			$Retorn .= Menu::TancaSubMenu();
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Cursos', 'Escriptori.php');
			$Retorn .= Menu::Opcio('Avaluacions', 'Recerca.php?accio=Avaluacio');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio("Borsa Treball", 'BorsaTreball.php');
			$Retorn .= Menu::Tanca();

			// Menú Centre
			$Retorn .= Menu::Obre('Centre');
			$Retorn .= Menu::Opcio('Usuaris', 'UsuariRecerca.php');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Any acadèmic', 'Recerca.php?accio=AnyAcademic');
			$Retorn .= Menu::Opcio('Festius', 'Recerca.php?accio=Festiu');
			$Retorn .= Menu::Separador();
			if ($Usuari->es_admin) {
				$Retorn .= Menu::ObreSubMenu('Material');
				$Retorn .= Menu::Opcio('Classificació', 'Recerca.php?accio=TipusMaterial');
				$Retorn .= Menu::Opcio('Material', 'Recerca.php?accio=Material');
				$Retorn .= Menu::Opcio('Imatges', 'Recerca.php?accio=ImatgeMaterial');
				$Retorn .= Menu::Opcio('Reserves', 'Recerca.php?accio=ReservaMaterial');
				$Retorn .= Menu::TancaSubMenu();
				$Retorn .= Menu::Separador();
			}
			$Retorn .= Menu::Opcio('Importa usuaris', 'ImportaUsuarisDialeg.php');
			$Retorn .= Menu::Opcio('Importa matrícules', 'ImportaMatriculaDialeg.php');
			if ($Usuari->es_admin) {
				$Retorn .= Menu::ObreSubMenu('ClickEdu');
				$Retorn .= Menu::Opcio('Alumnes', 'ClickEdu.php?accio=Alumnes');
				$Retorn .= Menu::TancaSubMenu();
			}
			$Retorn .= Menu::Tanca();

			// Menú Secretaria
			$Retorn .= Menu::Obre('Secretaria');
			$Retorn .= Menu::Opcio('Propostes matrícula', 'Recerca.php?accio=PropostaMatricula');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Preus matrícula', 'FPRecerca.php?accio=PreuMatricula');
			$Retorn .= Menu::Opcio('Bonificacions matrícula', 'FPRecerca.php?accio=BonificacioMatricula');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Documents', 'Recerca.php?accio=Document');
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
		else if ($Usuari->es_administratiu) {
			// Menú Secretaria
			$Retorn .= Menu::Obre('Secretaria');
			$Retorn .= Menu::Opcio('Matrícules', 'UsuariRecerca.php?accio=Matricules');
			$Retorn .= Menu::Opcio('Propostes matrícula', 'Recerca.php?accio=PropostaMatricula');
//			$Retorn .= Menu::Opcio('Preus matrícula', 'FPRecerca.php?accio=PreuMatricula');
//			$Retorn .= Menu::Opcio('Bonificacions matrícula', 'FPRecerca.php?accio=BonificacioMatricula');
			$Retorn .= Menu::Tanca();
		}
		$Retorn .= '		</ul>';

		// Menú usuari
		$NomComplet = utf8_encodeX(trim($Usuari->nom.' '.$Usuari->cognom1.' '.$Usuari->cognom2));
		$Retorn .= '		<ul class="navbar-nav">'.PHP_EOL;
		$Retorn .= '		  <li class="nav-item dropdown">'.PHP_EOL;
		$Retorn .= '          <a class="nav-link dropdown-toggle" href="#" id="ddUsuari" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$NomComplet.'</a>'.PHP_EOL;
		$Retorn .= '			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="ddUsuari">'.PHP_EOL;
		$Retorn .= Menu::Opcio('Canvia password', 'CanviPassword.html');
//		$Retorn .= Menu::Separador();
		if ($Usuari->es_alumne) {
			$Retorn .= Menu::Opcio('Perfil', 'Fitxa.php?accio=PerfilAlumne');
		}
		if ($Usuari->es_cap_estudis) {
			$Retorn .= Menu::Opcio('Canvia a professor', 'Canvia.php?accio=CanviaRolAProfessor');
		}
		if ($Usuari->es_admin) {
			$Retorn .= Menu::Opcio('Canvia usuari', 'Canvia.php?accio=SeleccionaUsuari');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Administra', 'Administra.php');
			$Retorn .= Menu::Opcio('Consola SQL', 'ConsolaSQL.php');
			$Retorn .= Menu::Opcio('Registres', 'Recerca.php?accio=Registre');
			// Hauria de ser un submenú de depuració, però el submenú no s'obre a l'esquerra!
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Apache error.log', 'Pagina.php?accio=ApacheErrorLog');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Quant a...', 'Pagina.php?accio=QuantA');
		}
		if (property_exists($Usuari, 'era_admin') && $Usuari->era_admin) {
			$Retorn .= Menu::Opcio('Torna a admin', 'Canvia.php?accio=TornaAAdmin');
		}
		$Retorn .= Menu::Separador();
		$Retorn .= Menu::Opcio('Surt', 'Surt.php');
		$Retorn .= Menu::Tanca();
		$Retorn .= '		</ul>'.PHP_EOL;

		$Retorn .= '	</div>'.PHP_EOL;
		$Retorn .= '</nav>'.PHP_EOL;
		$Retorn .= '<!-- FINAL Menú -->'.PHP_EOL;
		$Retorn .= PHP_EOL;
		$Retorn .= '<BR><BR>'; // Per donar espai al menú
		$Retorn .= PHP_EOL;

		return $Retorn;
	}		
}

/**
 * Classe per a la realització de menús de l'aplicació CapGest.
 */
class MenuCapGest extends Menu
{
	static public function Crea($Usuari): string {
		$Retorn = PHP_EOL;
		$Retorn .= '<!-- INICI Menú -->'.PHP_EOL;
		$Retorn .= '<nav class="navbar navbar-dark bg-dark navbar-expand-sm fixed-top">'.PHP_EOL;
		if ($Usuari->es_admin) 
			$Retorn .= '	<span class="navbar-brand">CapGest '.Config::Versio.'</span>'.PHP_EOL;
		else
			$Retorn .= '	<span class="navbar-brand">CapGest</span>'.PHP_EOL;
		$Retorn .= '	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar-collapse">'.PHP_EOL;
		$Retorn .= '		<span class="navbar-toggler-icon"></span>'.PHP_EOL;
		$Retorn .= '	</button>'.PHP_EOL;
		$Retorn .= '	<div class="collapse navbar-collapse">'.PHP_EOL;
		$Retorn .= '		<ul class="navbar-nav mr-auto">'.PHP_EOL;
		$Retorn .= '			<li class="nav-item active">'.PHP_EOL;
		$Retorn .= '				<a class="nav-link" href="'.GeneraURL('Escriptori.php').'">Inici</a>'.PHP_EOL;
		$Retorn .= '			</li>'.PHP_EOL;
		if ($Usuari->es_admin) {
			// Menú Club
			$Retorn .= Menu::Obre('Club');
			$Retorn .= Menu::Opcio('Usuaris', 'UsuariRecerca.php');
			$Retorn .= Menu::Tanca();

			// Menú Material
			$Retorn .= Menu::Obre('Material');
			$Retorn .= Menu::Opcio('Classificació', 'Recerca.php?accio=TipusMaterial');
			$Retorn .= Menu::Opcio('Material', 'Recerca.php?accio=Material');
			$Retorn .= Menu::Opcio('Imatges', 'Recerca.php?accio=ImatgeMaterial');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::ObreSubMenu('Préstec');
			$Retorn .= Menu::Opcio('Sortida material', 'Fitxa.php?accio=SortidaMaterial');
			$Retorn .= Menu::Opcio('Entrada material', 'Fitxa.php?accio=EntradaMaterial');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Històric', 'Recerca.php?accio=HistoricPrestecMaterial');
			$Retorn .= Menu::TancaSubMenu();
			$Retorn .= Menu::Opcio('Reserves', 'Recerca.php?accio=ReservaMaterial');
			$Retorn .= Menu::Tanca();
		}	
		$Retorn .= '		</ul>';

		// Menú usuari
		$NomComplet = utf8_encodeX(trim($Usuari->nom.' '.$Usuari->cognom1.' '.$Usuari->cognom2));
		$Retorn .= '		<ul class="navbar-nav">'.PHP_EOL;
		$Retorn .= '		  <li class="nav-item dropdown">'.PHP_EOL;
		$Retorn .= '          <a class="nav-link dropdown-toggle" href="#" id="ddUsuari" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$NomComplet.'</a>'.PHP_EOL;
		$Retorn .= '			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="ddUsuari">'.PHP_EOL;
		$Retorn .= Menu::Opcio('Canvia password', 'CanviPassword.html');
//		$Retorn .= Menu::Separador();
		if ($Usuari->es_alumne) {
			$Retorn .= Menu::Opcio('Perfil', 'Fitxa.php?accio=PerfilAlumne');
		}
		if ($Usuari->es_cap_estudis) {
			$Retorn .= Menu::Opcio('Canvia a professor', 'Canvia.php?accio=CanviaRolAProfessor');
		}
		if ($Usuari->es_admin) {
			$Retorn .= Menu::Opcio('Canvia usuari', 'Canvia.php?accio=SeleccionaUsuari');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Administra', 'Administra.php');
			$Retorn .= Menu::Opcio('Consola SQL', 'ConsolaSQL.php');
			$Retorn .= Menu::Opcio('Registres', 'Recerca.php?accio=Registre');
			// Hauria de ser un submenú de depuració, però el submenú no s'obre a l'esquerra!
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Apache error.log', 'Pagina.php?accio=ApacheErrorLog');
			$Retorn .= Menu::Separador();
			$Retorn .= Menu::Opcio('Quant a...', 'Pagina.php?accio=QuantA');
		}
		if (property_exists($Usuari, 'era_admin') && $Usuari->era_admin) {
			$Retorn .= Menu::Opcio('Torna a admin', 'Canvia.php?accio=TornaAAdmin');
		}
		$Retorn .= Menu::Separador();
		$Retorn .= Menu::Opcio('Surt', 'Surt.php');
		$Retorn .= Menu::Tanca();
		$Retorn .= '		</ul>'.PHP_EOL;

		$Retorn .= '	</div>'.PHP_EOL;
		$Retorn .= '</nav>'.PHP_EOL;
		$Retorn .= '<!-- FINAL Menú -->'.PHP_EOL;
		$Retorn .= PHP_EOL;
		$Retorn .= '<BR><BR>'; // Per donar espai al menú
		$Retorn .= PHP_EOL;

		return $Retorn;
	}	
}

?>