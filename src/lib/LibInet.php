<?php

/** 
 * LibInet.php
 *
 * Llibreria d'utilitats d'Internet.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibHTML.php');


/**
 * Obté l'adreça IP del visitant.
 * https://stackoverflow.com/questions/13646690/how-to-get-real-ip-from-visitor
 * @return string IP del visitant.
 */
function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

/**
 * Classe Inet.
 * Classe base per a les funcions d'Internet.
 */
class Inet extends Objecte
{
	// ToDo:
	// EsIPPublica()?
	// Tipus Adreça? Pública, Privada, Localhost...

	/**
	 * Obté les dades d'una IP.
	 * @param string $ip Adreça IP.
	 * @returns string Taula amb les dades d'una IP.
	 */
	public function ObteDadesIP(string $ip): string {
		$this->Carrega($ip);

		// Convert an object to associative array in PHP
		// https://www.geeksforgeeks.org/convert-an-object-to-associative-array-in-php/
		$aInfo = json_decode(json_encode($this->Registre), true);

		return CreaTaula1($aInfo);
	}
	
	private function Carrega(string $ip) {
		$SQL = " SELECT * FROM GEOLOCALITZACIO_IP where ip='$ip' ";
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$this->Registre = $ResultSet->fetch_object();
		}
		else {
			$this->ActualitzaIP($ip);
			$this->Carrega($ip);
		}
	}
	
	/**
	 * Obté les dades d'una IP en format JSON a través de l'API d'un proveïdor.
	 * @param string $ip Adreça IP.
	 * @returns array Dades en format JSON posades en un array.
	 */
	private function ObteJsonIP(string $ip) {
		$URL = "https://api.ipdata.co/$ip?api-key=".IPDATA_API_KEY;
		$json = file_get_contents($URL);

		// https://www.php.net/manual/en/function.json-decode.php
		$aJSON = json_decode($json, true);

		// Convert null value to empty string 
		// https://developertipsandtricks.blogspot.com/2013/10/convert-null-to-empty-string-for-json.html
		array_walk_recursive($aJSON,function(&$item){$item=strval($item);});

		return $aJSON;
	}
	
	/**
	 * Actualitza la informació sobre una IP (geolocalització, ...).
	 * @param object $Connexio Connexió a la base de dades.
	 * @param string $ip Adreça IP.
	 * @param string $JSON Dades en format JSON.
	 */
	private function ActualitzaIP(string $ip) {
		$JSON = $this->ObteJsonIP($ip);
		
		if (!str_starts_with($ip, '10.') && !str_starts_with($ip, '192.168.') && !str_starts_with($ip, '172.16.')) {
			$SQL = " SELECT ip FROM GEOLOCALITZACIO_IP where ip='$ip' ";
			$ResultSet = $this->Connexio->query($SQL);
			if ($ResultSet->num_rows > 0) {
				// Actualitzem
				$SQL = " 
					UPDATE GEOLOCALITZACIO_IP SET 
						is_eu=".BooleaAMySQL($JSON['is_eu']).",
						city=".TextAMySQL($JSON['city']).",
						region=".TextAMySQL($JSON['region']).",
						region_code=".TextAMySQL($JSON['region_code']).",
						country_name=".TextAMySQL($JSON['country_name']).",
						country_code=".TextAMySQL($JSON['country_code']).",
						latitude=".$JSON['latitude'].",
						longitude=".$JSON['longitude'].",
						postal=".TextAMySQL($JSON['postal']).",
						calling_code=".TextAMySQL($JSON['calling_code']).",
						flag_url=".TextAMySQL($JSON['flag']).",
						asn=".TextAMySQL($JSON['asn']['asn']).",
						asn_name=".TextAMySQL($JSON['asn']['name']).",
						asn_domain=".TextAMySQL($JSON['asn']['domain']).",
						asn_route=".TextAMySQL($JSON['asn']['route']).",
						asn_type=".TextAMySQL($JSON['asn']['type']).",
						is_tor=".BooleaAMySQL($JSON['threat']['is_tor']).",
						is_proxy=".BooleaAMySQL($JSON['threat']['is_proxy']).",
						is_anonymous=".BooleaAMySQL($JSON['threat']['is_anonymous']).",
						is_known_attacker=".BooleaAMySQL($JSON['threat']['is_known_attacker']).",
						is_known_abuser=".BooleaAMySQL($JSON['threat']['is_known_abuser']).",
						is_threat=".BooleaAMySQL($JSON['threat']['is_threat']).",
						is_bogon=".BooleaAMySQL($JSON['threat']['is_bogon'])."
					WHERE ip='$ip'
				";
			}
			else {
				// Insertem
				$SQL = " 
					INSERT INTO GEOLOCALITZACIO_IP 
						(ip, is_eu, city, region, region_code, country_name, country_code, latitude, 
						longitude, postal, calling_code, flag_url, asn, asn_name, asn_domain, 
						asn_route, asn_type, is_tor, is_proxy, is_anonymous, is_known_attacker, 
						is_known_abuser, is_threat, is_bogon)
					VALUES (
						".TextAMySQL($JSON['ip']).",
						".BooleaAMySQL($JSON['is_eu']).",
						".TextAMySQL($JSON['city']).",
						".TextAMySQL($JSON['region']).",
						".TextAMySQL($JSON['region_code']).",
						".TextAMySQL($JSON['country_name']).",
						".TextAMySQL($JSON['country_code']).",
						".$JSON['latitude'].",
						".$JSON['longitude'].",
						".TextAMySQL($JSON['postal']).",
						".TextAMySQL($JSON['calling_code']).",
						".TextAMySQL($JSON['flag']).",
						".TextAMySQL($JSON['asn']['asn']).",
						".TextAMySQL($JSON['asn']['name']).",
						".TextAMySQL($JSON['asn']['domain']).",
						".TextAMySQL($JSON['asn']['route']).",
						".TextAMySQL($JSON['asn']['type']).",
						".BooleaAMySQL($JSON['threat']['is_tor']).",
						".BooleaAMySQL($JSON['threat']['is_proxy']).",
						".BooleaAMySQL($JSON['threat']['is_anonymous']).",
						".BooleaAMySQL($JSON['threat']['is_known_attacker']).",
						".BooleaAMySQL($JSON['threat']['is_known_abuser']).",
						".BooleaAMySQL($JSON['threat']['is_threat']).",
						".BooleaAMySQL($JSON['threat']['is_bogon'])."
					)
				";
			}
			try {
				if (!$this->Connexio->query($SQL))
					throw new Exception($Connexio->error.'.<br>SQL: '.$SQL);
			} catch (Exception $e) {
				print("<BR><b>ERROR ActualitzaIP</b>. Causa: ".$e->getMessage());
			}		
			$ResultSet->close();
		}
	}
}

?>