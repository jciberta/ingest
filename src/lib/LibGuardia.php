<?php

/** 
 * LibGuardia.php
 *
 * Llibreria d'utilitats per a les guàrdies.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibArray.php');
require_once(ROOT.'/lib/LibDate.php');

/**
 * Classe que encapsula les utilitats per al maneig de les guàrdies.
 */
class Guardia 
{
	/**
	* Connexió a la base de dades.
	* @access public 
	* @var object
	*/    
	public $Connexio;

	/**
	* Guàrdies classificades primer per dia i després per hora.
	* És un array 3-D, [dia][hora][professor]. L'índex del professor és el camp ordre.
	* @access private 
	* @var array
	*/    
	private $GuardiaPerDia = array();

	/**
	* Guàrdies classificades primer per hora i després per dia.
	* És un array 3-D, [hora][dia][professor]. L'índex del professor és el camp ordre.
	* @access private
	* @var object
	*/    
	private $GuardiaPerHora = array();
	
	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con) {
		$this->Connexio = $con;
		$this->Inicialitza();
		$this->Carrega();
	}	

	/**
	 * Genera la SQL per obtenir les guàrdies.
	 * @return string Sentència SQL.
	 */
	private function SQL() {
		$SQL = ' SELECT '.
			' 	DG.dia, DG.nom, DG.punter_data, BG.hora, BG.hora_inici, BG.hora_final, PG.professor_guardia_id, PG.ordre, PG.guardies, '.
			' 	U.codi AS CodiProfessor, U.nom AS NomProfessor, U.cognom1 AS Cognom1Professor, U.cognom2 AS Cognom2Professor '.
			' FROM PROFESSOR_GUARDIA PG '.
			' LEFT JOIN USUARI U ON (U.usuari_id=PG.professor_id) '.
			' LEFT JOIN BLOC_GUARDIA BG ON (BG.dia=PG.dia AND BG.hora=PG.hora) '.
			' LEFT JOIN DIA_GUARDIA DG ON (DG.dia=BG.dia) '.
			' ORDER BY DG.dia, BG.hora, PG.guardies, PG.ordre ';
		return $SQL;
//print $SQL;
    }

	/**
	 * Inicialitza els arrays GuardiaPerDia i GuardiaPerHora.
	 * @return void.
	 */
	private function Inicialitza() {
		// Inicialització GuardiaPerDia
		for ($i=1; $i<=5; $i++) {
			$this->GuardiaPerDia[$i] = array();
			for ($j=1; $j<=13; $j++) {
				$this->GuardiaPerDia[$i][$j] = array();
				$this->GuardiaPerDia[$i][$j][1] = null;
			}
		}
		// Inicialització GuardiaPerHora
		for ($i=1; $i<=13; $i++) {
			$this->GuardiaPerHora[$i] = array();
			for ($j=1; $j<=5; $j++) {
				$this->GuardiaPerHora[$i][$j] = array();
				$this->GuardiaPerHora[$i][$j][1] = new stdClass();
				$this->GuardiaPerHora[$i][$j][1]->hora = $i;
				$this->GuardiaPerHora[$i][$j][1]->hora_inici = $i.'00:00';
				$this->GuardiaPerHora[$i][$j][1]->hora_final = ($i+1).'00:00';
				$this->GuardiaPerHora[$i][$j][1]->CodiProfessor = '';
			}
		}
	}

	/**
	 * Carrega les guàrdies de la base de dades a l'estructura interna.
	 * @return void.
	 */
	private function Carrega() {
		$ResultSet = $this->Connexio->query($this->SQL());
		if ($ResultSet->num_rows > 0) {
			$i = 1;
			$HoraAnterior = '';
			while ($row = $ResultSet->fetch_object()) {
		//var_dump($row);
				$Dia = $row->dia;
				$Hora = $row->hora;
				if ($Hora != $HoraAnterior) {
					$HoraAnterior = $Hora;
					$i = 1;
				}
		//print $Hora.'<BR>';		
				$Ordre = $row->ordre;
				$this->GuardiaPerDia[$Dia][$Hora][$i] = $row; // 5 dies
				$this->GuardiaPerHora[$Hora][$Dia][$i] = $row; // 13 hores
				$i++;
			}
		}
	}

	/**
	 * Genera la taula amb les guàrdies.
	 * @return string La taula HTML.
	 */
	private function GeneraTaula() {
//		$Retorn = '<TABLE border=1>';
		$Retorn = '<TABLE class="table table-striped">';
		$Retorn .= '<THEAD class="thead-dark">';
		$Retorn .= '<TH></TH>';
		$Retorn .= '<TH style="text-align:center">Dilluns</TH>';
		$Retorn .= '<TH style="text-align:center">Dimarts</TH>';
		$Retorn .= '<TH style="text-align:center">Dimecres</TH>';
		$Retorn .= '<TH style="text-align:center">Dijous</TH>';
		$Retorn .= '<TH style="text-align:center">Divendres</TH>';
		$Retorn .= '</THEAD>';
		$HoraAnterior = -1;
		for ($i=1; $i<=13; $i++) {
			$ProfessorsHora = max(count($this->GuardiaPerHora[$i][1]), count($this->GuardiaPerHora[$i][2]), count($this->GuardiaPerHora[$i][3]), count($this->GuardiaPerHora[$i][4]), count($this->GuardiaPerHora[$i][5]));
//print '$i='.$i.', $ProfessorsHora='.$ProfessorsHora.'<BR>';
			for ($j=1; $j<=$ProfessorsHora; $j++) {
	//var_dump($GuardiaPerHora[$i][1][$j]);
				$Retorn .= '<TR>';
				if ($this->GuardiaPerHora[$i][1][1]->hora != $HoraAnterior) {
					// Estil extret de bootstrap.css .table .thead-dark th
					$Retorn .= '<TD rowspan='.$ProfessorsHora.' style="background-color:#212529;color:#fff;border-color:#32383e;text-align:center">';
					$Retorn .= '<B>'.$this->GuardiaPerHora[$i][1][1]->hora.'</B><BR>';
					$Retorn .= $this->GuardiaPerHora[$i][1][1]->hora_inici.'<BR>';
					$Retorn .= $this->GuardiaPerHora[$i][1][1]->hora_final;
					$Retorn .= '</TD>';
					$HoraAnterior = $this->GuardiaPerHora[$i][1][1]->hora;
				}

				if (count($this->GuardiaPerHora[$i][1])>=$j) 
					$Retorn .= '<TD style="text-align:center">'.$this->GuardiaPerHora[$i][1][$j]->CodiProfessor.'</TD>';
				else
					$Retorn .= '<TD></TD>';
				if (count($this->GuardiaPerHora[$i][2])>=$j) 
					$Retorn .= '<TD style="text-align:center">'.$this->GuardiaPerHora[$i][2][$j]->CodiProfessor.'</TD>';
				else
					$Retorn .= '<TD></TD>';
				if (count($this->GuardiaPerHora[$i][3])>=$j) 
					$Retorn .= '<TD style="text-align:center">'.$this->GuardiaPerHora[$i][3][$j]->CodiProfessor.'</TD>';
				else
					$Retorn .= '<TD></TD>';
				if (count($this->GuardiaPerHora[$i][4])>=$j) 
					$Retorn .= '<TD style="text-align:center">'.$this->GuardiaPerHora[$i][4][$j]->CodiProfessor.'</TD>';
				else
					$Retorn .= '<TD></TD>';
				if (count($this->GuardiaPerHora[$i][5])>=$j) 
					$Retorn .= '<TD style="text-align:center">'.$this->GuardiaPerHora[$i][5][$j]->CodiProfessor.'</TD>';
				else
					$Retorn .= '<TD></TD>';

				$Retorn .= '</TR>';
			}
			$Retorn .= '</TR>';
		}
		$Retorn .= '</TABLE>';	
		return 	$Retorn;
	}

	/**
	 * Genera la taula amb les guàrdies per a un dia.
	 * @param integer $Dia Dia de la setmana. 
	 * @param boolean $Recarrega Si cert torna a carregar les dades de la base de dades. 
	 * @param array $Previa Array amb la nova configuració de guàrdies. 
	 * @return string La taula HTML.
	 */
	public function GeneraTaulaDia($Dia, $Recarrega = False, $Previa = []) {
		if ($Recarrega) {
			$this->Inicialitza();
			$this->Carrega();
		}
		$bPrevia = ($Previa != []);
//print_r($Previa);
//print '$this->GuardiaPerDia[$Dia][1][1]: '.$this->GuardiaPerDia[$Dia][1][1];
//var_dump($this->GuardiaPerDia[$Dia][1][1]);
//exit;
		$Retorn = DiaSetmana($this->GuardiaPerDia[$Dia][1][1]->punter_data).' '.MySQLAData($this->GuardiaPerDia[$Dia][1][1]->punter_data);
		$Retorn .= '<TABLE border=1>';
		$Retorn .= '<TR><TD colspan=2></TD><TD colspan=6>Signatures</TD></TR>';
		for ($i=1; $i<=7; $i++) {
			// Inicialitzem llista de professors (màxim 6)
			for ($j=1; $j<=6; $j++)
				$aProfessorsCodi[$j] = '';
			$Retorn .= '<TR>';
			// Bloc horari
			$Retorn .= '<TD style="width:100px">';
			$Retorn .= '<B>HORA '.$this->GuardiaPerHora[$i][1][1]->hora.'</B><BR>';
			$Retorn .= substr($this->GuardiaPerHora[$i][1][1]->hora_inici, 0, 5).'-<BR>';
			$Retorn .= substr($this->GuardiaPerHora[$i][1][1]->hora_final, 0, 5);
			$Retorn .= '</TD>';
			// Professors ordenats segons criteri
			$Retorn .= '<TD style="width:175px">';
			$aProfessors = [];
			$aID = [];
			for ($j=1; $j<=count($this->GuardiaPerDia[$Dia][$i]); $j++) {
				$aProfessorsCodi[$j] = $this->GuardiaPerDia[$Dia][$i][$j]->CodiProfessor;
				if ($i == 4)
					$aProfessors[$j] = $aProfessorsCodi[$j];
				else {
					$aProfessors[$j] = $j.' '.$aProfessorsCodi[$j].' ('.$this->GuardiaPerDia[$Dia][$i][$j]->guardies.')';
					if ($bPrevia)
						$aProfessors[$j] .= ' -> '.$Previa[$i][$j]->CodiProfessor.' ('.$Previa[$i][$j]->guardies.')';
//					$aProfessors[$j] = $this->GuardiaPerDia[$Dia][$i][$j]->ordre.' '.$aProfessorsCodi[$j].' - '.$this->GuardiaPerDia[$Dia][$i][$j]->guardies;
					$aID[$j] = $this->GuardiaPerDia[$Dia][$i][$j]->professor_guardia_id;
				}
			}
			$Retorn .= implode('<BR>', $aProfessors);
			$Retorn .= '</TD>';
			if ($i == 4)
				$Retorn .= '<TD colspan=6></TD>';
			else
				// Signatures de professors
				for ($j=1; $j<=6; $j++) {
//					$sText = ($aProfessorsCodi[$j]=='') ? '' : '<input type="checkbox" id="pg_'.$aID[$j].'" name="guardia_professor">'.$aProfessorsCodi[$j];
					$sText = ($aProfessorsCodi[$j]=='') ? '' : '<input type="checkbox" name="pg_'.$aID[$j].'">'.$aProfessorsCodi[$j];
					$Retorn .= '<TD style="width:100px;vertical-align:bottom">'.$sText.'</TD>';
				}
			$Retorn .= '</TR>';
		}
		$Retorn .= '</TABLE>';	
		return 	$Retorn;
	}
	
	/**
	 * Escriu la taula amb les guàrdies.
	 * @param integer $Dia Dia de la setmana. Si és 0 mostra la setmana sencera.
	 * @return void.
	 */
	public function EscriuTaula($Dia = 0) {
		if ($Dia == 0)
			echo $this->GeneraTaula();
		else
			echo $this->GeneraTaulaDia($Dia);
	}
	
	/**
	 * Genera el formulari per fer recerques.
	 * @return void.
	 */
	public function CreaBotoGeneraProperDia($Dia) {
		$dowMap = array('Diumenge', 'Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres', 'Dissabte');
		$DiaSetmana = strtolower($dowMap[$Dia]);
		$sRetorn = '<DIV id=ProperDia style="padding:10px">';
		$sRetorn .= '  <FORM class="form-inline my-2 my-lg-0" id=frm method="post" action="">';
		$sRetorn .= '    <a class="btn btn-primary active" role="button" aria-pressed="true" id="btnGeneraProperDia" name="btnGeneraProperDia" onclick="GeneraProperDia(this, '.$Dia.');">Genera proper '.$DiaSetmana.'</a>';
		$sRetorn .= '  </FORM>';
		$sRetorn .= '</DIV>';
		return $sRetorn;
	}

	/**
	 * Genera una prèvia de les guàrdies del proper dia.
	 * Consisteix en rodar l'ordre i incrementar les guàrdies.
	 * @param integer $Dia Dia de la setmana. 
	 * @param string $Guardies Llista de id de la taula PROFESSOR_GUARDIA que s'ha d'incrementar la guàrdia en 1.
	 * @return array Array d'hores amb les guàrdies dels professors.
	 */
	public function GeneraProperDiaPrevia($Dia, $Guardies) {
		$Previa = [];
		$aGuardies = explode(',', $Guardies);
		for ($i=1; $i<=7; $i++) {
			if ($i !=4)
				$Previa[$i] = $this->IncrementaIRodaGuardia($Dia, $i, $aGuardies);
		}
		return $Previa;
	}

	/**
	 * Genera el proper dia.
	 * Consisteix en rodar l'ordre i incrementar les guàrdies.
	 * @param integer $Dia Dia de la setmana. 
	 * @param string $Guardies Llista de id de la taula PROFESSOR_GUARDIA que s'ha d'incrementar la guàrdia en 1.
	 * @return void.
	 */
	public function GeneraProperDia($Dia, $Guardies) {
		// S'ha d'executar de forma atòmica
		$this->Connexio->query('START TRANSACTION');
		try {
			$aGuardies = explode(',', $Guardies);
			for ($i=1; $i<=7; $i++) {
				if ($i !=4)
					$this->IncrementaIRodaGuardia($Dia, $i, $aGuardies);
			}
			//$this->RodaGuardies($Dia);
			//$this->IncrementaGuardies($Guardies);
			$this->Connexio->query('COMMIT');
		} catch (Exception $e) {
			$this->Connexio->query('ROLLBACK');
			die("ERROR GeneraProperDia. Causa: ".$e->getMessage());
		}		
	}

	/**
	 * Roda les guàrdies d'un dia.
	 * Incrementa el camp ordre i el més alt el posa a 0.
	 * @param integer $Dia Dia de la setmana. 
	 * @param integer $Hora Bloc horari. 
	 * @param array $aGuardies Llista de id de la taula PROFESSOR_GUARDIA que s'ha d'incrementar la guàrdia en 1.
	 * @return void.
	 */
	public function IncrementaIRodaGuardia($Dia, $Hora, $aGuardies) {
		$BlocHora = $this->GuardiaPerDia[$Dia][$Hora];

		// Incrementem les guardies que han fet els professors
		for ($i=1; $i<=count($BlocHora); $i++) {
			if (in_array($BlocHora[$i]->professor_guardia_id, $aGuardies)) {
				$BlocHora[$i]->guardies++;
			}
		}
//var_dump($BlocHora);
//print_r($BlocHora);
//print '<p>';
		// Clonem l'array d'objectes
		$NouBlocHora = $BlocHora;
//		$NouBlocHora = new ArrayObject($BlocHora);
//		$NouBlocHora = $BlocHora->getArrayCopy();

//$this->EscriuBloc($NouBlocHora);
		// Movem el professor fins al final del grup que tenen el mateix nombre guàrdies
		// NOTA: recorrem l'array original (BlocHora), NO el nou (NouBlocHora)
		for ($i=1; $i<=count($BlocHora); $i++) {
//			print $i.' : ';
//			print_r($BlocHora[$i]);
//			print '<br>';
			
			if (in_array($BlocHora[$i]->professor_guardia_id, $aGuardies)) {
				$this->MouProfessorGuardia($NouBlocHora, $BlocHora[$i]->professor_guardia_id, $BlocHora[$i]->guardies);
			}
		}
//$this->EscriuBloc($NouBlocHora);
		return $NouBlocHora;
	}

	private function MouProfessorGuardia(&$bh, $id, $NumGuardies) {
		$aRetorn = [];
		
		$Origen = -1;
		for ($i=1; $i<=count($bh); $i++) {
			if ($bh[$i]->professor_guardia_id == $id) 
				$Origen = $i;
		}	
		
		$Desti = count($bh);
		for ($i=count($bh); $i<0; $i--) {
			if ($bh[$i]->guardies > $NumGuardies) 
				$Desti = $i;
		}	
//print $Origen.' '.$Desti.'<br>';
//$this->EscriuBloc($bh);
		
		$Element = $bh[$Origen];
		// Fem primer l'inserta ja que el destí serà més gran que l'origen, i així no s'alteren els índexs
		InsertaEnArray($bh, $Element, $Desti);
//$this->EscriuBloc($bh);
		EliminaEnArray($bh, $Origen);

		// Posem el número d'ordre
		for ($i=1; $i<=count($bh); $i++) 
			$bh[$i]->ordre = $i;
	}

	/**
	 * Per propòsit de depuració.
	 */
	private	function EscriuBloc($Bloc)
	{
		for ($i=1; $i<=count($Bloc); $i++) {
			print $i.' : ';
			print_r($Bloc[$i]);
			print '<br>';
		}
		print '<br>';
		//var_dump($Bloc);
	}
		
	/**
	 * Roda les guàrdies d'un dia.
	 * Incrementa el camp ordre i el més alt el posa a 0.
	 * @param integer $Dia Dia de la setmana. 
	 * @return void.
	 * @deprecated
	 */
	public function RodaGuardies($Dia) {
		// Increment del camp ordre
		$SQL = ' UPDATE PROFESSOR_GUARDIA SET ordre=ordre+1 WHERE dia='.$Dia;
		if (!$this->Connexio->query($SQL))
			throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
		
		// Llista d'Id de professors amb l'ordre més alt per a cada bloc d'un dia determinat
		// https://stackoverflow.com/questions/22221925/get-id-of-max-value-in-group
		$SQLProfessorsOrdreMesAlt = ' SELECT professor_guardia_id '.
		' FROM PROFESSOR_GUARDIA PG '.
		' JOIN '.
		' (SELECT '. 
		'     dia, hora, max(ordre) AS ordre '.
		' FROM PROFESSOR_GUARDIA '.
		' WHERE dia='.$Dia.
		' GROUP BY dia, hora) PGO '.
		' WHERE PG.dia=PGO.dia AND PG.hora=PGO.hora AND PG.ordre=PGO.ordre ';
		// No es pot un UPDATE d'una taula de la qual fas un SELECT (a MySQL)
		// https://stackoverflow.com/questions/45494/mysql-error-1093-cant-specify-target-table-for-update-in-from-clause
		// Solució: Incloure la SELECT dins d'una altra SELECT
		$SQLWrapper = ' SELECT professor_guardia_id FROM ('.$SQLProfessorsOrdreMesAlt.') AS Wrapper ';
		$SQL = ' UPDATE PROFESSOR_GUARDIA SET ordre=1 WHERE professor_guardia_id IN ('.$SQLWrapper.')';
		if (!$this->Connexio->query($SQL))
			throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
	}

	/**
	 * Incrementa les guàrdies dels professors.
	 * @param string $Guardies Llista de id de la taula PROFESSOR_GUARDIA que s'ha d'incrementar la guàrdia en 1.
	 * @return void.
	 * @deprecated
	 */
	public function IncrementaGuardies($Guardies) {
		if ($Guardies != '') {
			$SQL = ' UPDATE PROFESSOR_GUARDIA SET guardies=guardies+1 WHERE professor_guardia_id IN ('.$Guardies.') ';
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
		}
	}
}

?>
