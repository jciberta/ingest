<?php

/** 
 * LibNotes.php
 *
 * Llibreria d'utilitats per a les notes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */
 
require_once('LibDB.php');

 /**
 * CreaSQLNotes
 *
 * Crea la sentència SQL per recuperar les notes d'un cicle i un nivell concret.
 *
 * @param string $CicleId Identificador del cicle formatiu.
 * @param string $Nivell Nivell: 1r o 2n.
 * @return string Sentència SQL.
 */
function CreaSQLNotes($CicleId, $Nivell)
{
return ' SELECT M.alumne_id AS AlumneId, '.
	' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
	' UF.codi AS CodiUF, UF.hores AS Hores, '.
	' MP.codi AS CodiMP, '.
	' N.notes_id AS NotaId, N.baixa AS Baixa, N.convocatoria AS Convocatoria, '.
	' N.*, U.* '.
	' FROM NOTES N '.
	' LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
	' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
	' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=N.uf_id) '.
	' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
	' WHERE M.cicle_formatiu_id='.$CicleId.' AND M.nivell='.$Nivell.
	' ORDER BY U.cognom1, U.cognom2, U.nom, MP.codi, UF.codi ';	
//	' ORDER BY M.alumne_id, MP.codi, UF.codi ';	
}
 
 /**
 * ObteTaulaNotesJSON
 *
 * Crea la sentència SQL per recuperar les notes d'un cicle i un nivell concret.
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
		return -100;
	else
		return $Valor;
}
 
 ?>
