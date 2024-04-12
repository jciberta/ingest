<?php

/** 
 * LibGeneraPDF.php
 *
 * Llibreria d'utilitats per a la generació de PDF (massius).
 *
 * @author Josep Ciberta
 * @author Josep Maria Vegas
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibClasses.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibAvaluacio.php');
require_once(ROOT.'/lib/LibExpedient.php');
require_once(ROOT.'/lib/LibProgramacioDidactica.php');

/**
 * Factoria que genera l'objecte per a la generació de PDF depenent de l'acció.
 */
class GeneraPDFFactoria
{
	public static function Crea($conn, $user, $system, $Accio, $Id) {
		switch($Accio) {
			case "Expedient":
				$obj = new GeneraPDFExpedient($conn, $user, $system);
				break;
			case "Programacio":
				$obj = new GeneraPDFProgramacio($conn, $user, $system);
				break;
			default:
				throw new Exception("GeneraPDFFactoria: acció no implementada");
				break;
		}
		$obj->Id = $Id;
		return $obj;		
	}
}

/**
 * Classe que encapsula l'objecte per a la generació de PDF.
 */
abstract class GeneraPDF extends Objecte
{
	// Accions
	const aEXPEDIENT = 1;
	const aPROGRAMACIO = 2;

	/**
	 * Retorna l'ordre per executar el PHP des de la línia d'ordres depenent del sistema operatiu.
     * @return string Ordre.
	 */
	protected function ComandaPHP(): string {
		$Retorn = '';
		if ($this->SistemaOperatiu === Objecte::soWINDOWS)
			$Retorn = UNITAT_XAMPP.':\xampp\php\php.exe';
		else if ($this->SistemaOperatiu === Objecte::soLINUX)
			$Retorn = 'php';
		return $Retorn;
	}

	/**
	 * Prepara l'entorn per a la generació de la documentació.
	 */
    protected function PreparaEntorn() {
        echo "Preparant directori per als documents... ";
        if (Config::Debug) {
            echo "<PRE>";
            echo "  Esborrant directori per als documents<BR>";
        }
        EsborraDirectori(INGEST_DATA."/pdf");
        if (Config::Debug)
            echo "  Creant directori per als documents<BR>";
        mkdir(INGEST_DATA."/pdf");
        if (Config::Debug)
            echo "</PRE>";
        echo "Ok.<BR>";
    }

	/**
	 * Crea el fitxer comprimit amb els documents i presenta per pantalla l'enllaç on és.
	 * @param string $Nom Nom del fitxer ZIP.
	 */
    protected function CreaFitxerComprimit(string $Nom) {
 
    //Netejem el directori
    array_map('unlink', glob("scripts/*"));

    // Comprimeix expedients en un arxiu ZIP
    echo "Comprimint els documents... ";
    $tempDir = sys_get_temp_dir() . '/expedients' . DIRECTORY_SEPARATOR;
    $zipFile ="scripts/".$Nom.".zip";

    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = glob($tempDir . '/*');
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    } else {
        die('Error en crear l\'arxiu ZIP');
    }
   
        //Eliminar directori temporal
        array_map('unlink', glob("$tempDir/*"));
        rmdir($tempDir);
    
        echo " Ok.<BR>";
        echo "Podeu descarregar els documents comprimits <a href='scripts/$Nom.zip'>aquí</a>. Mida: "
        .FormataBytes(filesize($zipFile));       
    }
}

/**
 * Classe que encapsula l'objecte per a la generació de PDF de l'expedient.
 */
class GeneraPDFExpedient extends GeneraPDF
{
    /**
	 * Genera el contingut HTML i el presenta a la sortida.
	 */

