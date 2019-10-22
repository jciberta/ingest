<?php

/** 
 * LibNotes.php
 *
 * Llibreria d'utilitats per a les notes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * FUNCIONAMEMT DEL LLENÇOL DE NOTES
 * - Quan s'aprova una UF, la convocatòria passa a 0.
 * - Quan es convalida una UF, es posa un 5 a la convocatòria actual i la convocatòria passa a 0.
 */

require_once(ROOT.'/lib/LibArray.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibProfessor.php');
require_once(ROOT.'/lib/LibAvaluacio.php');

/**
 * CreaSQLNotes
 *
 * Crea la sentència SQL per recuperar les notes d'un cicle i un nivell concret.
 *
 * @param string $CicleId Identificador del cicle formatiu.
 * @param string $Nivell Nivell: 1r o 2n.
 * @return string Sentència SQL.
 */
/*function CreaSQLNotes($CursId, $Nivell)
//function CreaSQLNotes($CicleId, $Nivell)
{
	return ' SELECT M.alumne_id AS AlumneId, '.
		' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
		' UF.unitat_formativa_id AS unitat_formativa_id, UF.codi AS CodiUF, UF.hores AS Hores, UF.orientativa AS Orientativa, UF.nivell AS NivellUF, '.
		' MP.codi AS CodiMP, '.
		' N.notes_id AS NotaId, N.baixa AS BaixaUF, N.convocatoria AS Convocatoria, N.convalidat AS Convalidat, '.
		' M.grup AS Grup, M.grup_tutoria AS GrupTutoria, M.baixa AS BaixaMatricula, C.nivell AS NivellMAT, '.
		' N.*, U.* '.
		' FROM NOTES N '.
		' LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
		' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
		' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
		' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=N.uf_id) '.
		' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
		' WHERE C.curs_id='.$CursId.' OR C.curs_id=2 '.
//		' WHERE C.cicle_formatiu_id='.$CicleId.' AND C.nivell>='.$Nivell.
		' ORDER BY C.nivell, U.cognom1, U.cognom2, U.nom, MP.codi, UF.codi ';	
}*/
 
/**
 * ObteTaulaNotesJSON
 *
 * Recupera les notes d'un cicle i un nivell concret en format JSON.
 *
 * @param string $CicleId Identificador del cicle formatiu.
 * @param string $Nivell Nivell: 1r o 2n.
 * @return string Sentència SQL.
 */
function ObteTaulaNotesJSON($Connexio, $CicleId, $Nivell)
{
	$Notes = new Notes($conn, NULL);
	$SQL = $Notes->CreaSQL($CursId, $Nivell);
//	$SQL = CreaSQLNotes($CicleId, $Nivell);

	//return $SQL;
	//print_r($SQL);
	$ResultSet = $Connexio->query($SQL);
	//return $ResultSet;
//print_r($ResultSet);
	return ResultSetAJSON($ResultSet);
} 
 
/**
 * EsNotaValida
 *
 * Comprova si un valor és una nota vàlida. Valors vàlids:
 *   - 1, 2, 3, 4, 5, 6, 7, 8, 9, 10.
 *   - NP (no presentat), A (apte), NA (no apte).
 *   - La cadena nul·la (per esborrar una nota).
 *
 * @param string $Valor Valor a comprovar.
 * @return boolean Cert si és una valor vàlid com a nota.
 */
function EsNotaValida($Valor)
{
	return ((is_numeric($Valor) && ($Valor>0) && ($Valor<=10)) ||
		($Valor == 'NP') ||
		($Valor == 'A') ||
		($Valor == 'NA') ||
		($Valor == ''));
}

/**
 * NotaANumero
 *
 * Transforma una nota al seu valor numèric. Valors numèrics:
 *   - 1, 2, 3, 4, 5, 6, 7, 8, 9, 10.
 *   - NP: -1, A: 100, NA: -100.
 *   - La cadena nul·la passa a ser NULL (per esborrar una nota).
 *
 * @param string $Valor Nota tal com s'entra a l'aplicació (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, NP, A, NA).
 * @return int Retorna el valor numèric o NULL.
 */
function NotaANumero($Valor)
{
	if ($Valor == 'NP') 
		return -1;
	else if ($Valor == 'A') 
		return 100;
	else if ($Valor == 'NA') 
		return -100;
	else if ($Valor == '') 
		return 'NULL';
	else
		return $Valor;
}
 
