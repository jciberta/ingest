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
require_once(ROOT.'/lib/LibCurs.php');

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
	* Nivell (1 o 2)
	* @var int
	*/    
	public $Nivell = 0;

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
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
//			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
			' WHERE curs_id='.$id;
	}	
	
	/**
	 * Crea la SQL pel llistat de cursos.
     * @return string Sentència SQL.
	 */
	private function CreaSQLAvaluacions() {
		$SQL = ' SELECT '.
			'   C.curs_id, '.
			"   SUBSTRING_INDEX(SUBSTRING_INDEX(C.grups_tutoria, ',', numbers.n), ',', -1) grups_tutoria, ".
			'   C.cicle_formatiu_id, C.curs_id, C.codi, C.nom AS NomCurs, C.nivell, C.estat, CONCAT(AA.any_inici,"-",AA.any_final) AS Any, '.
			'   CASE WHEN C.estat = "T" THEN "Tancada" WHEN C.avaluacio = "ORD" THEN "Ordinària" WHEN C.avaluacio = "EXT" THEN "Extraordinària" END AS avaluacio, CASE WHEN C.avaluacio = "ORD" THEN C.trimestre WHEN C.avaluacio = "EXT" THEN NULL END AS trimestre '.
			' FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) numbers '.
			" RIGHT JOIN CURS C ON CHAR_LENGTH(C.grups_tutoria)-CHAR_LENGTH(REPLACE(C.grups_tutoria, ',', ''))>=numbers.n-1 ".

			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.

//			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
//			' WHERE AA.actual=1 '.
			' ORDER by C.curs_id, C.grups_tutoria ';
		return $SQL;
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
			$this->Nivell = $row->nivell;
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
			if ($row->estat == Curs::Tancat)
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
			if ($row->estat == Curs::Tancat)
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
		return $this->Registre->estat == Curs::Obert;
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
			if ($row->estat == Curs::Tancat) {
				echo "Avaluació: <B>El curs està tancat</B><br>";
			}
			else {
				$Avaluacio = ($row->avaluacio == 'ORD') ? 'Ordinària' : 'Extraordinària';
				echo "Avaluació: <B>".$Avaluacio."</B><br>";
				if ($row->avaluacio == 'ORD')
					echo "<div id='trimestre'>Trimestre: <B>".utf8_encode($row->trimestre)."</B></div>";
				echo '<div id="estat">Estat: '.Curs::TextEstat($row->estat).'</div>';
				
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
			
			if ($row->estat == Curs::Tancat) {
				echo " Avaluació: <img src=img/colorT.png> <B>Tancada</B>";
			}
			else {
				$Avaluacio = ($row->avaluacio == 'ORD') ? 'Ordinària' : 'Extraordinària';
				echo " Avaluació: <B>".$Avaluacio."</B>";
				if ($row->avaluacio == 'ORD')
					echo " Trimestre: <B>".utf8_encode($row->trimestre)."</B>";
				$this->Avaluacio = $row->avaluacio;
				echo " Estat: ".Curs::TextEstatColor($row->estat);
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
	 * Crea un botó per a un estat.
     * @return string HTML amb el botó.
	 */
	private function CreaBotoEstat(string $sEstat, $iCursId, string $sDisabled): string {
		$sRetorn = '<SPAN id=div_actiu>'.
			'<button class="btn btn-primary active"'.$sDisabled.' id="btn_'.$sEstat.'" '.
			'onclick="PosaEstatCurs(this, '.$iCursId.', '."'$sEstat'".')">'.
			Curs::TextEstat($sEstat).
			'</button>&nbsp;';
		return $sRetorn;
	}

	/**
	 * Crea un botó per a un trimestre.
     * @return string HTML amb el botó.
	 */
	private function CreaBotoTrimestre(string $iTrimestre, $iCursId, string $sDisabled): string {
		$sRetorn = '<SPAN id=div_actiu>'.
			'<button class="btn btn-primary active"'.$sDisabled.' id="btn_'.$iTrimestre.'" '.
			'onclick="PosaTrimestreCurs(this, '.$iCursId.', '."'$iTrimestre'".')">'.
			Ordinal($iTrimestre). ' trimestre'.
			'</button>&nbsp;';
		return $sRetorn;
	}

	/**
	 * Crea els botons disponibles depenent de les característiques del curs.
     * @return string HTML amb els botons.
	 */
	private function CreaBotons(): string {
		$sRetorn = '<DIV id=botons>';
		$aDisabled = array(Curs::Actiu => '', Curs::Junta => '', Curs::Inactiu => '', Curs::Obert => '', Curs::Tancat => '');
		$aDisabled[$this->Curs->estat] = 'disabled';
		$aTrimestre = array(1 => '', 2 => '', 3 => '');
		$aTrimestre[$this->Curs->trimestre] = 'disabled';

		if ($this->Curs->estat == Curs::Tancat) {
			$sRetorn .= "No es permeten accions.";
		}
		else {
			$sRetorn .= "<TABLE>";

			$sRetorn .= "<TR>";
			$sRetorn .= "<TD>";
			$sRetorn .= "<P>Passa al següent estat:</P>";
			$sRetorn .= $this->CreaBotoEstat(Curs::Actiu, $this->Curs->curs_id, $aDisabled[Curs::Actiu]);
			$sRetorn .= $this->CreaBotoEstat(Curs::Junta, $this->Curs->curs_id, $aDisabled[Curs::Junta]);
			$sRetorn .= $this->CreaBotoEstat(Curs::Inactiu, $this->Curs->curs_id, $aDisabled[Curs::Inactiu]);
			$sRetorn .= $this->CreaBotoEstat(Curs::Obert, $this->Curs->curs_id, $aDisabled[Curs::Obert]);
			$sRetorn .= "</TD>";
			$sRetorn .= "<TD ROWSPAN=2><DIV STYLE='margin-left:100px'>";
			$sRetorn .= Curs::LlegendaEstat();
			$sRetorn .= "</DIV></TD>";
			$sRetorn .= "</TR>";

			$sRetorn .= "<TR>";
			$sRetorn .= "<TD><DIV id=div_BotonsTrimestre>";
			if (($this->Curs->avaluacio == 'ORD')) {
				$sRetorn .= "<BR><P>Tria el trimestre:</P>";
				$sRetorn .= $this->CreaBotoTrimestre(1, $this->Curs->curs_id, $aTrimestre[1]);
				$sRetorn .= $this->CreaBotoTrimestre(2, $this->Curs->curs_id, $aTrimestre[2]);
				$sRetorn .= $this->CreaBotoTrimestre(3, $this->Curs->curs_id, $aTrimestre[3]);
			}
			else {
				$sRetorn .= "<BR><BR>";
			}
			$sRetorn .= "<DIV></TD>";
			$sRetorn .= "</TR>";

			$sRetorn .= "</TABLE>";

			$sRetorn .= "<BR><P>La següent acció no es pot desfer:</P>";

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
	 * Escriu el llistat de les avaluacions actuals.
	 */
	public function EscriuFormulariRecerca() {
		$SQL = $this->CreaSQLAvaluacions();
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		//$frm->AfegeixJavaScript('Matricula.js?v1.2');
		$frm->Titol = 'Avaluacions';
		$frm->SQL = utf8_decode($SQL);
		$frm->Taula = 'CURS';
		$frm->ClauPrimaria = 'curs_id, grups_tutoria';
		$frm->Camps = 'codi, grups_tutoria, NomCurs, nivell, Any, avaluacio, trimestre';
		$frm->Descripcions = 'Codi, Nom, Grup, Nivell, Any, Avaluació, Trimestre';
		
		$frm->AfegeixOpcio('Avaluació', 'Fitxa.php?accio=ExpedientSagaAvaluacio&Id=');
		$frm->AfegeixOpcio('Acta', 'Fitxa.php?accio=Acta&Id=');
		
		// Filtre
		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('CPE.any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1]);

		$frm->EscriuHTML();
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
			$SQL = ' UPDATE CURS SET avaluacio="EXT", estat="A" WHERE curs_id='.$id;
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
	 * 	1. Amaga els butlletins i marca el curs com .
	 * 	2. Es posa la convocatòria a 0 per a les notes aprovades. -> NO! Es fa al crear la següent matrícula
	 *  3. Es passa una convocatòria per a les notes no superades. -> NO!
	 * @param integer $id Identificador del curs.
	 */
	public function TancaCurs(int $id) {
		// S'ha d'executar de forma atòmica
		$this->Connexio->query('START TRANSACTION');
		try {
			$SQL = ' 
				UPDATE CURS 
				SET estat="T", data_tancament=now()
				WHERE curs_id='.$id;
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
	
	/**
	 * Retorna la llista de matrícules d'un curs i grup.
	 * @param string $CursIdGrup Identificador del <curs,grup>.
     * @return array Llista de matrícules.
	 */
	public function LlistaMatricules($CursIdGrup): array {
		$aRetorn = [];
		$aCursIdGrup = explode(',', $CursIdGrup); 
		
		$SQL = ' SELECT M.*, '. 
			'     U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne '.
			' FROM MATRICULA M '.
			' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
			' WHERE (M.baixa IS NULL OR M.baixa=0) AND curs_id='.$aCursIdGrup[0];
		if ((count($aCursIdGrup)>1) && ($aCursIdGrup[1] != ''))
			$SQL .= ' AND grup_tutoria="'.$aCursIdGrup[1].'"';
		$SQL .= ' ORDER BY U.cognom1, U.cognom2, U.nom ';
			
		$ResultSet = $this->Connexio->query($SQL);
		while ($obj = $ResultSet->fetch_object()) {
			array_push($aRetorn, $obj->matricula_id); 
		}
		
		return $aRetorn;
	}
}

?>