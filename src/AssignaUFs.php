<?php

/** 
 * AssignaUFs.php
 *
 * Assignació d'unitats formatives a professors.
 *
 * GET:
 * - accio: {AssignaUF, ProfessorsUF}.
 * - ProfessorId: Id del professor per a l'acció AssignaUF.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibHTML.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

//var_dump($_GET);
//print_r($_GET);

$Accio = $_GET['accio'];

if ($Accio == 'AssignaUF') {
	// Assigna diferents UF a un professor.
	$ProfessorId = $_GET['ProfessorId'];
//print_r($ProfessorId);

	CreaIniciHTML($Usuari, "Assignació d'unitats formatives");
	echo '<script language="javascript" src="js/Professor.js" type="text/javascript"></script>';

	$SQL = ' SELECT  UF.nom AS NomUF, UF.hores AS HoresUF, MP.nom AS NomMP, CF.nom AS NomCF, '.
		' MP.codi AS CodiMP, MP.nom AS NomMP, '.
		' CF.nom AS NomCF, CF.cicle_formatiu_id AS CicleFormatiuId, CF.codi AS CodiCF, '.
		' PUF.professor_uf_id AS ProfessorUFId, '.
		' U.nom AS NomProfessor, U.cognom1 AS Cognom1Professor, U.cognom2 AS Cognom2Professor, '.
		' UF.*, MP.*, CF.* '.
		' FROM UNITAT_FORMATIVA UF '.
		' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
		' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
		' LEFT JOIN PROFESSOR_UF PUF ON (UF.unitat_formativa_id=PUF.uf_id AND PUF.professor_id='.$ProfessorId.') '.
		' LEFT JOIN USUARI U ON (U.usuari_id='.$ProfessorId.') '.
		' ORDER BY CF.codi, MP.codi, UF.codi ';

	$ResultSet = $conn->query($SQL);
	if ($ResultSet->num_rows > 0) {

		// Creem un objecte per administrar els cicles
		$Cicles = new stdClass();
		$i = -1; 
		$j = 0;
		$CicleFormatiuId = -1;
		$row = $ResultSet->fetch_assoc();
		$NomProfessor = utf8_encode($row["NomProfessor"]." ".$row["Cognom1Professor"]." ".$row["Cognom2Professor"]);
		while($row) {
			if ($row["CicleFormatiuId"] != $CicleFormatiuId) {
				$CicleFormatiuId = $row["CicleFormatiuId"];
				$i++;
	//			$Cicles->CF = $row['CodiCF'];
	//			$Cicles->Nom = $row['NomCF'];
				$Cicles->CF[$i] = $row;
				$j = 0; 
			}	
			$Cicles->UF[$i][$j] = $row;
			$j++;
			$row = $ResultSet->fetch_assoc();
		}	
		
		echo '<div class="alert alert-primary" role="alert">Professor: <B>'.$NomProfessor.'</B></div>';

		// Creem una llista dels cicles formatius amb les UFs amb col·lapsament.
		// https://getbootstrap.com/docs/4.1/components/collapse/
		echo '<div class="accordion" id="accordionExample">';

		for($i = 0; $i < count($Cicles->CF); $i++) {
			$row = $Cicles->CF[$i];
			echo '  <div class="card">';
			echo '    <div class="card-header" id="'.$row['CodiCF'].'">';
			echo '      <h5 class="mb-0">';
			echo '        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$row['CodiCF'].'" aria-expanded="true" aria-controls="collapse'.$row['CodiCF'].'">';
			echo utf8_encode($row['NomCF']);
			echo '        </button>';
			echo '      </h5>';
			echo '    </div>';
			echo '    <div id="collapse'.$row['CodiCF'].'" class="collapse" aria-labelledby="'.$row['CodiCF'].'" data-parent="#accordionExample">';
			echo '      <div class="card-body">';

			echo '<TABLE class="table table-striped table-sm table-hover">';
			echo '<thead class="thead-dark">';
			echo "<TH>Mòdul</TH>";
			echo "<TH>Unitat formativa</TH>"; 
			echo "<TH></TH>"; 
			echo '</thead>';
			$ModulAnterior = '';
			for($j = 0; $j < count($Cicles->UF[$i]); $j++) {
				$row = $Cicles->UF[$i][$j];
				echo "<TR>";

				if ($row["CodiMP"] != $ModulAnterior)
					echo "<TD>".utf8_encode($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
				else 
					echo "<TD></TD>";
				$ModulAnterior = $row["CodiMP"];

				echo "<TD>".utf8_encode($row["NomUF"])."</TD>";
				$Checked = ($row["ProfessorUFId"] > 0)? ' checked ' : '';
				$Nom = 'chbUFId_'.$row["unitat_formativa_id"].'_'.$ProfessorId;
				echo "<TD><input type=checkbox name=".$Nom.$Checked." onclick='AssignaUF(this);'/></TD>";

	//			echo "<TD width=2><input type=text name=txtNotaId_".$row["NotaId"]."_".$row["Convocatoria"]." value='".$ValorNota."' size=1 ".$Deshabilitat." onBlur='ActualitzaNota(this);'></TD>";
				echo "</TR>";
			}
			echo "</TABLE>";
			echo '      </div>';
			echo '    </div>';
			echo '  </div>';		
		}
		echo '</div>';	
	};
}
else if ($Accio == 'ProfessorsUF') {
	// Mostra els professors que hi ha assignats a les diferents UF.
	CreaIniciHTML($Usuari, "Professors per unitats formatives");
	echo '<script language="javascript" src="js/Professor.js" type="text/javascript"></script>';

	$SQL = ' SELECT  UF.nom AS NomUF, UF.hores AS HoresUF, '.
		' MP.codi AS CodiMP, MP.nom AS NomMP, '.
		' CF.nom AS NomCF, '.
		' U.nom AS Nom, U.cognom1 AS Cognom1, U.cognom2 AS Cognom2, '.
		' CF.cicle_formatiu_id AS CicleFormatiuId, CF.codi AS CodiCF, '.
		' UF.unitat_formativa_id AS UnitatFormativaId, '.
		' PUF.professor_uf_id AS ProfessorUFId, '.
		' UF.*, MP.*, CF.* '.
		' FROM UNITAT_FORMATIVA UF '.
		' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
		' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
		' LEFT JOIN PROFESSOR_UF PUF ON (UF.unitat_formativa_id=PUF.uf_id) '.
		' LEFT JOIN USUARI U ON (U.usuari_id=PUF.professor_id) '.
		' ORDER BY CF.codi, MP.codi, UF.codi ';
	$ResultSet = $conn->query($SQL);
	if ($ResultSet->num_rows > 0) {
		// Creem un objecte per administrar els cicles
		$Cicles = new stdClass();
		$i = -1; 
		$j = 0;
		$CicleFormatiuIdAnterior = -1;
		$UnitatFormativaId = -1;
		$row = $ResultSet->fetch_assoc();
		while($row) {
			if ($row["CicleFormatiuId"] != $CicleFormatiuIdAnterior) {
				$CicleFormatiuIdAnterior = $row["CicleFormatiuId"];
				$i++;
				$Cicles->CF[$i] = $row;
				$j = 0; 
			}
			if ($row["UnitatFormativaId"] == $UnitatFormativaId) {
				$Cicles->UF[$i][$j-1]->NomComplet .= utf8_encode(', '.$row['Nom'].' '.$row['Cognom1'].' '.$row['Cognom2']);
			}
			else {
				$UnitatFormativaId = $row['UnitatFormativaId'];
				$Cicles->UF[$i][$j] = new stdClass();
				$Cicles->UF[$i][$j]->Dades = $row;
				$Cicles->UF[$i][$j]->NomComplet = utf8_encode($row['Nom'].' '.$row['Cognom1'].' '.$row['Cognom2']);
				$j++;
			}
			$row = $ResultSet->fetch_assoc();
		}	
		
		// Creem una llista dels cicles formatius amb les UFs amb col·lapsament.
		// https://getbootstrap.com/docs/4.1/components/collapse/
		echo '<div class="accordion" id="accordionExample">';

		for($i = 0; $i < count($Cicles->CF); $i++) {
			$row = $Cicles->CF[$i];
			echo '  <div class="card">';
			echo '    <div class="card-header" id="'.$row['CodiCF'].'">';
			echo '      <h5 class="mb-0">';
			echo '        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$row['CodiCF'].'" aria-expanded="true" aria-controls="collapse'.$row['CodiCF'].'">';
			echo utf8_encode($row['NomCF']);
			echo '        </button>';
			echo '      </h5>';
			echo '    </div>';
			echo '    <div id="collapse'.$row['CodiCF'].'" class="collapse" aria-labelledby="'.$row['CodiCF'].'" data-parent="#accordionExample">';
			echo '      <div class="card-body">';

			echo '<TABLE class="table table-striped table-sm table-hover">';
			echo '<thead class="thead-dark">';
			echo "<TH>Modul</TH>";
			echo "<TH>Unitat formativa</TH>"; 
			echo "<TH>Professors</TH>"; 
			echo '</thead>';
			$ModulAnterior = '';
			for($j = 0; $j < count($Cicles->UF[$i]); $j++) {
				$NomComplet = $Cicles->UF[$i][$j]->NomComplet;
//print_r($NomComplet);
				$row = $Cicles->UF[$i][$j]->Dades;
				echo "<TR>";
				if ($row["CodiMP"] != $ModulAnterior)
					echo "<TD>".utf8_encode($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
				else 
					echo "<TD></TD>";
				$ModulAnterior = $row["CodiMP"];
				echo "<TD>".utf8_encode($row["NomUF"])."</TD>";
				echo "<TD>".$NomComplet."</TD>";
				echo "</TR>";
			}
			echo "</TABLE>";
			echo '      </div>';
			echo '    </div>';
			echo '  </div>';		
		}
		echo '</div>';	
	};
}

echo "<DIV id=debug></DIV>";

?>