/**
 * NumeroANota
 *
 * Transforma una nota numèrica al seu valor de text. Valors numèrics:
 *   - 1, 2, 3, 4, 5, 6, 7, 8, 9, 10.
 *   - NP: -1, A: 100, NA: -100.
 *   - NULL passa a ser la cadena nul·la.
 *
 * @param int $Valor Valor numèric o NULL.
 * @return string Retorna la nota tal com s'entra a l'aplicació (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, NP, A, NA).
 */
function NumeroANota($Valor)
{
	if ($Valor == -1) 
		return 'NP';
	else if ($Valor == 100) 
		return 'A';
	else if ($Valor == -100) 
		return 'NA';
	else
		return $Valor;
}

/**
 * UltimaNota
 *
 * Donat un registre de notes, torna la nota de la última convocatòria.
 *
 * @param array $Registre Registre de notes corresponent a un alumne i una UF.
 * @return int Nota de la última convocatòria.
 */
function UltimaNota($Registre)
{
//print_r($Registre['nota5']);
	if ($Registre['nota5'] != '') 
		return $Registre['nota5'];
	else if ($Registre['nota4'] != '') 
		return $Registre['nota4'];
	else if ($Registre['nota3'] != '') 
		return $Registre['nota3'];
	else if ($Registre['nota2'] != '') 
		return $Registre['nota2'];
	else if ($Registre['nota1'] != '') 
		return $Registre['nota1'];
	else 
		return '';
}

/**
 * Classe que encapsula les utilitats per al maneig de les notes.
 */
