<?php

/** 
 * LibDB.php
 *
 * Llibreria d'utilitats per a base de dades.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibStr.php');

/**
 * ComprovaFortalesaPassword
 *
 * Comprova la fortalesa d'un password.
 * https://pages.nist.gov/800-63-3/sp800-63b.html
 *
 * @param string $pwd Password a comprovar.
 * @param array $errors Errors de fortalesa.
 * @return boolean Cert si supera la fortalesa exigida.
 */
function ComprovaFortalesaPassword($pwd, &$errors) {
    $errors_init = $errors;
    if (strlen($pwd) < 8) {
        $errors[] = "Password too short!";
    }
    if (!preg_match("#[0-9]+#", $pwd)) {
        $errors[] = "Password must include at least one number!";
    }
    if (!preg_match("#[a-zA-Z]+#", $pwd)) {
        $errors[] = "Password must include at least one letter!";
    }     
    return ($errors == $errors_init);
}

/**
 * ObteCodiValorDesDeSQL
 *
 * Obté un array que conté 2 arrays (parell codi-valor) a partir d'una SQL.
 *
 * @param object $Connexio Connexió a la base de dades.
 * @param string $SQL Sentència SQL.
 * @param array $CampCodi Nom del camp del codi.
 * @param array $CampValor Nom del camp del valor.
 * @return void Array que conté 2 arrays (parell codi-valor).
 */
function ObteCodiValorDesDeSQL($Connexio, $SQL, $CampCodi, $CampValor)
{
	$Codi = array();
	$Valor = array();	
	$ResultSet = $Connexio->query($SQL);
	if ($ResultSet->num_rows > 0) {
		$i = 0;
		while($row = $ResultSet->fetch_assoc()) {
			$Codi[$i] = $row[$CampCodi];
//			$Valor[$i] = utf8_encodeX($row[$CampValor]);
			$Valor[$i] = CodificaUTF8($row[$CampValor]);
			$i++;
		}
	};	
	$ResultSet->close();
//	print_r($Codi);
//	print_r($Valor);
	
	return array($Codi, $Valor);
}

/** 
 * ResultSetAJSON
 *
 * Passa un ResultSet de MySQL a JSON.
 * https://stackoverflow.com/questions/3430492/convert-mysql-record-set-to-json-string-in-php
 *
 * @param object $ResultSet ResultSet de MySQL.
 * @return string ResultSet en format JSON.
 */
function ResultSetAJSON($ResultSet)
{
	$JSON = '{ "notes": [';
	while($row = $ResultSet->fetch_assoc()) {
		$jsonRow = json_encode($row);
		if ($jsonRow != '')
			$JSON .= $jsonRow.',';
	}
	$JSON = rtrim($JSON, ',').']}';
	return $JSON;
}

/**
 * Classe que encapsula les utilitats per al maneig de la DB.
 */
class DB
{
	/**
	 * Carrega un registre d'una taula de la base de dades.
	 * @param object $Connexio Connexió a la base de dades.
	 * @param string $Taula Taula de la base de dades.
	 * @param string $Camp Nom del camp.
	 * @param string $Valor Valor del camp.
	 * @param int $Tipus Tipus d'objecte que retorna (1: objecte, 2: array associatiu)
	 * @return mixed Registre de la taula.
	 */
	public static function CarregaRegistre($Connexio, $Taula, $Camp, $Valor, $Tipus = 1)
	{
		$Retorn = NULL;
		$SQL = "SELECT * FROM $Taula WHERE $Camp='$Valor'";
		try {
			$ResultSet = $Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception('<p>'.$Connexio->error.'.</p><p>SQL: '.$SQL.'</p>');
		} catch (Exception $e) {
			die("<p><b>ERROR CarregaRegistre</b>. Causa:</p>".$e->getMessage());
		}	
		if ($ResultSet->num_rows > 0) {
			switch ($Tipus) {
				case 1:
					$Retorn = $ResultSet->fetch_object();
					break;
				case 2:
					$Retorn = $ResultSet->fetch_assoc();
					break;
			}
		}	
		return $Retorn;
	}
	
	/**
	 * Carrega un registre d'una taula de la base de dades i el retorna com a objecte.
	 * @param object $Connexio Connexió a la base de dades.
	 * @param string $Taula Taula de la base de dades.
	 * @param string $Camp Nom del camp.
	 * @param string $Valor Valor del camp.
	 * @return mixed Registre de la taula.
	 */
	public static function CarregaRegistreObj($Connexio, $Taula, $Camp, $Valor)
	{
		return self::CarregaRegistre($Connexio, $Taula, $Camp, $Valor, 1);
	}

