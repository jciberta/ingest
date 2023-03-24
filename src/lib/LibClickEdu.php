<?php

/** 
 * LibClickEdu.php
 *
 * Llibreria d'utilitats per al ClickEdu.
 * https://api-docs.ClickEdu.eu/
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibClasses.php');

/**
 * Classe que encapsula les utilitats per al maneig del ClickEdu.
 */
class ClickEdu extends Objecte
{
	/**
	 * Clau de l'API.
	 * @var string
	 */    
	private $ClickEduApiKey = '';
	
	/**
	 * Identificador de l'institut.
	 * @var integer
	 */    
	private $ClickEduId = -1;

	/**
	 * Clau secreta.
	 * @var string
	 */    
	private $ClickEduSecret = '';
	
	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 * @param objecte $system Dades de l'aplicació.
	 */
	function __construct($conn, $user, $system) {
		parent::__construct($conn, $user, $system);
		$this->ClickEduApiKey = $this->Sistema->clickedu_api_key;
		$this->ClickEduId = $this->Sistema->clickedu_id;
		$this->ClickEduSecret = $this->Sistema->clickedu_secret;
	}	

	public function AuthToken(): string {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.ClickEdu.eu/login/v1/auth/token',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{
				"grant_type": "client_credentials",
				"client_id": '.$this->ClickEduId.',
				"client_secret": "'.$this->ClickEduSecret.'"
			}',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'x-api-key: '.$this->ClickEduApiKey,
				'domain: inspalamos.ClickEdu.eu',
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$data = json_decode($response);
		$access_token = $data->access_token ?? '';
		return $access_token;
	}	

	public function AuthTokenValidate(string $access_token): string {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.ClickEdu.eu/login/v1/auth/token/validate?oauth_token=Bearer%20'.$access_token,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'domain: inspalamos.ClickEdu.eu',
			'x-api-key: '.$this->ClickEduApiKey,
			'Authorization: Bearer '.$access_token
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	public function Students(string $access_token, int $page = 1, int $limit = 100): string {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.clickedu.eu/users/v1/students?limit=100',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'x-api-key: '.$this->ClickEduApiKey,
				'domain: inspalamos.clickedu.eu',
				'Authorization: Bearer '.$access_token
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);		
		return $response;
	}
}

?>