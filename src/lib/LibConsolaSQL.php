<?php

/** 
 * LibConsolaSQL.php
 *
 * Llibreria d'utilitats per a la consola SQL.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibHTML.php');


/**
 * Classe que encapsula les utilitats per al maneig de la consola SQL.
 */
class ConsolaSQL extends Form
{
	/**
	 * Indica que el resultat de la SQL té dades i per tant, es pot descarregar.
	 * @var boolean
	 */    
	private $TeResultSet = False;

	function __construct($conn = null, $user = null, $system = null) {
		// Usuaris que poden instanciar aquesta classe: admin (SU)
		Seguretat::ComprovaAccessUsuari($user, ['SU']);
		parent::__construct($conn, $user, $system);
	}	

	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, 'Consola SQL');
		echo '<script language="javascript" src="js/ConsolaSQL.js?v1.0" type="text/javascript"></script>';

		echo '<div id=area>';
		echo '<textarea id="AreaText" rows="10" style="width:100%;"s>';
		echo '</textarea>';
		echo '</div>';
		echo '<br>';

		echo $this->GeneraResultat('');
	}	

	/**
	 * Crea els botons per a la consola SQL.
	 * @param string $SQL SQL per a la descàrrega.
     * @return string HTML dels botons.
	 */
	 private function CreaBotons($SQL): string {
		$Retorn = '<table border=0 width=100%>';
		$Retorn .= '<tr>';
		$Retorn .= '<td>';
		$Retorn .= "<a href='#' class='btn btn-primary active' role='button' aria-pressed='true' name='Nom' onclick='ExecutaSQL(this)'>Executa</a>&nbsp;";
		$Retorn .= '</td>';
		$Retorn .= '<td align=right>';

		$Disabled = (!$this->TeResultSet) ? 'disabled' : '';
		$SQL = bin2hex(Encripta(TrimX($SQL)));
		$URL = GeneraURL("Descarrega.php?Accio=ExportaCSV&SQL=$SQL");
		
		$Retorn .= '<div class="btn-group" role="group">';
		$Retorn .= '    <button id="btnGroupDrop1" type="button" class="btn btn-primary active '.$Disabled.' dropdown-toggle" data-toggle="dropdown">';
		$Retorn .= '      Descarrega';
		$Retorn .= '    </button>';
		$Retorn .= '    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">';
		$Retorn .= '      <a id="DescarregaCSV" class="dropdown-item" href="'.$URL.'">CSV</a>';
		$Retorn .= '    </div>';
		$Retorn .= '  </div>';
		
		$Retorn .= '</td>';
		$Retorn .= '</tr>';
		$Retorn .= '</table>';
		
		return $Retorn;		
	}	

	/**
	 * Genera la part del resultat, inclosos els botons.
	 * @param string $SQL SQL per a la taula i la descàrrega.
	 * @return string HTML del resultat.
	 */
	 public function GeneraResultat($SQL): string {
		$Retorn = '<div id=resultat>';
		
		$Ordre = strtoupper(PrimeraParaula($SQL));
		$this->TeResultSet = ($Ordre == 'SELECT' or $Ordre =='DESCRIBE' or $Ordre =='SHOW');
		
		$Retorn .= $this->CreaBotons($SQL);
		$Retorn .= $this->GeneraTaula($SQL);
		$Retorn .= "<div id=debug></div>";		
		$Retorn .= '</div>';
		return $Retorn;		
	 }	

	 /**
	 * Genera la taula amb les dades si hi ha sentència SQL.
	 * @param string $SQL SQL per a la taula.
	 * @return string HTML de la taula.
	 */
	 private function GeneraTaula($SQL): string {
		$Retorn = '<div id=taula></div>';
		if ($SQL != '') {
			if ($this->TeResultSet) {
				$ResultSet = $this->Connexio->query($SQL);
				if ($ResultSet->num_rows > 0) {
					$Taula = "<BR><TABLE>";
					$PrimerCop = True;
					while($row = $ResultSet->fetch_assoc()) {
						$keys = array_keys($row);
						if ($PrimerCop) {
							// Capçalera de la taula
							$Taula .= "<THEAD>";
							for ($i=0; $i<count($keys); $i++) 
								$Taula .= "<TH>".$keys[$i]."</TH>";
							$Taula .= "</THEAD>";
							$PrimerCop = False;
						}
						$Taula .= "<TR>";
						for ($i=0; $i<count($keys); $i++)
							$Taula .= "<TD>".utf8_encodeX($row[$keys[$i]])."</TD>";
						$Taula .= "</TR>";
					}
					$Taula .= "</TABLE>";
				}
				$ResultSet->close();
			}
			else {	
				$Taula = '<BR>SQL executada amb èxit.';
				try {
					if (!$this->Connexio->query($SQL))
						throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
				} catch (Exception $e) {
					$Taula = "<BR><b>ERROR ExecutaSQL</b>. Causa: ".$e->getMessage();
				}	
			}			
			$Retorn = "<div id=taula>$Taula</div>";
		}
		return $Retorn;		
	 }	
}

?>