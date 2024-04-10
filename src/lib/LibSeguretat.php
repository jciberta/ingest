<?php

/** 
 * LibSeguretat.php
 *
 * Llibreria d'utilitats per a la seguretat.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Classe que encapsula les utilitats per a la seguretat.
 */
class Seguretat
{
	 /**
	  * Comprova si l'usuari té els permisos necessaris per a l'accés al punt on es crida el mètode.
	  * @param object $Usuari Usuari de l'aplicació.
	  * @param array $Array Array de 2 caràcters d'usuaris permesos. Codificació usuaris:
	  *   - SU: administrador
	  *   - DI: direcció
	  *   - CE: cap d'estudis
	  *   - PR: professor
	  *   - AL: alumne
	  *   - PM: pare o mare
	  *   - AD: administratiu
	  *   - AU: auditor
	  * @param bool $Condicio Condició extra que farà un OR amb els usuaris. És a dir, es permetrà l'accés als usuaris o si es compleix la condició.
	  */
	public static function ComprovaAccessUsuari($Usuari, $Array, bool $Condicio = false) {
		$Access = false;
		if ($Usuari !== null) {
			foreach($Array as $Valor) 
				$Valor = strtoupper($Valor);
			if (in_array('SU', $Array) && $Usuari->es_admin)
				$Access = true;
			if (in_array('DI', $Array) && $Usuari->es_direccio)
				$Access = true;
			if (in_array('CE', $Array) && $Usuari->es_cap_estudis)
				$Access = true;
			if (in_array('PR', $Array) && $Usuari->es_professor)
				$Access = true;
			if (in_array('AL', $Array) && $Usuari->es_alumne)
				$Access = true;
			if (in_array('PM', $Array) && $Usuari->es_pare)
				$Access = true;
			if (in_array('AD', $Array) && $Usuari->es_administratiu)
				$Access = true;
			if (in_array('AU', $Array) && $Usuari->es_auditor)
				$Access = true;
		}
		if ($Condicio)
			$Access = true;
		
		if (!$Access) {
			if (isset($_SESSION)) {
				session_unset();
				session_destroy();
			}
			die('Usuari no autoritzat.');
		}
	}
	
	public static function base64url_encode($str) {
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}
	
	public static function GeneraJWT($headers, $payload, $secret) {
		$headers_encoded = base64url_encode(json_encode($headers));
		$payload_encoded = base64url_encode(json_encode($payload));
		$signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
		$signature_encoded = base64url_encode($signature);
		$jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
		return $jwt;
	}

	public static function EsJWTValid($jwt, $secret) {
		// split the jwt
		$tokenParts = explode('.', $jwt);
		$header = base64_decode($tokenParts[0]);
		$payload = base64_decode($tokenParts[1]);
		$signature_provided = $tokenParts[2];

		// check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
		$expiration = json_decode($payload)->exp;
		$is_token_expired = ($expiration - time()) < 0;

		// build a signature based on the header and payload using the secret
		$base64_url_header = base64url_encode($header);
		$base64_url_payload = base64url_encode($payload);
		$signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
		$base64_url_signature = base64url_encode($signature);

		// verify it matches the signature provided in the jwt
		$is_signature_valid = ($base64_url_signature === $signature_provided);
		
		return !($is_token_expired || !$is_signature_valid);
	}
}

?>