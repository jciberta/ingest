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
//			$Valor[$i] = utf8_encode($row[$CampValor]);
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
}

?>