	/**
	 * Carrega un registre d'una taula de la base de dades i el retorna com a array associatiu.
	 * @param object $Connexio Connexió a la base de dades.
	 * @param string $Taula Taula de la base de dades.
	 * @param string $Camp Nom del camp.
	 * @param string $Valor Valor del camp.
	 * @return mixed Registre de la taula.
	 */
	public static function CarregaRegistreAss($Connexio, $Taula, $Camp, $Valor)
	{
		return self::CarregaRegistre($Connexio, $Taula, $Camp, $Valor, 2);
	}

	/**
	 * Carrega un registre d'una taula de la base de dades.
	 * @param object $Connexio Connexió a la base de dades.
	 * @param string $SQL Sentència SQL.
	 * @param int $Tipus Tipus d'objecte que retorna (1: objecte, 2: array associatiu)
	 * @return mixed Conjunt de registres de la taula.
	 */
	public static function CarregaConjuntRegistres($Connexio, $SQL, $Tipus = 1)
	{
		$Retorn = NULL;
		//$SQL = "SELECT * FROM $Taula WHERE $Camp='$Valor'";
		try {
			$ResultSet = $Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception('<p>'.$Connexio->error.'.</p><p>SQL: '.$SQL.'</p>');
		} catch (Exception $e) {
			die("<p><b>ERROR CarregaConjuntRegistres</b>. Causa:</p>".$e->getMessage());
		}	
		if ($ResultSet->num_rows > 0) {
			$Retorn = [];
			switch ($Tipus) {
				case 1:
					while($row = $ResultSet->fetch_object()) 
						array_push($Retorn, $row);
//					$Retorn = $ResultSet->fetch_object();
					break;
				case 2:
					while($row = $ResultSet->fetch_assoc()) 
						array_push($Retorn, $row);
//					$Retorn = $ResultSet->fetch_assoc();
					break;
			}
		}	
		return $Retorn;
	}
	
	public static function CarregaConjuntRegistresObj($Connexio, $SQL)
	{
		return self::CarregaConjuntRegistres($Connexio, $SQL, 1);
	}

	public static function CarregaConjuntRegistresAss($Connexio, $SQL)
	{
		return self::CarregaConjuntRegistres($Connexio, $SQL, 2);
	}

	/**
	 * Obté les metadades d'una taula.
	 * @param object $Connexio Connexió a la base de dades.
	 * @param string $Taula Taula.
	 * @return array Metadades de la taula.
	 */
	public static function ObteMetadades($Connexio, string $Taula): array {
		$Retorn = [];
		$SQL = 'DESCRIBE '.$Taula;
		$ResultSet = $Connexio->query($SQL);
		while($row = $ResultSet->fetch_assoc()) {
			array_push($Retorn, $row);
		}
		return $Retorn;
	}

	/**
	 * Obté la clau primària a partir de les metadades d'una taula.
	 * @param array Metadades de la taula.
	 * @return string Clau primària. Si n'hi ha més d'una, es separen per comes.
	 */
	public static function ClauPrimariaDesDeMetadades(array $Metadades): string {
		$aClauPrimaria = [];
		for ($i=0; $i<count($Metadades); $i++) {
			$row = $Metadades[$i];
			if ($row['Key'] == 'PRI') 
				array_push($aClauPrimaria, $row['Field']);
		}
		$Retorn = implode(",", $aClauPrimaria);
		return $Retorn;
	}
	
	/**
	 * Duplica un registre d'una taula de la base de dades.
	 * @param object $Connexio Connexió a la base de dades.
	 * @param string $Taula Taula de la base de dades.
	 * @param string $ClauPrimaria Nom del camp de la clau primària.
	 * @param string $Valor Valor del camp de la clau primària.
	 * @param string $CampCopia Nom del camp que es vol que aparegui el text "(còpia)" al final. No implementat!
	 */
	public static function DuplicaRegistre($Connexio, $Taula, $ClauPrimaria, $Valor, $CampCopia = '')
	{
		$Metadades = self::ObteMetadades($Connexio, $Taula);
		$AutoIncrement = False;
		$aCamps = [];
		for ($i=0; $i<count($Metadades); $i++) {
			$row = $Metadades[$i];
			if ($row['Key'] == 'PRI') 
				$AutoIncrement = ($row['Extra'] == 'auto_increment');
			else
				array_push($aCamps, $row['Field']);
		}
		$Camps = implode(",", $aCamps);

		if ($AutoIncrement) 
			$SQL = "INSERT INTO $Taula ($Camps) SELECT $Camps FROM $Taula WHERE $ClauPrimaria=$Valor";
		else
			$SQL = "
				INSERT INTO $Taula ($ClauPrimaria,$Camps) 
				SELECT (SELECT MAX($ClauPrimaria)+1 FROM $Taula),$Camps FROM $Taula WHERE $ClauPrimaria=$Valor";

		try {
			$ResultSet = $Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception('<p>'.$Connexio->error.'.</p><p>SQL: '.$SQL.'</p>');
		} catch (Exception $e) {
			die("<p><b>ERROR DuplicaRegistre</b>. Causa:</p>".$e->getMessage());
		}	
	}
}

?>