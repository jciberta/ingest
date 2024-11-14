<?php

/** 
 * LibSQL.php
 *
 * Llibreria per a la manipulació de SQL.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT . '/lib/LibStr.php');

use PHPSQLParser\PHPSQLParser;

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
class SQL
{
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
	function __construct($sSQL)
	{
		$this->SQL = TrimX($sSQL);
		if (substr(strtoupper($this->SQL), 0, 6) != "SELECT")
			throw new Exception("La classe SQL només manipula SELECT.");

		$this->SQL = str_replace(array("\t"), array(" "), $this->SQL);
		$this->SQL = str_replace(array("\n"), array(" "), $this->SQL);

		$this->Parteix();
	}


	/**
	 * Genera la SQL a partir de les parts SELECT, FROM, WHERE, GROUP BY, HAVING i ORDER.
	 * @return string.
	 */
	public function GeneraSQL()
	{
		$sRetorn = ' SELECT ' . $this->Select;
		if ($this->From != '')
			$sRetorn .= ' FROM ' . $this->From;
		if ($this->Where != '')
			$sRetorn .= ' WHERE ' . $this->Where;
		if ($this->Group != '')
			$sRetorn .= ' GROUP BY ' . $this->Group;
		if ($this->Having != '')
			$sRetorn .= ' HAVING ' . $this->Having;
		if ($this->Order != '')
			$sRetorn .= ' ORDER BY ' . $this->Order;
		return $sRetorn;
	}

	/**
	 * Crea l'array associatiu dels àlies dels camps.
	 * @return void.
	 */
	private function CreaCampAlies()
	{
		$this->AliesCamp = array();
		//print('<hr>');
		//print($this->Select);
		//print('<hr>');
		// $aCamps = explode(',', $this->Select); -> No funciona com a parser, separa també les comes de dins les funcions
		$Select = $this->Select;
		$aCamps = [];
		$s = '';
		$i = 0;
		while ($i < strlen($Select)) {
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
			} else
				$s .= $Select[$i];
			$i++;
		}
		array_push($aCamps, $s);

		//print_h($aCamps);
		//print('hr');
		foreach ($aCamps as $data) {
			$i = strpos(strtoupper($data), ' AS ');
			if ($i != 0)
				$this->AliesCamp[trim(substr($data, $i + 4))] = trim(substr($data, 0, $i));
		}
	}

	/**
	 * Obté el nom del camp en el cas que es tracti d'un àlies, sinó retorna el mateix valor.
	 * MySQL (i altres DB) no deixen posar àlies a la clàusula WHERE.
	 * @param string $alies Possible àlies.
	 * @return string Nom del camp.
	 */
	public function ObteCampDesDeAlies($alies): string
	{
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
	static public function CreaCase(string $Camp, array $Taula): string
	{
		$Retorn = " CASE " . $Camp;
		foreach ($Taula as $clau => $valor) {
			$valor = str_replace("'", "\'", $valor);
			$Retorn .= " WHEN '$clau' THEN '$valor'";
		}
		$Retorn .= " END";
		return $Retorn;
	}
	/**Codi de prova generat per chatgpt
	 * 
	 */
	private function Parteix()
	{
		$parser = new PHPSQLParser($this->SQL);
//echo "".$this->SQL."<br>";

		// Obtener el array con las partes de la consulta
		$partsConsulta = array(
			'SELECT' => isset($parser->parsed['SELECT']) ? $parser->parsed['SELECT'] : array(),
			'FROM' => isset($parser->parsed['FROM']) ? $parser->parsed['FROM'] : array(),
			'WHERE' => isset($parser->parsed['WHERE']) ? $parser->parsed['WHERE'] : array(),
			'GROUP' => isset($parser->parsed['GROUP']) ? $parser->parsed['GROUP'] : array(),
			'HAVING' => isset($parser->parsed['HAVING']) ? $parser->parsed['HAVING'] : array(),
			'ORDER' => isset($parser->parsed['ORDER']) ? $parser->parsed['ORDER'] : array()
		);
//print_h($partsConsulta);		

		// Procesar la cláusula SELECT
		$SELECT = '';
		foreach ($partsConsulta['SELECT'] as $v) {
			if ($v['expr_type'] === 'colref' || $v['expr_type'] === 'alias') {
				// Si es una referència de columna o un alies, simplement l'agreguem al SELECT
				$SELECT .= $v['base_expr'] . (isset($v['alias']['name']) ? ' AS ' . $v['alias']['name'] : '') . ', ';
			} elseif ($v['expr_type'] === 'function' || $v['expr_type'] === 'aggregate_function') {
				// Si es una funció, la procesem
				$funcName = $v['base_expr'];
				$funcArgs = '';
				if (isset($v['sub_tree']) && is_array($v['sub_tree'])) {
					$argParts = [];
					foreach ($v['sub_tree'] as $sub_v) {
						$argParts[] = $sub_v['base_expr'];
					}
					$funcArgs = implode(', ', $argParts);
				}
				$SELECT .= $funcName . '(' . $funcArgs . ')' . (isset($v['alias']['name']) ? ' AS ' . $v['alias']['name'] : '') . ', ';
			} elseif ($v['expr_type'] === 'reserved' && strtoupper($v['base_expr']) === 'CASE') {
				// Si és l'inici d'un CASE, agreguem l'expresió CASE
				$SELECT .= 'CASE ';
			} elseif ($v['expr_type'] === 'reserved' && strtoupper($v['base_expr']) === 'END') {
				// Si és el final d'un CASE, agreguem l'alies només una vegada
				$SELECT = rtrim($SELECT, ', ');
				$SELECT .= ' END' . (isset($v['alias']['name']) ? ' AS ' . $v['alias']['name'] : '') . ', ';
			} else {
				// En altres casos, simplement agreguem l'expresió al SELECT
				$SELECT .= $v['base_expr'] . ', ';
			}
		}
		$this->Select = rtrim($SELECT, ', ');
//echo "".$this->Select."<br>";

		// Procesar la cláusula FROM
		$FROM = '';
		foreach ($partsConsulta['FROM'] as $k => $v) {
			switch ($v['join_type']) {
				case 'JOIN':
					$FROM .= 'JOIN ';
					break;
				case 'LEFT':
					$FROM .= 'LEFT JOIN ';
					break;
				case 'RIGHT':
					$FROM .= 'RIGHT JOIN ';
					break;
			}
			$FROM .= print_r($v['base_expr'], true) . ' ';
		}
		if (substr($FROM, 0, 5) === 'JOIN ') {
			$this->From = substr($FROM, 5);
		} else {
			$this->From = $FROM;
		}

		// Procesar la cláusula WHERE
		if (!empty($partsConsulta['WHERE'])) {
			$WHERE = '';
			foreach ($partsConsulta['WHERE'] as $v) {
				$WHERE .= $v['base_expr'] . ' ';
			}
			$this->Where = trim($WHERE);
		} else {
			$this->Where = '';
		}

		// Procesar la clàusula GROUP
		$GROUP = '';
		if (!empty($partsConsulta['GROUP'])) {
			foreach ($partsConsulta['GROUP'] as $k => $v) {
				$GROUP .= print_r($v['base_expr'], true) . ', ';
			}
			$this->Group = rtrim($GROUP, ', ');
		} else {
			$this->Group = '';
		}
		// Procesar la clàusula HAVING
		$HAVING = '';
		if (!empty($partsConsulta['HAVING'])) {

			foreach ($partsConsulta['HAVING'] as $k => $v) {
				switch ($v['expr_type']) {
					case 'aggregate_function':
						// Si es una funció d'agregació, agreguem el nom de la funció
						$HAVING .= $v['base_expr'] . '(';
						// Luego, procesamos los argumentos de la función
						foreach ($v['sub_tree'] as $s) {
							$HAVING .= $s['base_expr'];
						}
						$HAVING .= ') ';
						break;
					case 'operator':
						// Si es un operador, simplement l'agreguem a la clàusula
						$HAVING .= $v['base_expr'] . ' ';
						break;
					case 'const':
						// Si és una constant, l'agreguem a la clàusula
						$HAVING .= $v['base_expr'] . ' ';
						break;
					case 'alias':
						// Si es un àlies, probablement sigui una columna calculada
						$HAVING .= $v['no_quotes']['parts'][0] . ' ';
						break;
				}
			}
			$this->Having = $HAVING;
		} else {
			$this->Having = '';
		}

		// Procesar la cláusula ORDER BY
		$ORDER = '';
		if (!empty($partsConsulta['ORDER'])) {
			foreach ($partsConsulta['ORDER'] as $v) {
				$ORDER .= $v['base_expr'] . ', ';
			}
			$this->Order = rtrim($ORDER, ', ');
		} else {
			$this->Order = '';
		}


		$this->CreaCampAlies();
	}
}
