<?php

/** 
 * LibMatricula.php
 *
 * Llibreria d'utilitats per a la matriculació.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibClasses.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibExpedient.php');

/**
 * Classe que encapsula les utilitats per al maneig de la matrícula.
 */
class Matricula extends Expedient
{
	/**
	 * CreaMatricula
	 * Crea la matrícula per a un alumne. Quan es crea la matrícula:
	 *   1. Pel nivell que sigui, es creen les notes, una per cada UF d'aquell cicle
	 *   2. Si l'alumne és a 2n, l'aplicació ha de buscar les que li han quedar de primer per afegir-les.
	 *
	 * @param integer $CursId Id del curs.
	 * @param integer $AlumneId Id de l'alumne.
	 * @param string $Grup Grup (cap, A, B, C).
	 * @param string $GrupTutoria Grup de tutoria.
	 * @return integer Valor de retorn: 0 Ok, -1 Alumne ja matriculat, -99 Error.
	 */
	public function CreaMatricula($Curs, $Alumne, $Grup, $GrupTutoria) {
		$SQL = " CALL CreaMatricula(".$Curs.", ".$Alumne.", '".$Grup."', '".$GrupTutoria."', @retorn)";

		if (Config::Debug)
			print $SQL.'<br>';		
		
		// Obtenció de la variable d'un procediment emmagatzemat.
		// http://php.net/manual/en/mysqli.quickstart.stored-procedures.php
		if (!$this->Connexio->query("SET @retorn = -99") || !$this->Connexio->query($SQL)) {
			echo "CALL failed: (" . $this->Connexio->errno . ") " . $this->Connexio->error;
		}

		if (!($res = $this->Connexio->query("SELECT @retorn as _retorn"))) {
			echo "Fetch failed: (" . $this->Connexio->errno . ") " . $this->Connexio->error;
		}

		$row = $res->fetch_assoc();
		return $row['_retorn'];	
	}