class Notes 
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
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}	
	
	/**
	 * Escriu el formulari corresponent a les notes d'un cicle i nivell.
	 * @param string $CicleId Identificador del cicle formatiu.
	 * @param string $Nivell Nivell: 1r o 2n.
	 * @param array $Notes Dades amb les notes.
	 * @param int $IdGraella Identificador de la graella de notes.
	 * @param object $Professor Objecte professor.
	 * @param string $EstatAvaluacio Estat de l'avaluació (ordinària, extraordinària, tancada).
	 * @return void.
	 */
	public function EscriuFormulari($CicleId, $Nivell, $Notes, $IdGraella, $Professor, string $EstatAvaluacio) {
		// Formulari amb les notes
		echo '<DIV id=notes'.$IdGraella.'>';
		echo '<FORM id=form'.$IdGraella.' method="post" action="">';
		echo '<input type=hidden id=CicleId value='.$CicleId.'>';
		echo '<input type=hidden id=Nivell value='.$Nivell.'>';
		echo '<TABLE id="TaulaNotes" border=0>';

		// Capçalera de la taula
		$aModuls = [];
		$aModulsNom = [];

		// CAI2 no existeix com a tal. Tots els crèdits estan posats a 1r
		if (!property_exists($Notes, 'UF')) return;
	
		for($j = 0; $j < count($Notes->UF[0]); $j++) {
			$row = $Notes->UF[0][$j];
			$aModuls[$j] = utf8_encode($row["CodiMP"]);
			$aModulsNom[$j] = utf8_encode($row["NomMP"]);
		}
		$aOcurrenciesModuls = Ocurrencies($aModuls);
//print_r($aOcurrenciesModuls);
//print_r($aModulsNom);

		// Mòdul
		echo "<TR><TD></TD><TD></TD><TD></TD><TD></TD>";
		$index = 0;
		for($i = 0; $i < count($aOcurrenciesModuls); $i++) {
			$iOcurrencies = $aOcurrenciesModuls[$i][1];
			echo '<TD width='.($iOcurrencies*25).' colspan='.$iOcurrencies.' data-toggle="tooltip" data-placement="top" title="'.$aModulsNom[$index].'">'.utf8_encode($aOcurrenciesModuls[$i][0]).'</TD>';
			$index += $iOcurrencies;
		}
		echo "<TD></TD></TR>";
	
		// Unitat formativa
		echo "<TR><TD></TD><TD></TD><TD></TD><TD></TD>";
		for($j = 0; $j < count($Notes->UF[0]); $j++) {
			$row = $Notes->UF[0][$j];
			echo '<TD width=20 style="text-align:center" data-toggle="tooltip" data-placement="top" title="'.utf8_encode($row["NomUF"]).'">'.utf8_encode($row["CodiUF"]).'</TD>';
		}
		echo "<TD style='text-align:center' colspan=2>Hores</TD></TR>";

		// Hores
		echo "<TR><TD></TD><TD></TD>";
		echo "<TD style='text-align:center'>Grup</TD>";
		echo "<TD style='text-align:center'>Tutoria</TD>";
		$TotalHores = 0;
		$aHores = []; // Array d'hores per posar-ho com a element ocult (format JSON) a l'HTML i poder-ho obtenir des de JavaScript.
		for($j = 0; $j < count($Notes->UF[0]); $j++) {
			$row = $Notes->UF[0][$j];
			$TotalHores += $row["Hores"];
			echo "<TD width=20 align=center>".$row["Hores"]."</TD>";
			array_push($aHores, $row["Hores"]);
		}
		echo "<TD style='text-align:center'>".$TotalHores."</TD>";
		echo "<TD style='text-align:center'>&percnt;</TD></TR>";

		for($i = 0; $i < count($Notes->Alumne); $i++) {
			$row = $Notes->Alumne[$i];
			if ($row["NivellMAT"] == $Nivell) {
				echo "<TR name='Baixa".$row["BaixaMatricula"]."'>";
				$Color = ($row["BaixaMatricula"] == 1) ? ';color:lightgrey' : '';
//				echo "<TD>".utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"])."</TD>";
				echo "<TD style='text-align:left$Color'>".utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"])."</TD>";

				if ($row["BaixaMatricula"] == 1)
					echo "<TD></TD>";
				else
					echo "<TD><A href='MatriculaAlumne.php?accio=MostraExpedient&MatriculaId=".$row["matricula_id"]."'><IMG src=img/grades-sm.svg></A></TD>";

				echo "<TD style='text-align:center$Color'>".$row["Grup"]."</TD>";
				echo "<TD style='text-align:center$Color'>".$row["GrupTutoria"]."</TD>";
				$Hores = 0;
				for($j = 0; $j < count($Notes->UF[$i]); $j++) {
					$row = $Notes->UF[$i][$j];
					echo self::CreaCellaNota($IdGraella, $i, $j, $row, $Professor, $Hores, $EstatAvaluacio);
				}
				$Id = 'grd'.$IdGraella.'_TotalHores_'.$i;
				echo '<TD id="'.$Id.'" style="text-align:center;color:grey">'.$Hores.'</TD>';
				$Id = 'grd'.$IdGraella.'_TotalPercentatge_'.$i;
				$TotalPercentatge = $Hores/$TotalHores*100;
				$Color = (($TotalPercentatge>=60 && $Nivell==1) ||($TotalPercentatge>=100 && $Nivell==2)) ? ';background-color:lightgreen' : '';
				echo '<TD id="'.$Id.'" style="text-align:center'.$Color.'">'.number_format($TotalPercentatge, 2).'&percnt;</TD>';
				if ($this->Usuari->es_admin || $this->Usuari->es_direccio || $this->Usuari->es_cap_estudis) {
					$onClick = "AugmentaConvocatoriaFila($i, $IdGraella)";
					echo "<TD><A href=# onclick='".$onClick."'>[PassaConv]</A></TD></TR>";
				}
				else
					echo "<TD></TD></TR>";
			}
		}
		echo "</TABLE>";
		echo "<input type=hidden name=TempNota value=''>";
		echo "<input type=hidden id='grd".$IdGraella."_ArrayHores' value='".ArrayIntAJSON($aHores)."'>";
		echo "<input type=hidden id='grd".$IdGraella."_TotalHores' value=".$TotalHores.">";
		echo "<input type=hidden id='grd".$IdGraella."_Nivell' value=".$Nivell.">";
		echo "</FORM>";
		echo "</DIV>";
	}
	
	/**
	 * Crea una cel·la de la taula de notes amb tota la seva casuística.
	 * @param string $IdGraella Nom de la graella.
	 * @param integer $i Columna.
	 * @param integer $j Fila.
	 * @param object $row Registre que correspon a la nota.
	 * @param object $Professor Objecte de la classe Professor.
	 * @param integer $Hores Hores que es sumen per saber el total.
	 * @param string $EstatAvaluacio Estat de l'avaluació (ordinària, extraordinària, tancada).
	 * @return string Codi HTML de la cel·la.
	 */
	public static function CreaCellaNota(string $IdGraella, int $i, int $j, $row, $Professor, int &$Hores, string $EstatAvaluacio): string {
		$style = "text-align:center;text-transform:uppercase";
		$Baixa = (($row["BaixaUF"] == 1) || ($row["BaixaMatricula"] == 1));
		$Convalidat = ($row["Convalidat"] == True);

		$Deshabilitat = '';
		if ($Baixa)
			$Deshabilitat = ' disabled ';
		else if (!$Professor->TeUF($row["unitat_formativa_id"]) && !$Professor->EsAdmin() && !$Professor->EsDireccio() && !$Professor->EsCapEstudis())
			$Deshabilitat = ' disabled ';

		$Nota = '';
		$ToolTip = ''; // L'usarem per indicar la nota anterior quan s'ha recuperat
		if (!$Baixa) {
			if ($Convalidat) {
				$Nota = UltimaNota($row);
				$Deshabilitat = " disabled ";
				$style .= ";background-color:blue;color:white";
			}
			else if ($row["Convocatoria"] == 0) {
				$Nota = UltimaNota($row);
				$Deshabilitat = " disabled ";
				$style .= ";background-color:black;color:white";
			}
			else if ($row["Convocatoria"] < self::UltimaConvocatoriaNota($row) && self::UltimaConvocatoriaNota($row) != -999) {
				// Nota recuperada
				$Nota = UltimaNota($row);
				$Deshabilitat = " disabled ";
				$style .= ";background-color:lime";
				$ToolTip = 'data-toggle="tooltip" title="Nota anterior: '.$row["nota".$row["Convocatoria"]].'"';
			}
			else {
				$Nota = $row["nota".$row["Convocatoria"]];
				if ($row["Orientativa"] && !$Baixa) {
					$style .= ";background-color:yellow";
				}
			}
		}
		else
			// Sense nota
			$style .= ";background-color:grey";
		if ($Nota >= 5)
			$Hores += $row["Hores"];
		else if ($Nota!='' && $Nota>=0 && $Nota<5)
			$style .= ";color:red";
		
		// Si l'avaluació (el curs) està tancada, tot deshabilitat.
		$Deshabilitat = ($EstatAvaluacio == Avaluacio::Tancada) ? ' disabled ' : $Deshabilitat;
		
		// <INPUT>
		// name: conté id i convocatòria
		// id: conté les coordenades x, y. Inici a (0, 0).
		$ValorNota = NumeroANota($Nota);
		$Id = 'grd'.$IdGraella.'_'.$i.'_'.$j;
		return "<TD width=2><input type=text ".$Deshabilitat." style='".$style."'".
			" name=txtNotaId_".$row["NotaId"]."_".$row["Convocatoria"].
			" id='".$Id."' value='".$ValorNota."' size=1 ".$ToolTip.
			" onfocus='EnEntrarCellaNota(this);' onBlur='EnSortirCellaNota(this);' onkeydown='NotaKeyDown(this, event);'></TD>";
	}

	/**
	 * Donat un registre de notes, torna la última convocatòria.
     * Si la convocatòria és 0, torna la que té l'ultima nota.
	 * @param array $Registre Registre de notes corresponent a un alumne i una UF.
	 * @return int Última convocatòria.
	 */
	public static function UltimaConvocatoria($Registre) {
		if ($Registre['convocatoria'] != 0) 
			return $Registre['convocatoria'];
		else if ($Registre['nota5'] != '') 
			return 5;
		else if ($Registre['nota4'] != '') 
			return 4;
		else if ($Registre['nota3'] != '') 
			return 3;
		else if ($Registre['nota2'] != '') 
			return 2;
		else if ($Registre['nota1'] != '') 
			return 1;

		// Cas per quan s'usen àlies a la SQL
		if ($Registre['Convocatoria'] != 0) 
			return $Registre['Convocatoria'];
		else if ($Registre['Nota5'] != '') 
			return 5;
		else if ($Registre['Nota4'] != '') 
			return 4;
		else if ($Registre['Nota3'] != '') 
			return 3;
		else if ($Registre['Nota2'] != '') 
			return 2;
		else if ($Registre['Nota1'] != '') 
			return 1;

		else 
			return -999;
	}
	
	/**
	 * Donat un registre de notes, torna la última convocatòria amb nota.
	 * @param array $Registre Registre de notes corresponent a un alumne i una UF.
	 * @return int Última convocatòria.
	 */
	public static function UltimaConvocatoriaNota($Registre) {
//print_r($Registre);
		if ($Registre['nota5'] != '') 
			return 5;
		else if ($Registre['nota4'] != '') 
			return 4;
		else if ($Registre['nota3'] != '') 
			return 3;
		else if ($Registre['nota2'] != '') 
			return 2;
		else if ($Registre['nota1'] != '') 
			return 1;
/*
		// Cas per quan s'usen àlies a la SQL
		if ($Registre['Nota5'] != '') 
			return 5;
		else if ($Registre['Nota4'] != '') 
			return 4;
		else if ($Registre['Nota3'] != '') 
			return 3;
		else if ($Registre['Nota2'] != '') 
			return 2;
		else if ($Registre['Nota1'] != '') 
			return 1;
*/
		else 
			return -999;
	}
	
	public static function CreaMenuContextual($Usuari) {
		// Adaptat de http://jsfiddle.net/KyleMit/X9tgY/
		echo '<ul id="contextMenu" class="dropdown-menu dropdown-menu-sm" role="menu" style="display:none" >';
		echo '    <li><a class="dropdown-item" id="ddi_IntrodueixRecuperacio" href="#">Introdueix recuperació</a></li>';
		if ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis) {
			echo '    <li><a class="dropdown-item" id="ddi_NotaAnterior" href="#">Marca com a nota anterior (convocatòria a 0)</a></li>';
			echo '    <li><a class="dropdown-item" id="ddi_Convalida" href="#">Convalida</a></li>';
		}
		echo '</ul>';


		echo '<script>';
		echo '$("#TaulaNotes input").contextMenu({';
		echo '    menuSelector: "#contextMenu",';
		echo '    menuSelected: function (invokedOn, selectedMenu) {';
