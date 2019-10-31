<?php

/** 
 * MatriculaAlumne.php
 *
 * Visualitza la matrícula/expedient d'un alumne.
 *
 * GET:
 * - AlumneId: Id de l'alumne. NO!
 * - MatriculaId: Id de la matrícula de l'alumne.
 * - accio: {MatriculaUF, MostraExpedient}.
 * POST:
 * - alumne: Id de l'alumne. NO!
 * - MatriculaId: Id de la matrícula de l'alumne.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibUsuari.php');
require_once(ROOT.'/lib/LibNotes.php');
require_once(ROOT.'/lib/LibExpedient.php');
require_once(ROOT.'/lib/LibMatricula.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

//print_r($_GET);

if (!empty($_POST)) {
//	$alumne = $_POST['alumne'];
	$MatriculaId = $_POST['MatriculaId'];
}
else {
//	$alumne = $_GET['AlumneId'];
	$MatriculaId = $_GET['MatriculaId'];
}

$Matricula = new Matricula($conn, $Usuari);
$alumne = $Matricula->ObteAlumne($MatriculaId);

//echo "<BR><BR><BR>";
//echo "alumne:".$alumne."<BR>";
//echo "MatriculaId:".$MatriculaId."<BR>";

$accio = (isset($_GET) && array_key_exists('accio', $_GET)) ? $_GET['accio'] : '';
$ActivaEdicio = (isset($_GET) && array_key_exists('ActivaEdicio', $_GET)) ? $_GET['ActivaEdicio'] : '';

// Si intenta manipular l'usuari des de la URL -> al carrer!
if (($Usuari->es_alumne) && ($Usuari->usuari_id != $alumne))
	header("Location: Surt.php");

$objUsuari = new Usuari($conn, $Usuari);
if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor && !$Usuari->es_alumne && !($Usuari->es_pare && $objUsuari->EsProgenitor($alumne)))
	header("Location: Surt.php");

// L'edició de l'expedient només la pot fer l'administrador
if (!$Usuari->es_admin && $ActivaEdicio==1)
	header("Location: Surt.php");

if ($accio == 'MostraExpedient')
	CreaIniciHTML($Usuari, 'Visualitza expedient');
else
	CreaIniciHTML($Usuari, 'Visualitza matrícula');
	
echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
echo '<script language="javascript" src="js/Matricula.js?v1.3" type="text/javascript"></script>';
echo '<script language="javascript" src="js/Notes.js?v1.2" type="text/javascript"></script>';

// L'alumne i el pare només poden veure les notes quan s'ha activat la visibilitat dels butlletins per a aquell curs
$ButlletiVisible = True;
if ($Usuari->es_alumne || $Usuari->es_pare) {
	$Expedient = new Expedient($conn);
	$ButlletiVisible = $Expedient->EsVisibleButlleti($alumne);
}

if ($ButlletiVisible) {
	$SQL = Expedient::SQL($MatriculaId);
//print_r($SQL);

	$ResultSet = $conn->query($SQL);

	if ($ResultSet->num_rows > 0) {
		$row = $ResultSet->fetch_assoc();
		$NomComplet = trim(utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"]));
		if ($CFG->Debug)
			$NomComplet .= " (".$row["usuari_id"].")";
		echo '<div class="alert alert-primary" role="alert">';
		echo 'Alumne: <B>'.$NomComplet.'</B><BR>';
		echo 'Cicle: <B>'.utf8_encode($row["NomCF"]).'</B>';
		echo '</div>';

		echo '<TABLE class="table table-sm table-striped table-hover">';
		echo '<thead class="thead-dark">';
		echo "<TH>Mòdul</TH>";
		echo "<TH>UF</TH>";
		echo "<TH>Nivell</TH>";
		echo "<TH>Hores</TH>";
		if ($accio == 'MostraExpedient') {
			echo "<TH colspan=5>Notes</TH>";
			if ($ActivaEdicio==1)
				echo "<TH>Convocatòria</TH>";
		}
		else {
			echo "<TH>Matrícula</TH>";
			echo "<TH>Convalidació</TH>";
		}
		echo '</thead>';

		$ModulAnterior = '';
		$j = 1;
		while($row) {
			echo "<TR>";
	//		echo "<TD>".utf8_encode($row["NomCF"])."</TD>";
			if ($row["CodiMP"] != $ModulAnterior)
				echo "<TD>".utf8_encode($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
			else 
				echo "<TD></TD>";
			$ModulAnterior = $row["CodiMP"];
			echo "<TD>".utf8_encode($row["NomUF"])."</TD>";
			echo "<TD>".$row["NivellUF"]."</TD>";
			echo "<TD>".$row["HoresUF"]."</TD>";
			$Baixa = ($row["Baixa"] == True);
			if ($Baixa) 
				$sChecked = '';
			else
				$sChecked = ' checked';
			$Convalidat = ($row["Convalidat"] == True);
			$sCheckedConvalidat = $Convalidat ? ' checked disabled' : '';
			if ($accio == 'MostraExpedient') {
				for ($i=1; $i<6; $i++) {
					$style = 'width:2em;text-align:center';
					if (($row['convocatoria'] == $i) && (!$Baixa)) {
						// Marquem la convocatòria actual
						$style .= ';border-width:1px;border-color:blue';
						if ($row['orientativa'])
							$style .= ";background-color:yellow";
					}
					$Nota = NumeroANota($row["Nota".$i]);
					$Deshabilitat = ($ActivaEdicio==1) ? '' : 'disabled';
					
					// <INPUT>
					// name: conté id i convocatòria
					// id: conté les coordenades x, y. Inici a (0, 0).
					$Id = 'grd_'.$j.'_'.$i;
					echo "<TD><input type=text $Deshabilitat style='$style' name=txtNotaId_".$row["NotaId"]."_".$i.
						" id='$Id' value='$Nota' ".
						" onfocus='EnEntrarCellaNota(this);' onBlur='EnSortirCellaNota(this);' onkeydown='NotaKeyDown(this, event);'>".
						"</TD>";
				}
				if ($ActivaEdicio==1) {
					echo "<TD>";
					echo "<A HREF=# onclick='RedueixConvocatoria(".$row["NotaId"].",".$row['convocatoria'].");'><IMG SRC=img/left.svg data-toggle='tooltip' data-placement='top' title='Redueix convocatòria'></A>&nbsp;";
					echo "<A HREF=# onclick='AugmentaConvocatoria(".$row["NotaId"].",".$row['convocatoria'].");'><IMG SRC=img/right.svg data-toggle='tooltip' data-placement='top' title='Augmenta convocatòria'></A>&nbsp;";
					echo "<A HREF=# onclick='ConvocatoriaA0(".$row["NotaId"].");'><IMG SRC=img/check.svg data-toggle='tooltip' data-placement='top' title='Convocatòria a 0 (aprovat)'></A>";
					echo "</TD>";
				}
			}
			else {
				// Columna matriculació
				if ($Convalidat || ($row['convocatoria'] == 0))
					echo "<TD></TD>";
				else
					echo "<TD><input type=checkbox name=chbNotaId_".$row["NotaId"].$sChecked." onclick='MatriculaUF(this);'/></TD>";
				// Columna convalidació
				if ($row['convocatoria'] == 0)
					echo "<TD></TD>";
				else
					echo "<TD><input type=checkbox name=chbConvalidaUFNotaId_".$row["NotaId"].$sCheckedConvalidat." onclick='ConvalidaUF(this, $alumne);'/></TD>";
			}
			echo "</TR>";
			$j++;
			$row = $ResultSet->fetch_assoc();
		}
		echo "</TABLE>";
		echo "<input type=hidden name=TempNota value=''>";
	};	

	if ($accio == 'MostraExpedient') {
		echo "<DIV id=DescarregaExpedientPDF>";
		echo '<a href="ExpedientPDF.php?MatriculaId='.$MatriculaId.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnDescarregaPDF" name="btnDescarregaPDF_'.$alumne.'">Descarrrega PDF</a>';
		if ($Usuari->es_admin) {
			// Edició de l'expedient
			echo '&nbsp';
			if ($ActivaEdicio==1) 
				echo '<a href="MatriculaAlumne.php?accio=MostraExpedient&MatriculaId='.$MatriculaId.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnActivaEdicio">Desactiva edició</a>';
			else
				echo '<a href="MatriculaAlumne.php?accio=MostraExpedient&ActivaEdicio=1&MatriculaId='.$MatriculaId.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnActivaEdicio">Activa edició</a>';
		}
		echo "</DIV>";
	}
	
	$ResultSet->close();
}
else
	echo 'El butlletí de notes no està disponible.';	

echo "<DIV id=debug></DIV>";

$conn->close();

?>




