	public function EscriuHTML() {
        if ($this->Id == -1)
            header("Location: Surt.php");
        CreaIniciHTML($this->Usuari, "Generació d'expedients en PDF");

        echo "<h2>Avaluació actual</h2>";
        $Avaluacio = new Avaluacio($this->Connexio, $this->Usuari, $this->Sistema);
        $Avaluacio->Carrega($this->Id);
        $Avaluacio->EscriuTaula();
        $Expedient = new Expedient($this->Connexio, $this->Usuari, $this->Sistema);
        $Sufix = $Avaluacio->EstatText();

        echo "<HR>";
        $this->PreparaEntorn();

        echo "Generant l'script per als documents... ";
        $Text = $Expedient->GeneraScript($this->Id, $Sufix);
        echo "Ok.<BR>";

        echo "Executant l'script per als documents...";
        
        if (Config::Debug)
            echo "<PRE>";
        for ($i=0; $i<count($Text['matricula'])-1; $i++) {
            if (Config::Debug){
                echo "  Matricula:  ".$Text['matricula'][$i]." ";
                // Directori temporal per emmagatzemar els expedients en PDF
                $pdfPath = $Expedient->GeneraPDFArxiu($Text['matricula'][$i],$Text['arxiu'][$i]);
                echo "  Ubicació: $pdfPath<BR>";
        }
    }
        if (Config::Debug)
            echo "</PRE>";
        echo " Ok.<BR>";

        $Nom = $Avaluacio->NomFitxer();
        $this->CreaFitxerComprimit($Nom);
    }
}

/**
 * Classe que encapsula l'objecte per a la generació de PDF de la programació.
 */
class GeneraPDFProgramacio extends GeneraPDF
{
    /**
	 * Nom del fitxer ZIP.
	 * @var string
	 */
	private $NomZIP = '';

    /**
	 * Genera el contingut HTML i el presenta a la sortida.
	 */
	public function EscriuHTML() {
        if ($this->Id == -1)
            header("Location: Surt.php");
        CreaIniciHTML($this->Usuari, "Generació de programacions en PDF");

        echo "<h2>Pla d'estudis</h2>";
        $SQL = '
            SELECT AA.any_inici, AA.any_final, CPE.codi AS CodiCF, CPE.nom AS NomCF
            FROM CICLE_PLA_ESTUDI CPE
            LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id)
            WHERE CPE.cicle_pla_estudi_id='.$this->Id;
        $Registre = DB::CarregaRegistreSQL($this->Connexio, $SQL);
        echo "Any acadèmic: <B>".$Registre->any_inici.'-'.$Registre->any_final."</B><br>";
        echo "Curs: <B>".$Registre->NomCF."</B><br>";
        echo "Codi: <B>".$Registre->CodiCF."</B><br>";

        echo "<HR>";
        $this->PreparaEntorn();
        
        echo "Generant l'script per als documents... ";
        $Text = $this->GeneraScript();
        echo "Ok.<BR>";

        echo "Executant l'script per als documents...";
        $aText = explode("\r\n",$Text[0]);
        if (Config::Debug)
            echo "<PRE>";
        for ($i=0; $i<count($aText)-1; $i++) {
            if (Config::Debug)
                echo "  Executant $aText[$i]<BR>";
                $Result = shell_exec($aText[$i]);
        }
        if (Config::Debug)
            echo "</PRE>";
        echo " Ok.<BR>";
        
        $this->CreaFitxerComprimit($this->NomZIP);
    }

	/**
	 * Genera l'script per a poder generar tots els expedients en PDF d'un curs.
	 */
	public function GeneraScript(): string {
		$Comanda = $this->ComandaPHP();
		$Retorn = '';
		$SQL = '
            SELECT AA.any_inici, AA.any_final, CPE.codi AS CodiCF, MPE.modul_pla_estudi_id, MPE.codi AS CodiMP
            FROM MODUL_PLA_ESTUDI MPE
            LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
            LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id)
            WHERE MPE.cicle_pla_estudi_id=?
        ';        
        $stmt = $this->Connexio->prepare($SQL);
        $stmt->bind_param("i", $this->Id);
        $stmt->execute();
        $ResultSet = $stmt->get_result();
		if ($ResultSet->num_rows > 0) {
			while ($row = $ResultSet->fetch_object()) {
                $this->NomZIP = 'Programacio_didactica_'.substr($row->any_inici, -2).''.substr($row->any_final, -2).'_'.$row->CodiCF;
                $Nom = $this->NomZIP.'_'.$row->CodiMP;
				$Retorn .= "$Comanda ".ROOT."/ProgramacioDidacticaPDF.php ".$row->modul_pla_estudi_id." >".INGEST_DATA."/pdf/".$Nom.".pdf\r\n";
			}
		}
		$ResultSet->close();
		return $Retorn;
	}

}

?>