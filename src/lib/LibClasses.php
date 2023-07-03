<?php

/** 
 * LibClasses.php
 *
 * Llibreria de classes bàsiques de l'InGest.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Classe Objecte.
 * Classe base de la quals descendeixen els objectes.
 */
class Objecte 
{
	/**
	 * Connexió a la base de dades.
	 * @var object
	 */    
	public $Connexio = null;

	/**
	 * Usuari autenticat.
	 * @var object
	 */    
	public $Usuari = null;

	/**
	 * Dades de l'aplicació.
	 * @var object
	 */    
	public $Sistema = null;

	/**
	 * Identificador de propòsit general.
	 * @var mixed
	 */    
	public $Id = -1;
	
	/**
	 * Registre per a emmagatzemar el resultat d'un DataSet.
	 * @var object
	 */    
    public $Registre = null;
	
	// Sistemes operatius
	const soDESCONEGUT = 0;
	const soWINDOWS = 1;
	const soLINUX = 2;
	const soOSX = 3;

    /**
	 * Sistema operatiu (Windows, Linux, Mac).
	 * @var int
	 */
	public $SistemaOperatiu = self::soDESCONEGUT;

	/**
	 * Constructor de l'objecte.
	 * @param object $conn Connexió a la base de dades.
	 * @param object $user Usuari.
	 * @param object $system Dades de l'aplicació.
	 */
	function __construct($conn = null, $user = null, $system = null) {
		$this->Connexio = $conn;
		$this->Usuari = $user;
		$this->Sistema = $system;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$this->SistemaOperatiu = self::soWINDOWS;
		else if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN')
			$this->SistemaOperatiu = self::soLINUX;
		else if (strtoupper(substr(PHP_OS, 0, 3)) === 'DAR')
			$this->SistemaOperatiu = self::soOSX;
	}	
}
