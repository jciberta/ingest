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
	
	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 * @param objecte $system Dades de l'aplicació.
	 */
	function __construct($conn = null, $user = null, $system = null) {
		$this->Connexio = $conn;
		$this->Usuari = $user;
		$this->Sistema = $system;
	}	
}
