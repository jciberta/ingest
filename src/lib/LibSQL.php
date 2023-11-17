<?php

/** 
 * LibSQL.php
 *
 * Llibreria per a la manipulació de SQL.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibStr.php');

/**
 * Classe SQL.
 *
 * Classe per a la manipulació de SQL. Només manipula SELECT. Sintaxi:
 *
 *	SELECT column_name(s)
 *	FROM table_name
 *	WHERE condition
 *	GROUP BY column_name(s)
 *	HAVING condition
 *	ORDER BY column_name(s)
 */
class SQL {
	/**
	* Sentència SQL.
	* @access protected 
	* @var string
	*/    
	protected $SQL = '';
	
	/**
	* Part SELECT de la sentència SQL.
	* @access public 
	* @var string
	*/    
	public $Select = '';
	
	/**
	* Part FROM de la sentència SQL.
	* @access public 
	* @var string
	*/    
	public $From = '';
	
	/**
	* Part WHERE de la sentència SQL.
	* @access public 
	* @var string
	*/    
	public $Where = '';
	
	/**
	* Part ORDER de la sentència SQL.
	* @access public 
	* @var string
	*/    
	public $Order = '';

	/**
	* Part GROUP de la sentència SQL.
	* @access public 
	* @var string
	*/    
	public $Group = '';

	/**
	* Part HAVING de la sentència SQL.
	* @access public 
	* @var string
	*/    
	public $Having = '';
	
	/**
	* Array associatiu dels àlies dels camps. La clau és l'àlies i el valor és el nom del camp.
	* @access public 
	* @var array
	*/    
	public $AliesCamp = [];

	/**
	 * Constructor de l'objecte.
	 * @param string $sSQL Sentència SQL.
	 */
	function __construct($sSQL) {
		$this->SQL = TrimX($sSQL);
		if (substr(strtoupper($this->SQL), 0, 6) != "SELECT")
			throw new Exception("La classe SQL només manipula SELECT.");
		
		$this->SQL = str_replace(array("\t"), array(" "), $this->SQL);
		$this->SQL = str_replace(array("\n"), array(" "), $this->SQL);
		
		$this->Parteix();
	}	

	/**
	 * Parteix la SQL en les parts SELECT, FROM, WHERE i ORDER.
     * @return void.
	 */
	private function Parteix() {
		$SQL = $this->SQL;
		$SQLMaj = strtoupper($SQL);
		$iLon = strlen($SQLMaj);
		$iFrom = strpos($SQLMaj, ' FROM ');
		$iWhere = strrpos($SQLMaj, ' WHERE '); // L'últim WHERE
		$iGroup = strrpos($SQLMaj, ' GROUP '); // L'últim GROUP
		$iHaving = strrpos($SQLMaj, ' HAVING '); // L'últim HAVING
		$iOrder = strrpos($SQLMaj, ' ORDER '); // L'últim ORDER
		if ($iOrder < $iWhere) 
			$iOrder = 0;

		// Treiem la part de l'ORDER
		if ($iOrder != 0) {
			$this->Order = trim(substr($SQL, $iOrder + 9, strlen($SQL) - $iOrder));
			$SQL = trim(substr($SQL, 0, $iOrder));
		}

		// Treiem la part del HAVING
		if ($iHaving != 0) {
			$this->Having = trim(substr($SQL, $iHaving + 8, strlen($SQL) - $iHaving));
			$SQL = trim(substr($SQL, 0, $iHaving));
		}

		// Treiem la part del GROUP
		if ($iGroup != 0) {
			$this->Group = trim(substr($SQL, $iGroup + 9, strlen($SQL) - $iGroup));
			$SQL = trim(substr($SQL, 0, $iGroup));
		}

		if ($iFrom == 0) {
			// No hi ha FROM
			$this->Select = trim(substr($SQL, 6));
		}
		else {
			$this->Select = substr($SQL, 0, $iFrom);
//print('<hr>');
//print($SQL);
//print($this->Select);
//print('<hr>');
			$this->Select = trim(substr($this->Select, 6));
			$iWhere = strpos($SQL, ' WHERE ');
			if ($iWhere == 0) {
				// No hi ha WHERE
				$this->From = trim(substr($SQL, $iFrom));
				$this->From = trim(substr($this->From, 4));
			}
			else {
				$this->From = trim(substr($SQL, $iFrom, $iWhere - $iFrom));
				$this->From = trim(substr($this->From, 4));
				$this->Where = trim(substr($SQL, $iWhere));
				$this->Where = trim(substr($this->Where, 5));
			}
		}
		$this->CreaCampAlies();
/*
print('<hr>');
print('<b>SELECT</b>: '.$this->Select.'<br>');
print('<b>FROM</b>:   '.$this->From.'<br>');
print('<b>WHERE</b>:  '.$this->Where.'<br>');
print('<b>GROUP</b>:  '.$this->Group.'<br>');
print('<b>HAVING</b>:  '.$this->Having.'<br>');
print('<b>ORDER</b>:  '.$this->Order.'<br>');
print('<hr>');
*/
	}