//echo 'console.dir(selectedMenu);';
		echo '        if (selectedMenu[0].id == "ddi_IntrodueixRecuperacio")';
		echo '            IntrodueixRecuperacio(invokedOn);';
		echo '        else if (selectedMenu[0].id == "ddi_NotaAnterior")';
		echo '            MarcaComNotaAnterior(invokedOn);';
		echo '        else if (selectedMenu[0].id == "ddi_Convalida")';
		echo '            Convalida(invokedOn);';
		echo '    }';
		echo '});';
		echo '</script>';
	}
	
	/**
	 * Donat un 1r curs, retorna l'identificador del 2n curs per a aquell any i cicle.
	 * Si no el troba, retorna -1.
	 * @return int Identificador del 2n curs.
	 */
	private function ObteSegonCurs(int $CursId): int {
		$iRetorn = -1;
		
		$SQL = 'SELECT curs_id FROM CURS C '.
		' LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id) '.
		' WHERE cicle_formatiu_id in ( '.
		' 	SELECT cicle_formatiu_id FROM CURS C '.
		' 	LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id) '.
		' 	WHERE curs_id='.$CursId.
		' ) '.
		' AND any_inici in ( '.
		' 	SELECT any_inici FROM CURS C '.
		' 	LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id) '.
		' 	WHERE curs_id='.$CursId.
		' ) '.
		' AND nivell=2 ';
//print_r($SQL.'<HR>');
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$obj = $ResultSet->fetch_object();
			$iRetorn = $obj->curs_id; 
			if ($iRetorn == $CursId)
				$iRetorn = -1;
		}
		
		return $iRetorn;
	}	
	
	/**
	 * CreaSQL
	 *
	 * Crea la sentència SQL per recuperar les notes d'un curs i un nivell concret.
	 *
	 * @param string $CursId Identificador del curs del cicle formatiu.
	 * @param string $Nivell Nivell: 1r o 2n.
	 * @return string Sentència SQL.
	 */
	public function CreaSQL($CursId, $Nivell)
	{
		$iSegonCurs = $this->ObteSegonCurs($CursId);
		$sRetorn = ' SELECT M.alumne_id AS AlumneId, '.
			' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
			' UF.unitat_formativa_id AS unitat_formativa_id, UF.codi AS CodiUF, UF.nom AS NomUF, UF.hores AS Hores, UF.orientativa AS Orientativa, UF.nivell AS NivellUF, '.
			' MP.codi AS CodiMP, MP.nom AS NomMP, '.
			' N.notes_id AS NotaId, N.baixa AS BaixaUF, N.convocatoria AS Convocatoria, N.convalidat AS Convalidat, '.
			' M.matricula_id, M.grup AS Grup, M.grup_tutoria AS GrupTutoria, M.baixa AS BaixaMatricula, C.nivell AS NivellMAT, '.
			' N.*, U.* '.
			' FROM NOTES N '.
			' LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=N.uf_id) '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
			' WHERE C.curs_id='.$CursId;
		if ($iSegonCurs>0)
			$sRetorn .= ' OR C.curs_id='.$iSegonCurs;
		$sRetorn .= ' ORDER BY C.nivell, U.cognom1, U.cognom2, U.nom, MP.codi, UF.codi ';	
			
		return $sRetorn;
	}
}

?>
