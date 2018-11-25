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
 *   2. Si l'alumne és a 2n, l'aplicació ha de buscar les que li han quedar de primer per afegir-les
 * Ús: 
 *
 * @param object $Connexio Connexió a la base de dades.
 * @param integer $CursId Id del curs.
 * @param integer $AlumneId Id de l'alumne.
 * @param integer $CicleId Id del cicle.
 * @param integer $Nivell Nivell (1r o 2n).
 * @param integer $Grup Grup (cap, A, B, C).
 * @return integer Valor de retorn: 0 Ok, -1 Alumne ja matriculat.
 */
function CreaMatricula($Connexio, $Curs, $Alumne, $Cicle, $Nivell, $Grup)
{
	// Comprovem si l'alumne ja està matriculat aquest curs
	$SQL = " SELECT * FROM MATRICULA WHERE curs_id=".$Curs." AND alumne_id=".$Alumne;
//print_r($SQL);
	$ResultSet = $Connexio->query($SQL);
	if ($ResultSet->num_rows == 0) {
		$Connexio->query("START TRANSACTION");

		$SQL = " INSERT INTO MATRICULA (curs_id, alumne_id, cicle_formatiu_id, nivell, grup) ".
			" VALUES (".$Curs.", ".$Alumne.", ".$Cicle.", ".$Nivell.", '".$Grup."') ";
//print_r($SQL);
		$Connexio->query($SQL);
		$iMatriculaId = $Connexio->insert_id;
//print_r($iMatriculaId);

		$SQL = " INSERT INTO NOTES (matricula_id, uf_id, convocatoria) ".
			" SELECT ".$iMatriculaId.", UF.unitat_formativa_id, 1 ".
			" FROM UNITAT_FORMATIVA UF ".
			" LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) ".
			" LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) ".
			" WHERE CF.cicle_formatiu_id=".$Cicle.
			" AND UF.nivell=".$Nivell;	
//print_r($SQL);
		$Connexio->query($SQL);
		
		$Connexio->query("COMMIT");
		return 0;
	}
	else {
		return -1;
	}
}
 
 
 
 ?>