	/**
	 * Genera la SQL a partir de les parts SELECT, FROM, WHERE, GROUP BY, HAVING i ORDER.
     * @return string.
	 */
	public function GeneraSQL() {
		$sRetorn = ' SELECT '.$this->Select;
		if ($this->From != '')
			$sRetorn .= ' FROM '.$this->From;
		if ($this->Where != '')
			$sRetorn .= ' WHERE '.$this->Where;
		if ($this->Group != '')
			$sRetorn .= ' GROUP BY '.$this->Group;
		if ($this->Having != '')
			$sRetorn .= ' HAVING '.$this->Having;
		if ($this->Order != '')
			$sRetorn .= ' ORDER BY '.$this->Order;
		return $sRetorn;
	}
	
	/**
	 * Crea l'array associatiu dels àlies dels camps.
     * @return void.
	 */
	private function CreaCampAlies() {
		$this->AliesCamp = array();
//print('<hr>');
//print($this->Select);
//print('<hr>');
		// $aCamps = explode(',', $this->Select); -> No funciona com a parser, separa també les comes de dins les funcions
		$Select = $this->Select;
		$aCamps = [];
		$s = '';
		$i = 0;
		while  ($i < strlen($Select)) {
			if ($Select[$i] == '(') {
				// Incrementem el punter fins al següent )
				// FALTA: aniuament de parèntesi
				$s .= $Select[$i];
				$i++;
				while ($i < strlen($Select) && $Select[$i] != ')') {
					$s .= $Select[$i];
					$i++;
				}
			}
			if ($Select[$i] == ',') {
				array_push($aCamps, $s);
				$s = '';
			}
			else
				$s .= $Select[$i];
			$i++;
		}
		array_push($aCamps, $s);

//print_h($aCamps);
//print('hr');
		foreach ($aCamps as $data) {
			$i = strpos(strtoupper($data), ' AS ');
			if ($i != 0)
				$this->AliesCamp[trim(substr($data, $i+4))] = trim(substr($data, 0, $i));
		}
	}

	/**
	 * Obté el nom del camp en el cas que es tracti d'un àlies, sinó retorna el mateix valor.
	 * MySQL (i altres DB) no deixen posar àlies a la clàusula WHERE.
	 * @param string $alies Possible àlies.
     * @return string Nom del camp.
	 */
	public function ObteCampDesDeAlies($alies): string {
		$Retorn = $alies;
		foreach ($this->AliesCamp as $key => $value) {
			if ($key == $alies)
				if (substr($value, 0, 4) != 'CASE')
				$Retorn = $value;
		}
		return $Retorn;
	}
	
	/**
	 * Crea la part de sentència SQL per a un CASE a partir d'un array associatiu.
	 * @param string $Camp Camp.
	 * @param array $Taula Array associatiu amb els valors.
     * @return string Sentència CASE.
	 */
	static public function CreaCase(string $Camp, array $Taula): string {
		$Retorn = " CASE ".$Camp;
		foreach ($Taula as $clau => $valor) {
			$valor = str_replace("'", "\'", $valor);
			$Retorn .= " WHEN '$clau' THEN '$valor'";
		}		
		$Retorn .= " END";
		return $Retorn;
	}
}