	/**
	 * CreaMatriculaDNI
	 * Crea la matrícula per a un alumne a partir del DNI.
	 *
	 * @param integer $CursId Id del curs.
	 * @param string $DNI DNI de l'alumne.
	 * @param string $Grup Grup (cap, A, B, C).
	 * @param string $GrupTutoria Grup de tutoria.
	 * @return integer Valor de retorn:
	 *    0 Ok.
	 *   -1 Alumne ja matriculat.
	 *   -2 DNI inexistent.
	 *  -99 Error.
	 */
	public function CreaMatriculaDNI(int $Curs, string $DNI, string $Grup, string $GrupTutoria): int {
		$SQL = " CALL CreaMatriculaDNI(".$Curs.", '".$DNI."', '".$Grup."', '".$GrupTutoria."', @retorn)";

		if (Config::Debug)
			print $SQL.'<br>';		
		
		// Obtenció de la variable d'un procediment emmagatzemat.
		// http://php.net/manual/en/mysqli.quickstart.stored-procedures.php
		if (!$this->Connexio->query("SET @retorn = -99") || !$this->Connexio->query($SQL)) {
			echo "CALL failed: (" . $this->Connexio->errno . ") " . $this->Connexio->error;
		}

		if (!($res = $this->Connexio->query("SELECT @retorn as _retorn"))) {
			echo "Fetch failed: (" . $this->Connexio->errno . ") " . $this->Connexio->error;
		}

		$row = $res->fetch_assoc();
		return $row['_retorn'];	
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

	/**
	 * Carrega les dades d'una matrícula i les emmagatzema en l'atribut Registre.
     * @param int $MatriculaId Identificador de la matrícula. Si no s'especifica, l'agafa de la propietat Id.
	 */
	public function Carrega(int $MatriculaId = -1) {
		if ($MatriculaId == -1)
			$MatriculaId = $this->Id;
		$SQL = " SELECT M.*, C.nivell ".
			" FROM MATRICULA M ".
			" LEFT JOIN CURS C ON (C.curs_id=M.curs_id) ".
			" WHERE matricula_id=$MatriculaId ";	
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {		
			$rsMatricula = $ResultSet->fetch_object();
			$this->Registre = $rsMatricula;
		}
	}

	/**
	 * Obté l'identificador de l'alumne a partir de les dades en l'atribut Registre.
	 * @return integer Identificador de l'alumne.
	 */
	public function ObteAlumne(): int {
		if ($this->Registre === null)
			$iRetorn = -1;
		else 
			$iRetorn = $this->Registre->alumne_id;
		return $iRetorn;
	}

	/**
	 * Obté l'identificador del curs a partir de les dades en l'atribut Registre.
	 * @return integer Identificador del curs.
	 */
	public function ObteCurs(): int {
		if ($this->Registre === null)
			$iRetorn = -1;
		else 
			$iRetorn = $this->Registre->curs_id;
		return $iRetorn;
	}

	/**
	 * Obté el nivell (1r o 2n) de la matrícula a partir de les dades en l'atribut Registre.
	 * @return integer Nivell.
	 */
	public function ObteNivell(): int {
		if ($this->Registre === null)
			$iRetorn = -1;
		else 
			$iRetorn = $this->Registre->nivell;
		return $iRetorn;
	}
	
	/**
	 * Obté el grup de tutoria a partir de les dades en l'atribut Registre.
	 * @return string Grup de tutoria.
	 */
	public function ObteGrupTutoria(): string {
		if ($this->Registre === null)
			$iRetorn = -1;
		else 
			$iRetorn = $this->Registre->grup_tutoria;
		return $iRetorn;
	}

	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		$this->CreaInici();

		$Matricula = new Matricula($this->Connexio, $this->Usuari, $this->Sistema);
		$Matricula->Carrega($this->Id);
		$alumne = $Matricula->ObteAlumne();
		$nivell = $Matricula->ObteNivell();		

		$SQL = Expedient::SQL($this->Id);
		$ResultSet = $this->Connexio->query($SQL);
	
		if ($ResultSet->num_rows > 0) {
			
			$row = $ResultSet->fetch_assoc();
			$NomComplet = trim(utf8_encodeX($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"]));
			if ($this->Usuari->es_admin) {
				$NomComplet = $NomComplet." [".$row["usuari_id"]."]";
				echo "Matrícula: <B>[$this->Id]</B>&nbsp;&nbsp;&nbsp;";
			}
			echo 'Alumne: <B>'.$NomComplet.'</B>&nbsp;&nbsp;&nbsp;';
			echo 'Cicle: <B>'.utf8_encodeX($row["NomCF"]).'</B>';
				
			echo '<span style="float:right;">';
			if ($nivell == 2) {
				echo '<input type="checkbox" name="chbNivell1" checked onclick="MostraNotes(this, 1);">Notes 1r &nbsp';
				echo '<input type="checkbox" name="chbNivell2" checked onclick="MostraNotes(this, 2);">Notes 2n &nbsp';
			}	
			echo '</span>';	
	
			echo '<BR><BR>';
	
			echo '<TABLE class="table table-fixed table-sm table-striped table-hover">';
			echo '<thead class="thead-dark">';
			echo "<TH width=200>Mòdul</TH>";
			echo "<TH width=200>UF</TH>";
			echo "<TH width=50>Nivell</TH>";
			echo "<TH width=50>Hores</TH>";
			echo "<TH width=50 style='text-align:center'>Matrícula</TH>";
			echo "<TH width=50 style='text-align:center'>Convalidació</TH>";
			echo '</thead>';
	
			$ModulAnterior = '';
			$j = 1;
			while($row) {
				echo "<TR class='Nivell".$row["NivellUF"]."'>";
				if ($row["CodiMP"] != $ModulAnterior)
					echo "<TD width=200>".utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
				else 
					echo "<TD width=200></TD>";
				$ModulAnterior = $row["CodiMP"];
				echo "<TD width=200>".utf8_encodeX($row["NomUF"])."</TD>";
				echo "<TD width=50>".$row["NivellUF"]."</TD>";
				echo "<TD width=50>".$row["HoresUF"]."</TD>";
				$Baixa = ($row["Baixa"] == True);
				$Convalidat = ($row["Convalidat"] == True);
				if ($Baixa) 
					$sChecked = '';
				else
					$sChecked = ' checked';
				$Convalidat = ($row["Convalidat"] == True);
				$sCheckedConvalidat = $Convalidat ? ' checked disabled' : '';

				// Columna matriculació
				if ($Convalidat || ($row['convocatoria'] == 0))
					echo "<TD width=50></TD>";
				else
					echo "<TD width=50 style='text-align:center'><input type=checkbox name=chbNotaId_".$row["NotaId"].$sChecked." onclick='MatriculaUF(this);'/></TD>";
				// Columna convalidació
				if ($row['convocatoria'] == 0)
					echo "<TD width=50></TD>";
				else
					echo "<TD width=50 style='text-align:center'><input type=checkbox name=chbConvalidaUFNotaId_".$row["NotaId"].$sCheckedConvalidat." onclick='ConvalidaUF(this, $alumne);'/></TD>";					

				echo "</TR>";
				$j++;
				$row = $ResultSet->fetch_assoc();
			}
			echo "</TABLE>".PHP_EOL;
			echo "<input type=hidden name=TempNota value=''>".PHP_EOL;
	
		};	
	
		$ResultSet->close();
	}

	/**
	 * Crea l'inici de la pàgina HTML.
	 */
	private function CreaInici() {
		CreaIniciHTML($this->Usuari, 'Visualitza matrícula');
		echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/Matricula.js?v1.5" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/Notes.js?v1.2" type="text/javascript"></script>';
		echo "<DIV id=debug></DIV>";
	}
}

/**
 * Classe que encapsula les utilitats per al maneig de la proposta de la matrícula.
 */
class PropostaMatricula extends Objecte
{
	private $RegistreNotes = [];
	private $PropostaMatricula = [];
	private $ComentariMatriculaSeguent = "";

	/**
	 * Crea la sentència SQL que retorna les matrícules.
	 * @param integer $MatriculaId Identificador de la matrícula, sinó s'especifica, carrega totes.
	 * @return string Sentència SQL.
	 */
	private function CreaSQL(int $MatriculaId = -1): string {
		$SQL = "
			SELECT 
				M.matricula_id, 
				C.nivell, C.nom AS NomCurs,
				U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.username, U.email_ins,
				PercentatgeAprovat(M.matricula_id) AS PercentatgeAprovat
			FROM MATRICULA M
			LEFT JOIN CURS C ON (C.curs_id=M.curs_id)
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id)
			LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id)
			LEFT JOIN USUARI U ON (U.usuari_id=M.alumne_id)
		";
		if ($MatriculaId != -1)
			$SQL .= " WHERE M.matricula_id=$MatriculaId ";
		return $SQL;
	}

