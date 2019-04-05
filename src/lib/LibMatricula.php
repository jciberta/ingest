<?php

/** 
 * LibMatricula.php
 *
 * Llibreria d'utilitats per a la matriculació.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * CreaMatricula
 *
 * Crea la matrícula per a un alumne. Quan es crea la matrícula:
 *   1. Pel nivell que sigui, es creen les notes, una per cada UF d'aquell cicle
 *   2. Si l'alumne és a 2n, l'aplicació ha de buscar les que li han quedar de primer per afegir-les (PENDENT!)
 * Ús: 
 *
 * @param object $Connexio Connexió a la base de dades.
 * @param integer $CursId Id del curs.
 * @param integer $AlumneId Id de l'alumne.
 * @param integer $CicleId Id del cicle.
 * @param integer $Nivell Nivell (1r o 2n).
 * @param integer $Grup Grup (cap, A, B, C).
 * @return integer Valor de retorn: 0 Ok, -1 Alumne ja matriculat, -2 Error.
 */
function CreaMatricula($Connexio, $Curs, $Alumne, $Cicle, $Nivell, $Grup)
{
	$SQL = " CALL CreaMatricula(".$Curs.", ".$Alumne.", ".$Cicle.", ".$Nivell.", '".$Grup."', @retorn)";
	
	// Obtenció de la variable d'un procediment emmagatzemat.
	// http://php.net/manual/en/mysqli.quickstart.stored-procedures.php
	if (!$Connexio->query("SET @retorn = -2") || !$Connexio->query($SQL)) {
		echo "CALL failed: (" . $Connexio->errno . ") " . $Connexio->error;
	}

	if (!($res = $Connexio->query("SELECT @retorn as _retorn"))) {
		echo "Fetch failed: (" . $Connexio->errno . ") " . $Connexio->error;
	}

	$row = $res->fetch_assoc();
	return $row['_retorn'];	
}

/**
 * Classe que encapsula les utilitats per al maneig de la matrícula.
 */
class Matricula 
{
	/**
	* Connexió a la base de dades.
	* @access public 
	* @var object
	*/    
	public $Connexio;

	/**
	* Usuari autenticat.
	* @access public 
	* @var object
	*/    
	public $Usuari;

	/**
	 * Constructor de l'objecte.
	 * @param object $conn Connexió a la base de dades.
	 * @param object $user Usuari de l'aplicació.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}	
	
	/**
	 * Convalida una UF (no es pot desfer).
	 * Posa el camp convalidat de NOTES a cert, posa una nota de 5 i el camp convocatòria a 0.
     * @param array Primera línia.
	 */
	public function ConvalidaUF(int $NotaId) {
		$SQL = 'SELECT * FROM NOTES WHERE notes_id='.$NotaId;	
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {		
			$rsNota = $ResultSet->fetch_object();

			$SQL = 'UPDATE NOTES SET convalidat=1 WHERE notes_id='.$NotaId;	
			$this->Connexio->query($SQL);

			$SQL = 'UPDATE NOTES SET nota'.$rsNota->convocatoria.'=5 WHERE notes_id='.$NotaId;	
			$this->Connexio->query($SQL);

			$SQL = 'UPDATE NOTES SET convocatoria=0 WHERE notes_id='.$NotaId;	
			$this->Connexio->query($SQL);
		}
	}
}

 ?>