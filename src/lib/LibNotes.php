<?php

/** 
 * LibNotes.php
 *
 * Llibreria d'utilitats per a les notes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibProfessor.php');

/**
 * CreaSQLNotes
 *
 * Crea la sentència SQL per recuperar les notes d'un cicle i un nivell concret.
 * Es fa així (i no per curs) per obtenir també els alumnes d'un altre curs que també fan les UFs.
 *
 * @param string $CicleId Identificador del cicle formatiu.
 * @param string $Nivell Nivell: 1r o 2n.
 * @return string Sentència SQL.
 */
function CreaSQLNotes($CicleId, $Nivell)
{
	return ' SELECT M.alumne_id AS AlumneId, '.
		' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
		' UF.unitat_formativa_id AS unitat_formativa_id, UF.codi AS CodiUF, UF.hores AS Hores, UF.orientativa AS Orientativa, UF.nivell AS NivellUF, '.
		' MP.codi AS CodiMP, '.
		' N.notes_id AS NotaId, N.baixa AS BaixaUF, N.convocatoria AS Convocatoria, '.
		' M.grup AS Grup, M.grup_tutoria AS GrupTutoria, M.baixa AS BaixaMatricula, C.nivell AS NivellMAT, '.
		' N.*, U.* '.
		' FROM NOTES N '.
		' LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
		' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
		' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
		' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=N.uf_id) '.
		' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
		' WHERE C.cicle_formatiu_id='.$CicleId.' AND C.nivell>='.$Nivell.
		' ORDER BY C.nivell, U.cognom1, U.cognom2, U.nom, MP.codi, UF.codi ';	
}
 
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
	$SQL = CreaSQLNotes($CicleId, $Nivell);
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
	 * Escriu el formulari corresponent a les notes d'un cicle i nivell.
	 * @param string $CicleId Identificador del cicle formatiu.
	 * @param string $Nivell Nivell: 1r o 2n.
	 * @param array $Notes Dades amb les notes.
	 * @param int $IdGraella Identificador de la graella de notes.
	 * @param object $Professor Objecte professor.
	 * @return void.
	 */
	public static function EscriuFormulari($CicleId, $Nivell, $Notes, $IdGraella, $Professor) {
		// Formulari amb les notes
		echo '<DIV id=notes'.$IdGraella.'>';
		echo '<FORM id=form'.$IdGraella.' method="post" action="">';
		echo '<input type=hidden id=CicleId value='.$CicleId.'>';
		echo '<input type=hidden id=Nivell value='.$Nivell.'>';
		echo '<TABLE border=0>';

		// Capçalera de la taula
		$aModuls = [];
		for($j = 0; $j < count($Notes->UF[0]); $j++) {
			$row = $Notes->UF[0][$j];
			$aModuls[$j] = utf8_encode($row["CodiMP"]);
		}
		$aOcurrenciesModuls = Ocurrencies($aModuls);
	//print_r($aOcurrenciesModuls);

		// Mòdul
		echo "<TR><TD></TD><TD></TD><TD></TD>";
		for($i = 0; $i < count($aOcurrenciesModuls); $i++) {
			$iOcurrencies = $aOcurrenciesModuls[$i][1];
			echo "<TD width=".($iOcurrencies*25)." colspan=".$iOcurrencies.">".utf8_encode($aOcurrenciesModuls[$i][0])."</TD>";
		}
		echo "<TD></TD></TR>";
	
		// Unitat formativa
		echo "<TR><TD></TD><TD></TD><TD></TD>";
		for($j = 0; $j < count($Notes->UF[0]); $j++) {
			$row = $Notes->UF[0][$j];
			echo "<TD width=20 style='text-align:center'>".utf8_encode($row["CodiUF"])."</TD>";
		}
		echo "<TD style='text-align:center' colspan=2>Hores</TD></TR>";

		// Hores
		echo "<TR><TD></TD>";
		echo "<TD style='text-align:center'>Grup</TD>";
		echo "<TD style='text-align:center'>Tutoria</TD>";
		$TotalHores = 0;
		for($j = 0; $j < count($Notes->UF[0]); $j++) {
			$row = $Notes->UF[0][$j];
			$TotalHores += $row["Hores"];
			echo "<TD width=20 align=center>".$row["Hores"]."</TD>";
		}
		echo "<TD style='text-align:center'>".$TotalHores."</TD>";
		echo "<TD style='text-align:center'>&percnt;</TD></TR>";

		for($i = 0; $i < count($Notes->Alumne); $i++) {
			$row = $Notes->Alumne[$i];
			if ($row["NivellMAT"] == $Nivell) {
				echo "<TR>";
				echo "<TD>".utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"])."</TD>";
				echo "<TD style='text-align:center'>".$row["Grup"]."</TD>";
				echo "<TD style='text-align:center'>".$row["GrupTutoria"]."</TD>";
				$Hores = 0;
				for($j = 0; $j < count($Notes->UF[$i]); $j++) {
					$row = $Notes->UF[$i][$j];
					$style = "text-align:center;text-transform:uppercase";
					$Baixa = (($row["BaixaUF"] == 1) || ($row["BaixaMatricula"] == 1));

					$Deshabilitat = '';
					if ($Baixa)
						$Deshabilitat = ' disabled ';
					else if (!$Professor->TeUF($row["unitat_formativa_id"]) && !$Professor->EsAdmin() && !$Professor->EsDireccio() && !$Professor->EsCapEstudis())
						$Deshabilitat = ' disabled ';

					$Nota = '';
					if (!$Baixa) {
						if ($row["Convocatoria"] == 0) {
							$Nota = UltimaNota($row);
							$Deshabilitat = " disabled ";
							$style .= ";background-color:black;color:white";
						}
						else {
							$Nota = $row["nota".$row["Convocatoria"]];
							if ($row["Orientativa"] && !$Baixa) {
								$style .= ";background-color:yellow";
							}
						}
					}
					else
						$style .= ";background-color:grey";
					if ($Nota >= 5)
						$Hores += $row["Hores"];
					$ValorNota = NumeroANota($Nota);
					$Id = 'grd'.$IdGraella.'_'.$i.'_'.$j;
					echo "<TD width=2><input type=text ".$Deshabilitat." style='".$style."' name=txtNotaId_".$row["NotaId"]."_".$row["Convocatoria"]." id='".$Id."' value='".$ValorNota."' size=1 onfocus='ObteNota(this);' onBlur='ActualitzaNota(this);' onkeydown='NotaKeyDown(this, event);'></TD>";
				}
				echo "<TD style='text-align:center'>".$Hores."</TD>";
				echo "<TD style='text-align:center'>".number_format($Hores/$TotalHores*100, 2)."&percnt;</TD>";
				echo "<TD></TD></TR>";
			}
		}
		echo "</TABLE>";
		echo "<input type=hidden name=TempNota value=''>";
		echo "</FORM>";
		echo "</DIV>";
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
}

?>