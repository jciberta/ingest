<?php

/** 
 * LibAvaluacio.php
 *
 * Llibreria d'utilitats per a l'avaluació.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibForms.php');

/**
 * Classe que encapsula les utilitats per a l'avaluació.
 */
class Avaluacio 
{
	// Estat de l'avaluació
	const Ordinaria = 'ORD';
	const ExtraOrdinaria = 'EXT';
	const Tancada = 'TAN';
	
	/**
	* Connexió a la base de dades.
	* @var object
	*/    
	public $Connexio;

	/**
	* Usuari autenticat.
	* @var object
	*/    
	public $Usuari;

	/**
	* Avaluació
	* @var string
	*/    
	public $Avaluacio = self::Ordinaria;

	/**
	* Dades dels curs.
	* @var object
	*/    
	private $Curs;

	/**
	* Registre de la base de dades que conté les dades d'una avaluació.
	* @var object
	*/    
    private $Registre = NULL;

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
	 * Crea la SQL per obtenir les dades de l'avaluació.
	 * @param integer $id Identificador del curs.
     * @return string Sentència SQL.
	 */
	private function CreaSQL($id): string {
		return ' SELECT C.*, AA.nom AS AnyAcademic, AA.any_inici, AA.any_final '.
			' FROM CURS C '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
			' WHERE curs_id='.$id;
	}	
	
