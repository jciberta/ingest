<?php

/** 
 * LibSQL.php
 *
 * Llibreria per a la manipulació de SQL.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 * @version 1.0
 */

require_once(ROOT.'/lib/LibStr.php');

/**
 * Classe SQL.
 *
 * Classe per a la manipulació de SQL. Només manipula SELECT.
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
	* Array associatiu dels àlies dels camps. La clau és l'àlies i el valor és el nom del camp.
	* @access public 
	* @var array
	*/    
	public $AliesCamp = [];

	/**
	 * Constructor de l'objecte.
	 *
	 * @param string $sSQL Sentència SQL.
	 */
	function __construct($sSQL) {
		$this->SQL = TrimX($sSQL);
		if (substr(strtoupper($this->SQL), 0, 6) != "SELECT")
			throw new Exception("La classe SQL només manipula SELECT.");
		$this->Parteix();
	}	

	/**
	 * Parteix la SQL en les parts SELECT, FROM i WHERE.
     *
     * @return void.
	 */
	private function Parteix() {
		$SQLMaj = strtoupper($this->SQL);
		$iLon = strlen($SQLMaj);
		$iFrom = strpos($SQLMaj, ' FROM ');
		$iWhere = strrpos($SQLMaj, ' WHERE '); // L'últim WHERE

		if ($iFrom == 0) {
			// No hi ha FROM
			$this->Select = trim(substr($this->SQL, 6));
		}
		else {
			$this->Select = substr($this->SQL, 0, $iFrom);
			$this->Select = trim(substr($this->Select, 6));
			if ($iWhere == 0) {
				// No hi ha WHERE
				$this->From = trim(substr($this->SQL, $iFrom));
				$this->From = trim(substr($this->From, 4));
			}
			else {
				$this->From = trim(substr($this->SQL, $iFrom, $iWhere - $iFrom));
				$this->From = trim(substr($this->From, 4));
				$this->Where = trim(substr($this->SQL, $iWhere));
				$this->Where = trim(substr($this->Where, 5));
			}
		}
		$this->CreaCampAlies();
	}

	/**
	 * Crea l'array associatiu dels àlies dels camps.
     *
     * @return void.
	 */
	private function CreaCampAlies() {
		$this->CampAlies = array();
		$aCamps = explode(',', $this->Select);
		foreach ($aCamps as $data) {
			$i = strpos(strtoupper($data), ' AS ');
			if ($i != 0)
				$this->CampAlies[trim(substr($data, $i+4))] = trim(substr($data, 0, $i));
		}
	}

}

?>