	public function EscriuFormulariRecerca() {
		Seguretat::ComprovaAccessUsuari($this->Usuari, ['SU', 'DI', 'CE', 'AD']);
		$frm = new FormRecerca($this->Connexio, $this->Usuari, $this->Sistema);
		$frm->Titol = 'Proposta matrícula';
		$frm->SQL = $this->CreaSQL();
		$frm->Taula = 'MATRICULA';
		$frm->ClauPrimaria = 'matricula_id';
		$frm->Camps = 'username, NomAlumne, Cognom1Alumne, Cognom2Alumne, email_ins, nivell, %2:PercentatgeAprovat';
		$frm->Descripcions = 'Usuari, Nom, 1r cognom, 2n cognom, Correu, Nivell, Percentatge aprovat';

		// Filtre any acadèmic
		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1]);
		// Filtre cicle
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		array_unshift($aCicles[0] , '');
		array_unshift($aCicles[1] , 'Tots');
		$CicleFormatiuId = $aCicles[0][0]; 
		$frm->Filtre->AfegeixLlista('CF.cicle_formatiu_id', 'Cicle', 100, $aCicles[0], $aCicles[1]);
		// Filtre nivell
		$frm->Filtre->AfegeixLlista('C.nivell', 'Nivell', 30, array('', '1', '2'), array('Tots', '1r', '2n'));
		// Filtre grup tutoria
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, "SELECT DISTINCT grup_tutoria, grup_tutoria FROM MATRICULA WHERE grup_tutoria IS NOT NULL AND TRIM(grup_tutoria)<>'' ORDER BY grup_tutoria", "grup_tutoria", "grup_tutoria");
		array_unshift($aCicles[0] , '');
		array_unshift($aCicles[1] , 'Tots');
		$CicleFormatiuId = $aCicles[0][0]; 
		$frm->Filtre->AfegeixLlista('grup_tutoria', 'Grup', 30, $aCicles[0], $aCicles[1]);

		$frm->AfegeixOpcio('Proposta matrícula', 'Fitxa.php?accio=PropostaMatricula&Id=', 'matricula_id');
		$frm->EscriuHTML();		
	}

	public function EscriuFormulariFitxa() {
		Seguretat::ComprovaAccessUsuari($this->Usuari, ['SU', 'DI', 'CE', 'AD']);
		$SQL = $this->CreaSQL($this->Id);
		$this->Registre = DB::CarregaRegistreSQL($this->Connexio, $SQL);
		switch($this->Registre->nivell) {
			case 1:
				if ($this->Registre->PercentatgeAprovat == 100)
					$this->PropostaMatriculaTot2n();
				else if ($this->Registre->PercentatgeAprovat < 60)
					$this->PropostaMatricula1r();
				else
					$this->PropostaMatricula1r2n();
				break;
			case 2:
				if ($this->Registre->PercentatgeAprovat == 100)
					$this->PropostaTitol();
				else
					$this->PropostaMatricula2n();
				break;
			default:
				throw new Exception("PropostaMatricula: Nivell incorrecte");
				break;
		}
	}

	private function GeneraDescripcioAlumne(): string {
		$Retorn = "";
		if ($this->Usuari->es_admin)
			$Retorn = "<b>Id matrícula</b>: ".$this->Id."<br>";
		$Retorn .= "
			<b>Alumne</b>: ".Trim($this->Registre->NomAlumne." ".$this->Registre->Cognom1Alumne." ".$this->Registre->Cognom2Alumne)."<br>
			<b>Curs</b>: ".$this->Registre->NomCurs."<br>
			<b>Nivell</b>: ".$this->Registre->nivell."<br>
			<b>Percentatge hores aprovat</b>: ".number_format($this->Registre->PercentatgeAprovat, 2)."%<br><br>
		";
		return $Retorn;
	}

	private function PropostaMatricula1r() {
		CreaIniciHTML($this->Usuari, "Proposta matrícula");
		echo $this->GeneraDescripcioAlumne();
		echo '<div class="alert alert-primary" role="alert">';
		echo "Aquest és un <b>alumne de 1r</b> que no arriba al 60% del les hores. Per tant, s'ha de <b>matricular del que li queda de 1r</b>.";
		echo '</div>';
		$this->CarregaNotes();
		echo $this->GeneraTaula($this->RegistreNotes);
		CreaFinalHTML();
	}

	private function PropostaMatricula1r2n() {
		CreaIniciHTML($this->Usuari, "Proposta matrícula");
		echo $this->GeneraDescripcioAlumne();
		echo '<div class="alert alert-primary" role="alert">';
		echo "Aquest és un <b>alumne de 1r</b> que ha superat el 60% del les hores. Per tant, s'ha de <b>matricular del que li queda de 1r</b> i de la <b>proposta de matrícula de 2n</b>.";
		echo '</div>';
		$this->CarregaNotes();
		$this->CarregaPropostaMatricula();

		echo "<b>Comentari per a la matriculació del proper curs</b>: ".$this->ComentariMatriculaSeguent."<br><br>";

		echo "<table width=100% class='table table-striped table-sm table-hover'>";
		echo '<thead class="thead-dark">';
		echo "<tr>";
		echo "<th>Unitats de 1r</th>";
		echo "<th>Proposta de 2n</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tr>";
		echo "<td valign=top>".$this->GeneraTaula($this->RegistreNotes)."</td>";
		echo "<td valign=top>".$this->GeneraTaula($this->PropostaMatricula)."</td>";
		echo "<td></td>";
		echo "</tr>";
		echo "</table>";
		CreaFinalHTML();
	}

	private function PropostaMatricula2n() {
		CreaIniciHTML($this->Usuari, "Proposta matrícula");
		echo $this->GeneraDescripcioAlumne();
		echo '<div class="alert alert-primary" role="alert">';
		echo "Aquest és un <b>alumne de 2n</b> que s'ha de <b>matricular de totes les UF que li queden</b> (tant si en té a 1r com a 2n).";
		echo '</div>';
		$this->CarregaNotes();
		echo $this->GeneraTaula($this->RegistreNotes);
		CreaFinalHTML();
	}

	private function PropostaMatriculaTot2n() {
		CreaIniciHTML($this->Usuari, "Proposta matrícula");
		echo $this->GeneraDescripcioAlumne();
		echo '<div class="alert alert-primary" role="alert">';
		echo "Aquest és un <b>alumne que ha aprovat tot 1r</b> que s'ha de <b>matricular de totes les UF de 2n</b>.";
		echo '</div>';
		CreaFinalHTML();
	}

	private function PropostaTitol() {
		CreaIniciHTML($this->Usuari, "Proposta matrícula");
		echo $this->GeneraDescripcioAlumne();
		echo '<div class="alert alert-primary" role="alert">';
		echo "Aquest és un <b>alumne que ha aprovat tot el cicle</b>, per tant, <b>no s'ha matricular</b>.";
		echo '</div>';
		CreaFinalHTML();
	}

	private function CarregaNotes() {
		$SQL = Expedient::SQL($this->Id);
		$ResultSet = $this->Connexio->query($SQL);
		// Carreguem les notes de les UF
		// Posem les dades del ResultSet en una estructura de dades pròpia
		$Qualificacions = [];
		$i = -1;
		$j = -1;
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			$NomAlumne = $row["NomAlumne"];
			$Cognom1Alumne = $row["Cognom1Alumne"];
			$Cognom2Alumne = $row["Cognom2Alumne"];
			$Llei = $row["llei"];
			$ModulAnterior = '';
			while($row) {
				if ($row["CodiMP"] != $ModulAnterior) {
					$i++;
					$Qualificacions[$i] = new stdClass();
					$Qualificacions[$i]->Nom = utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"]);
					$Qualificacions[$i]->Hores = $row["HoresMP"];
					$Qualificacions[$i]->Conv = 'Ord.';
					$Qualificacions[$i]->UF = [];
					$j = -1;
				}
				$ModulAnterior = $row["CodiMP"];
				$j++;
				$Qualificacions[$i]->UF[$j] = new stdClass();
				$Qualificacions[$i]->UF[$j]->Codi = utf8_encodeX($row["CodiUF"]);
				$Qualificacions[$i]->UF[$j]->Nom = utf8_encodeX($row["NomUF"]);
				$Qualificacions[$i]->UF[$j]->Hores = utf8_encodeX($row["HoresUF"]);
				$Qualificacions[$i]->UF[$j]->Qualf = NumeroANotaText(UltimaNota($row));
				$Qualificacions[$i]->UF[$j]->Conv = Notes::UltimaConvocatoria($row);
				$row = $ResultSet->fetch_assoc();
			}
		}
		$ResultSet->close();
		$this->RegistreNotes = $Qualificacions;
	}

	private function CarregaPropostaMatricula() {
		$SQL = "
			SELECT 
				UF.codi AS CodiUF, UF.nom AS NomUF, UF.hores AS HoresUF, UF.nivell AS NivellUF, 
				MP.modul_professional_id AS IdMP, MP.codi AS CodiMP, MP.nom AS NomMP, MP.hores AS HoresMP,
				M.comentari_matricula_seguent, 
				PM.* 
			FROM PROPOSTA_MATRICULA PM
			LEFT JOIN MATRICULA M ON (M.matricula_id=PM.matricula_id) 
			LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=PM.unitat_formativa_id) 
			LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) 
			WHERE PM.matricula_id=?
			ORDER BY CodiMP, CodiUF;		
		";

		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("i", $this->Id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();

		$Qualificacions = [];
		$i = -1;
		$j = -1;
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			$this->ComentariMatriculaSeguent = $row["comentari_matricula_seguent"];

			$ModulAnterior = '';
			while($row) {
				if ($row["CodiMP"] != $ModulAnterior) {
					$i++;
					$Qualificacions[$i] = new stdClass();
					$Qualificacions[$i]->Nom = utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"]);
					$Qualificacions[$i]->Hores = $row["HoresMP"];
					$Qualificacions[$i]->UF = [];
					$j = -1;
				}
				$ModulAnterior = $row["CodiMP"];
				$j++;
				$Qualificacions[$i]->UF[$j] = new stdClass();
				$Qualificacions[$i]->UF[$j]->Codi = utf8_encodeX($row["CodiUF"]);
				$Qualificacions[$i]->UF[$j]->Nom = utf8_encodeX($row["NomUF"]);
				$Qualificacions[$i]->UF[$j]->Hores = utf8_encodeX($row["HoresUF"]);
				// Pedaç per fer quadrar amb l'entructura de CarregaNotes
				$Qualificacions[$i]->UF[$j]->Qualf = ($row["baixa"]) ? 5 : 1;
				$row = $ResultSet->fetch_assoc();
			}
		}
		$ResultSet->close();
		$this->PropostaMatricula = $Qualificacions;
	}

	private function GeneraTaula($Qualificacions): string {
		$Retorn = '';
		if (empty($Qualificacions)) {
			$Retorn = 'No hi ha dades';
		}
		else {
			foreach($Qualificacions as $MP) {
				$Retorn .= str_repeat('&nbsp', 4)."<b>".$MP->Nom."</b><br>".PHP_EOL;
				foreach($MP->UF as $row) {
					$Style = ($row->Qualf < 5) ? "" : "style='color:lightgrey'";
					$Checked = ($row->Qualf < 5) ? 'checked' : '';
					$Retorn .= str_repeat('&nbsp', 8)."<input type=checkbox $Checked disabled>&nbsp;";
					$Retorn .= "<span $Style>".$row->Codi.' '.$row->Nom;
					if ($row->Qualf >= 5)
						$Retorn .= " (aprovat: ".$row->Qualf.")";
					$Retorn .= '</span><br>'.PHP_EOL;
				}
			}
		}
		return $Retorn;
	}
}

?>