	/**
	 * Carrega les dades d'una avaluació i les  emmagatzema en l'atribut Registre.
     * @param int $CursId Identificador del curs.
	 */
	public function Carrega(int $CursId) {
		$SQL = $this->CreaSQL($CursId);
//print $SQL;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {		
			$row = $ResultSet->fetch_object();
			$this->Registre = $row;
		}
	}

	/**
	 * Retorna l'estat de l'avaluació del curs.
	 * @param integer $id Identificador del curs.
     * @return string estat de l'avaluació.
	 */
	public function Estat(): string {
		$sRetorn = '';
		if ($this->Registre != NULL) {
			$row = $this->Registre;
			if ($row->finalitzat)
				$sRetorn = self::Tancada;
			else
				$sRetorn = ($row->avaluacio == 'ORD') ? self::Ordinaria : self::ExtraOrdinaria;
		}
		return $sRetorn;
	}

	/**
	 * Retorna el text de l'estat de l'avaluació per a l'expedient.
     * @return string Text de l'estat de l'avaluació.
	 */
	public function EstatText(): string {
		$sRetorn = '';
		if ($this->Registre != NULL) {
			$row = $this->Registre;
			if ($row->finalitzat)
				$sRetorn = '';
			else if ($row->avaluacio == 'EXT')
				$sRetorn = 'Ext.';
			else
				$sRetorn = $row->trimestre.'T';
		}
		return $sRetorn;
	}
	
	/**
	 * Comprova si el butlletí és visible per als estudiants.
	 * @returns boolean Cert si el butlletí és visible.
	 */
	function ButlletiVisible(): bool {
		return $this->Registre->butlleti_visible == '1';
	}	

	/**
	 * Crea la taula HTML amb les dades de l'avaluació.
	 * @param integer $id Identificador del curs.
     * @return string Taula amb les dades de l'avaluació.
	 */
	public function CreaTaula(): string {
		$sRetorn = '';
		if ($this->Registre != NULL) {
			$row = $this->Registre;
			echo "Any acadèmic: <B>".utf8_encode($row->AnyAcademic)."</B><br>";
			echo "Curs: <B>".utf8_encode($row->nom)."</B><br>";
			echo "Codi: <B>".utf8_encode($row->codi)."</B><br>";
			echo "Nivell: <B>".utf8_encode($row->nivell)."</B><br>";
			echo "<BR>";
			if ($row->finalitzat) {
				echo "Avaluació: <B>El curs està tancat</B><br>";
			}
			else {
				$Avaluacio = ($row->avaluacio == 'ORD') ? 'Ordinària' : 'Extraordinària';
				echo "Avaluació: <B>".$Avaluacio."</B><br>";
				if ($row->avaluacio == 'ORD')
					echo "Trimestre: <B>".utf8_encode($row->trimestre)."</B><br>";
				$MostraButlletins = ($row->butlleti_visible == 1)? ' checked ' : '';
				echo 'Butlletí visible: <input type="checkbox" disabled id="chb_butlleti_visible" '.$MostraButlletins.'><br>';
			}
			$this->Curs = $row;
			$this->Avaluacio = $row->avaluacio;
		}
		return $sRetorn;
	}
	
	/**
	 * Crea la descripció de l'avaluació del curs.
	 * @param integer $id Identificador del curs.
     * @return string Descripció amb les dades de l'avaluació.
	 */
	public function CreaDescripcio(): string {
		$sRetorn = '<DIV id=desc>';
		if ($this->Registre != NULL) {
			$row = $this->Registre;
			echo "Curs: <B>".utf8_encode($row->nom)."</B>";
			
			if ($row->finalitzat) {
				echo " Avaluació: <B>Tancada</B><br>";
			}
			else {
				$Avaluacio = ($row->avaluacio == 'ORD') ? 'Ordinària' : 'Extraordinària';
				echo " Avaluació: <B>".$Avaluacio."</B>";
				if ($row->avaluacio == 'ORD')
					echo " Trimestre: <B>".utf8_encode($row->trimestre)."</B>";
				$this->Avaluacio = $row->avaluacio;
			}
		}
		$sRetorn .= '</DIV>';
		return $sRetorn;
	}

	/**
	 * Crea un nom de fitxer per a les dades de l'avaluació del curs.
     * @return string Descripció amb les dades de l'avaluació.
	 */
	public function NomFitxer(): string {
		$sRetorn = '';
		if ($this->Registre != NULL) {
			$row = $this->Registre;
			$sRetorn .= $row->any_inici.'-'.$row->any_final.'_'.$row->codi.'_';
			if ($row->avaluacio == 'ORD')
				$sRetorn .= $row->trimestre.'T';
			else
				$sRetorn .= 'Ext';
		}
		return $sRetorn;
	}

	/**
	 * Crea els botons disponibles depenent de les característiques del curs.
     * @return string HTML amb els botons.
	 */
	private function CreaBotons(): string {
		$sRetorn = '<DIV id=botons>';

		if ($this->Curs->finalitzat) {
			$sRetorn .= "No es permeten accions.";
		}
		else {
			$Text = ($this->Curs->butlleti_visible != 1) ? 'Mostra butlletins' : 'Amaga butlletins';
			$sRetorn .= '<DIV id=div_MostraButlletins><P><button class="btn btn-primary active" id="btn_MostraButlletins" onclick="MostraButlletins(this, '.$this->Curs->curs_id.')">'.$Text.'</button>&nbsp;';
			$sRetorn .= "Els alumnes d'aquest curs poden veure el butlletí de notes.</P></DIV>";

			$Estil = ($this->Curs->avaluacio == 'ORD') ? '' : 'style="display:none"';
			$sRetorn .= '<DIV id=div_TancaAvaluacio '.$Estil.'><P><button class="btn btn-primary active" onclick="TancaAvaluacio(this, '.$this->Curs->curs_id.')">Tanca avaluació i ves a l\'extraordinària</button>&nbsp;'.
				"Tanca l'avaluació (les notes apunten a la següent convocatòria) i amaga els butlletins.</P></DIV>";

			$Estil = ($this->Curs->avaluacio == 'EXT') ? '' : 'style="display:none"';
			$sRetorn .= '<DIV id=div_TancaCurs '.$Estil.'><P><button class="btn btn-primary active" onclick="TancaCurs(this, '.$this->Curs->curs_id.')">Tanca el curs</button>&nbsp;'.
				"Tanca el curs i amaga els butlletins.</P></DIV>";

			$sRetorn .= '</DIV>';
		}
		return $sRetorn;
	}
	
	/**
	 * Crea l'espai pels missatges de succés i error per quan es realitzen les accions AJAX.
	 */
	public function CreaMissatges() {
		$sRetorn = '<div class="alert alert-success collapse" id="MissatgeCorrecte" role="alert"></div>';
		$sRetorn .= '<div class="alert alert-danger collapse" id="MissatgeError" role="alert"></div>';
		return $sRetorn;
	}
		
	/**
	 * Escriu la taula HTML amb les dades de l'avaluació.
	 * @param integer $id Identificador del curs.
	 */
	public function EscriuTaula() {
		echo '<DIV id=taula>';
		echo $this->CreaTaula();
		echo '</DIV>';
	}
	
	/**
	 * Escriu els botons disponibles depenent de les característiques del curs.
	 */
	public function EscriuBotons() {
		echo $this->CreaBotons();
	}

	/**
	 * Tanca una avaluació (passa de avaluació ordinària a extraordinària).
	 * 	1. Posa el camp avaluacio a EXT i amaga els butlletins.
	 * 	2. Es posa la convocatòria a 0 per a les notes aprovades.
	 *  3. Es passa una convocatòria per a les notes no superades.
	 * @param integer $id Identificador del curs.
	 */
	public function TancaAvaluacio(int $id) {
		// S'ha d'executar de forma atòmica
		$this->Connexio->query('START TRANSACTION');
		try {
			$SQL = ' UPDATE CURS SET avaluacio="EXT", butlleti_visible=0 WHERE curs_id='.$id;
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
			
			// MySQL no deixa fer un UPDATE amb una subconsulta. Es soluciona amb un wrapper.
			// https://stackoverflow.com/questions/4429319/you-cant-specify-target-table-for-update-in-from-clause
			$SQL = ' UPDATE NOTES SET convocatoria=0 WHERE notes_id IN ('.
				'  SELECT notes_id FROM ('.
				'     SELECT N.notes_id FROM NOTES N '.
				'     LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
				'     WHERE M.curs_id='.$id.' AND ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria)>=5 '.
				'  ) AS TEMP '.
				')';
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
			
			// Falta tractar les convocatòries de gràcia !!! (màxim 5)
			$SQL = ' UPDATE NOTES SET convocatoria=convocatoria+1 WHERE notes_id IN ('.
				'  SELECT notes_id FROM ('.
				'     SELECT N.notes_id FROM NOTES N '.
				'     LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
				'     WHERE M.curs_id='.$id.' AND ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria)<5 '.
				'  ) AS TEMP '.
				')';
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
			
			$this->Connexio->query('COMMIT');
		} 
		catch (Exception $e) {
			$this->Connexio->query('ROLLBACK');
			die("ERROR TancaAvaluacio. Causa: ".$e->getMessage());
		}	
//exit;		
	}
	
	/**
	 * Tanca un curs.
	 * 	1. Amaga els butlletins i marca el curs com finalitzat.
	 * 	2. Es posa la convocatòria a 0 per a les notes aprovades. -> NO! Es fa al crear la següent matrícula
	 *  3. Es passa una convocatòria per a les notes no superades. -> NO!
	 * @param integer $id Identificador del curs.
	 */
	public function TancaCurs(int $id) {
		// S'ha d'executar de forma atòmica
		$this->Connexio->query('START TRANSACTION');
		try {
			$SQL = ' UPDATE CURS SET butlleti_visible=0, finalitzat=1 WHERE curs_id='.$id;
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'. SQL: '.$SQL);

			// 2 i 3: Es fan al crear la següent matrícula (quan es copien les notes anteriors)

			/*
			// 2. Es posa la convocatòria a 0 per a les notes aprovades
			$SQL = ' UPDATE NOTES SET convocatoria=0 WHERE notes_id IN ('.
				'  SELECT notes_id FROM ('.
				'     SELECT N.notes_id FROM NOTES N '.
				'     LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
				'     WHERE M.curs_id='.$id.' AND ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria)>=5 '.
				'  ) AS TEMP '.
				')';
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
			
			// 3. Es passa una convocatòria per a les notes no superades. 
			// Falta tractar les convocatòries de gràcia !!! (màxim 5)
			$SQL = ' UPDATE NOTES SET convocatoria=convocatoria+1 WHERE notes_id IN ('.
				'  SELECT notes_id FROM ('.
				'     SELECT N.notes_id FROM NOTES N '.
				'     LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
				'     WHERE M.curs_id='.$id.' AND ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria)<5 '.
				'  ) AS TEMP '.
				')';
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
			*/
			
			$this->Connexio->query('COMMIT');
		} 
		catch (Exception $e) {
			$this->Connexio->query('ROLLBACK');
			die("ERROR TancaCurs. Causa: ".$e->getMessage());
		}	
	}
}

?>