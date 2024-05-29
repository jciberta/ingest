<?php

/** 
 * LibDocument.php
 *
 * Llibreria d'utilitats per a l'administració dels documents.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibUsuari.php');

/**
 * Classe que encapsula el comportament de la documentació.
 */
class Document extends Objecte
{
	// Visibilitat
	const VISIBILITAT = array(
		'V' => 'Privat',
		'B' => 'Públic'
	);

	// Categoria
	const CATEGORIA = array(
		'D' => 'Document de centre',
		'I' => 'Imprès de funcionament',
		'N' => 'Document intern',
		'Q' => 'Document de qualitat'
	);

	// Estudis
	const ESTUDI = array(
		'GEN' => 'General', 
		'ESO' => 'ESO',
		'BAT' => 'Batxillerat',
		'CF0' => 'Cicle formatiu',
		'CFB' => 'Cicle formatiu de grau bàsic',
		'CFM' => 'Cicle formatiu de grau mig',
		'CFS' => 'Cicle formatiu de grau superior'
	);

	// Subestudis
	const SUBESTUDI = array(
		'FPB' => 'FPB',
		'APD' => 'APD',
		'CAI' => 'CAI',
		'FIP' => 'FIP',
		'SMX' => 'SMX',
		'DAM' => 'DAM',
		'HBD' => 'HBD'
	);

	// Sol·licitant
	const SOLICITANT = array(
		'T' => 'Tutor',
		'A' => 'Alumne'
	);

	// Liurament/Custòdia
	const LLIURAMENT_CUSTODIA = array(
		'TU' => "Tutor",
		'TF' => "Tutor FCT",
		'TD' => "Tutor Dual",
		'SE' => "Secretaria",
		'CE' => "Cap d'estudis",
		'CF' => "Coordinador FP",
		'CD' => "Coordinador Dual"
	);

	//Propietats per guardar els filtres passats per URL
	public $Filtre= '';
	public $Estudi= '';
	public $Nivell= '';
	public $Categoria= '';

	/**
	 * Retorna el document amb el codi que li passem per paràmetre, el document ha de tenir
	 * visibilitat pública.
	 * @param String $CodiDocument.
	 */
	public function RetornaDocument($codiDocument){
		$enllac=null;
		$query = "SELECT enllac FROM DOCUMENT_VERSIO dv
		JOIN DOCUMENT d ON dv.document_id = d.document_id
		WHERE codi = ? AND visibilitat= 'B' AND estat = 'A' order by versio desc limit 1";		

		$stmt = $this->Connexio->prepare($query);
		$stmt->bind_param('s', $codiDocument);
		$stmt->execute();
		$stmt->bind_result($enllac);
		$stmt->fetch();

		if ($enllac !== null) {
			header("Location: " . $enllac);
			exit; 
		} else {    
			echo "El document no s'ha trobat";
		}
		$stmt->close();

	}

	/**
	 * Genera el formulari de la recerca.
	 * @param integer $Modalitat Modalitat del formulari.
	 */
	public function EscriuFormulariRecerca($Modalitat = FormRecerca::mfLLISTA) {
		$Professor = new Professor($this->Connexio, $this->Usuari, $this->Sistema);

		$frm = new FormRecerca($this->Connexio, $this->Usuari, $this->Sistema);
		
		if ($this->Usuari === null||$this->Filtre =='N'|| $this->Estudi !=''|| $this->Categoria !=''|| $this->Nivell !=''){
			$frm->PermetCercar=false;
		}
		
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Documents';
		$frm->SQL = $this->CreaSQL();
		$frm->Taula = 'DOCUMENT';
		$frm->ClauPrimaria = 'document_id';
		$frm->Camps = 'nom, estudi, subestudi, categoria, solicitant, lliurament, custodia';
		$frm->Descripcions = 'Nom, Estudi, Nivell, Categoria, Sol·licitant, Lliurament, Custòdia';
		if ($this->Usuari !== null) {
			if ($this->Usuari->es_admin || $this->Usuari->es_direccio ||$this->Usuari->es_cap_estudis || $Professor->EstaAQualitat()) {
				$frm->Camps = 'codi, nom, visibilitat2, versio, estudi, subestudi, categoria, solicitant, lliurament, custodia';
				$frm->Descripcions = 'Codi, Nom, Visibilitat, Versió, Estudi, Nivell, Categoria, Sol·licitant, Lliurament, Custòdia';
				$frm->PermetEditar = true;
				$frm->URLEdicio = 'Fitxa.php?accio=Document';
				$frm->PermetAfegir = true;
			}
		}
		$frm->AfegeixSuggeriment('observacions');
		$frm->AfegeixEnllacImatge('enllac', FormRecerca::tiPDF, [FormRecerca::ofrNOVA_PAGINA]);

		if ($this->Usuari !== null) {
			$aClaus = array_keys(self::VISIBILITAT); array_unshift($aClaus, '');
			$aValors = array_values(self::VISIBILITAT); array_unshift($aValors, 'Tots');
			$frm->Filtre->AfegeixLlista('visibilitat', 'Visibilitat', 20, $aClaus, $aValors);
		}
		
print_h($this->Categoria);		
		
		if ($this->Estudi ==''|| $this->Categoria ==''|| $this->Nivell ==''){
			$aClaus = array_keys(self::ESTUDI); array_unshift($aClaus, '');
			$aValors = array_values(self::ESTUDI); array_unshift($aValors, 'Tots');
			$frm->Filtre->AfegeixLlista('estudi', 'Estudi', 60, $aClaus, $aValors);

			$aClaus = array_keys(self::SUBESTUDI); array_unshift($aClaus, '');
			$aValors = array_values(self::SUBESTUDI); array_unshift($aValors, 'Tots');
			$frm->Filtre->AfegeixLlista('subestudi', 'Nivell', 30, $aClaus, $aValors);

			
			$aClaus = array_keys(self::CATEGORIA); array_unshift($aClaus, '');
			$aValors = array_values(self::CATEGORIA); array_unshift($aValors, 'Tots');
			$frm->Filtre->AfegeixLlista('categoria', 'Categoria', 50, $aClaus, $aValors);
		}

		if ($this->Usuari !== null) {
			$aClaus = array_keys(self::SOLICITANT); array_unshift($aClaus, '');
			$aValors = array_values(self::SOLICITANT); array_unshift($aValors, 'Tots');
			$frm->Filtre->AfegeixLlista('solicitant', 'Sol·licitant', 30, $aClaus, $aValors);
		}

		if ($this->Usuari !== null) {
			$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); array_unshift($aClaus, '');
			$aValors = array_values(self::LLIURAMENT_CUSTODIA); array_unshift($aValors, 'Tots');
			$frm->Filtre->AfegeixLlista('lliurament', 'Lliurament', 40, $aClaus, $aValors);
		}

		if ($this->Usuari !== null) {
			$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); array_unshift($aClaus, '');
			$aValors = array_values(self::LLIURAMENT_CUSTODIA); array_unshift($aValors, 'Tots');
			$frm->Filtre->AfegeixLlista('custodia', 'Custòdia', 40, $aClaus, $aValors);
		}
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL dels documents.
     * @return string Sentència SQL.
	 */
	private function CreaSQL() {
		if ($this->Usuari !== null) 
			return $this->CreaSQLUsuariAutenticat();
		else
			return $this->CreaSQLUsuariNoAutenticat();
	}	

	/**
	 * Crea la SQL dels documents que pot veure els usuaris autenticats.
     * @return string Sentència SQL.
	 */
	private function CreaSQLUsuariAutenticat() {
		$SQL = "
			SELECT 
				D.document_id, D.codi, D.nom, D.visibilitat, D.observacions, 
				(SELECT MAX(versio) FROM DOCUMENT_VERSIO DV WHERE DV.document_id=D.document_id AND estat='A') AS versio,
			    (SELECT enllac FROM DOCUMENT_VERSIO DV2 WHERE DV2.document_id = D.document_id AND DV2.versio = (SELECT MAX(versio) FROM DOCUMENT_VERSIO DV WHERE DV.document_id = D.document_id AND estat = 'A') LIMIT 1) AS enllac,
				".
				SQL::CreaCase('estudi', self::ESTUDI)." AS estudi, ".
				SQL::CreaCase('subestudi', self::SUBESTUDI)." AS subestudi, ".
				SQL::CreaCase('categoria', self::CATEGORIA)." AS categoria, ".
				SQL::CreaCase('visibilitat', self::VISIBILITAT)." AS visibilitat2, ".
				SQL::CreaCase('solicitant', self::SOLICITANT)." AS solicitant, ".
				SQL::CreaCase('lliurament', self::LLIURAMENT_CUSTODIA)." AS lliurament, ".
				SQL::CreaCase('custodia', self::LLIURAMENT_CUSTODIA)." AS custodia ".
				"
			FROM DOCUMENT D
			WHERE (0=0)
		";
//echo "<hr>$SQL<hr>";
		return $SQL;
	}

	/**
	 * Crea la SQL dels documents que pot veure tothom.
     * @return string Sentència SQL.
	 */
	private function CreaSQLUsuariNoAutenticat() {
		$FiltreEstudi='';
		$FiltreCategoria= '';
		$FiltreNivell='';

		if ($this->Estudi!='' && in_array($this->Estudi, array_keys(self::ESTUDI))){
			$FiltreEstudi= "AND estudi="."'".$this->Estudi."'"."";
		}
		

		if ($this->Nivell!='' && in_array($this->Nivell, array_keys(self::SUBESTUDI))){
			$FiltreNivell= "AND subestudi="."'".$this->Nivell."'"."";
		}
	
		
		if ($this->Categoria!='' && in_array($this->Categoria, array_keys(self::CATEGORIA))){
			$FiltreCategoria= "AND Categoria="."'".$this->Categoria."'"."";
		}

    $SQL = "
			SELECT 
				D.document_id, D.codi, D.nom, D.visibilitat, D.observacions,
				MAX(DV.document_versio_id) AS document_versio_id, DV.versio, DV.enllac, ".
				SQL::CreaCase('estudi', self::ESTUDI)." AS estudi, ".
				SQL::CreaCase('subestudi', self::SUBESTUDI)." AS subestudi, ".
				SQL::CreaCase('categoria', self::CATEGORIA)." AS categoria, ".
				SQL::CreaCase('visibilitat', self::VISIBILITAT)." AS visibilitat2, ".
				SQL::CreaCase('solicitant', self::SOLICITANT)." AS solicitant, ".
				SQL::CreaCase('lliurament', self::LLIURAMENT_CUSTODIA)." AS lliurament, ".
				SQL::CreaCase('custodia', self::LLIURAMENT_CUSTODIA)." AS custodia ".
				"
			FROM DOCUMENT_VERSIO DV
			LEFT JOIN DOCUMENT D ON (D.document_id=DV.document_id)
			WHERE versio=(SELECT MAX(versio) FROM DOCUMENT_VERSIO DV2 WHERE DV.document_id=DV2.document_id AND estat='A')
			AND visibilitat='B'".$FiltreEstudi."".$FiltreNivell."".$FiltreCategoria."
		";
		
		$SQL .= " GROUP BY document_versio_id ";
//echo "<hr>$SQL<hr>";
		return $SQL;
	}

	private function CreaAjudaFormat() {
		$Retorn = 'Categoria:<ul>';
		foreach(self::CATEGORIA as $key => $value)
			$Retorn .= "<li>$key: $value</li>";
		$Retorn .= "</ul>";

		$Retorn .= 'Estudi:<ul>';
		foreach(self::ESTUDI as $key => $value)
			$Retorn .= "<li>$key: $value</li>";
		$Retorn .= "</ul>";

		$Retorn .= 'Nivell:<ul>';
		foreach(self::SUBESTUDI as $key => $value)
			$Retorn .= "<li>$key: $value</li>";
		$Retorn .= "</ul>";
		
		return $Retorn;
	}
	
	public function EscriuFormulariFitxa() {
		$frm = new FormFitxaDetall($this->Connexio, $this->Usuari, $this->Sistema);
		$frm->Titol = 'Documents';
		$frm->Taula = 'DOCUMENT';
		$frm->ClauPrimaria = 'document_id';
		$frm->AutoIncrement = true;
		$frm->Id = $this->Id;

		$frm->SubTitol = 'Format del codi del document: <b>Categoria_Estudi_[Nivell]_Descripció_...</b>&nbsp;'.$frm->CreaAjuda('Format', $this->CreaAjudaFormat());

		$frm->AfegeixText('document_id', 'Id', 20, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('codi', 'Codi', 50, [FormFitxa::offREQUERIT]);
		$aClaus = array_keys(self::CATEGORIA); array_unshift($aClaus, '');
		$aValors = array_values(self::CATEGORIA); array_unshift($aValors, 'Tots');
		$frm->AfegeixLlista('categoria', 'Categoria', 50, $aClaus, $aValors);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);

		$aClaus = array_keys(self::VISIBILITAT);
		$aValors = array_values(self::VISIBILITAT);
		$frm->AfegeixLlista('visibilitat', 'Visibilitat', 20, $aClaus, $aValors);

		$frm->AfegeixEspai();

		$frm->IniciaColumnes();
		$aClaus = array_keys(self::ESTUDI); //array_unshift($aClaus, '');
		$aValors = array_values(self::ESTUDI); //array_unshift($aValors, 'Tots');
		$frm->AfegeixLlista('estudi', 'Estudi', 60, $aClaus, $aValors);
		$frm->SaltaColumna();
		$aClaus = array_keys(self::SUBESTUDI); array_unshift($aClaus, '');
		$aValors = array_values(self::SUBESTUDI); array_unshift($aValors, 'Tots');
		$frm->AfegeixLlista('subestudi', 'Nivell', 30, $aClaus, $aValors);
		$frm->FinalitzaColumnes();

		$frm->IniciaColumnes();
		$aClaus = array_keys(self::SOLICITANT); array_unshift($aClaus, '');
		$aValors = array_values(self::SOLICITANT); array_unshift($aValors, '');
		$frm->AfegeixLlista('solicitant', 'Sol·licitant', 30, $aClaus, $aValors);
		$frm->SaltaColumna();
		$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); array_unshift($aClaus, '');
		$aValors = array_values(self::LLIURAMENT_CUSTODIA); array_unshift($aValors, '');
		$frm->AfegeixLlista('lliurament', 'Lliurament', 40, $aClaus, $aValors);
		$frm->SaltaColumna();
		$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); array_unshift($aClaus, '');
		$aValors = array_values(self::LLIURAMENT_CUSTODIA); array_unshift($aValors, '');
		$frm->AfegeixLlista('custodia', 'Custòdia', 40, $aClaus, $aValors);
		$frm->FinalitzaColumnes();

		$frm->AfegeixEspai();

		$frm->Pestanya("Observacions");
		$frm->AfegeixTextArea('observacions', 'Observacions', 25, 5);
		
		$frm->DetallsEnPestanyes = true;
		$frm->Pestanya("Històric", true);
//		$frm->AfegeixAmagat('data_inici', MySQLAData($this->AnyAcademic->data_inici));

		$frm->AfegeixDetall('Històric', 'DOCUMENT_VERSIO', 'document_versio_id', 'document_id', '
			enllac:Enllaç:text:400:r, 
			versio:Versió:int:60:r,
			descripcio_modificacio:Modificació:text:400:r, 
			estat:Estat:llista[["E";"R";"V";"A"]|["Elaboració";"Realitzat";"Revisió";"Aprovat"]]:150:r,
			data_creacio:Creat:date:0:r,
			data_modificacio:Modificat:date:0:r,
			data_realitzat:Realitzat:date:0:r,
			data_revisat:Revisat:date:0:r,
			data_aprovat:Aprovat:date:0:r
		', 'Fitxa.php?accio=DocumentVersio', true);
/*
		$frm->AfegeixDetall('Històric', 'DOCUMENT_VERSIO', 'document_id', 'document_id', '
			enllac:Enllaç:text:400:r, 
			versio:Versió:int:60:r,
			descripcio_modificacio:Modificació:text:400:r, 
			estat:Estat:text:40:r,
			data_creacio:Creació:date:0:r,
			data_modificacio:Creació:date:0:r,
			data_realitzat:Realitzat:date:0:r,
			data_revisat:Revisat:date:0:r,
			data_aprovat:Aprovat:date:0:r
		');
		
		$frm->AfegeixDetallJSON('Històric', 'DOCUMENT_VERSIO', 'document_id', 'document_id', '
			{
				enllac: {
					camp: "enllac",
					titol: "Enllaç",
					tipus: "text",
					longitud: 400,
					atribut: "r"
				},
				versio: {
					camp: "versio",
					titol: "Versió",
					tipus: "int",
					longitud: 60,
					atribut: "r"
				},
				descripcio_modificacio: {
					camp: "descripcio_modificacio",
					titol: "Modificació",
					tipus: "text",
					longitud: 400,
					atribut: "r"
				},
				estat: {
					camp: "estat",
					titol: "Estat",
					tipus: "llista",
					codi: ["E", "R", "V", "A"],
					valor: ["Elaboració", "Realitzat", "Revisió", "Aprovat"],
					longitud: 100,
					atribut: "r"
				}
				
			}
		');
*/	

	
/*		
    versio INT NOT NULL DEFAULT 0,
    descripcio_modificacio VARCHAR(255) NOT NULL,
    enllac VARCHAR(255) NOT NULL,
    estat char(1) NOT NULL DEFAULT 'E' CHECK (estat IN ('E', 'R', 'V', 'A')),  Elaboració, Realitzat, reVisió, Aprovat 
    data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_modificacio DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    usuari_realitzat INT,
    data_realitzat DATE,
    usuari_revisat INT,
    data_revisat DATE,
    usuari_aprovat INT,
    data_aprovat DATE,		
		*/

		// Pedaç. Hauria de ser un botó especial, propietat PermetAfegir
//		$frm->AfegeixBotoJSDetall('Nova versió', 'NovaVersioDocument', '', "window.location.href = 'Fitxa.php?accio=DocumentVersio&ClauForana="..";");

		$frm->EscriuHTML();
	}
}

/**
 * Classe que encapsula el comportament de les versions de la documentació.
 */
class DocumentVersio extends Objecte
{
	// Estat
	const ESTAT = array(
		'E' => 'Elaboració', 
		'R' => 'Realitzat',
		'V' => 'Revisió',
		'A' => 'Aprovat'
	);
	
	/**
	 * Nom de la clau forana (per afegir un nou registre).
	 * @var string
	 */    
    public $ClauForanaNom = ''; 

	/**
	 * Valor de la clau forana.
	 * @var int
	 */    
    public $ClauForanaValor = -1; 

	public function EscriuFormulariFitxa() {
		$frm = new FormFitxa($this->Connexio, $this->Usuari, $this->Sistema);
		$frm->Titol = 'Versió de document';
		$frm->Taula = 'DOCUMENT_VERSIO';
		$frm->ClauPrimaria = 'document_versio_id';
		$frm->AutoIncrement = true;
		$frm->Id = $this->Id;
		$frm->ClauForanaNom = $this->ClauForanaNom;
		$frm->ClauForanaValor = $this->ClauForanaValor;
		$frm->AfegeixText('document_versio_id', 'Id', 20, [FormFitxa::offNOMES_LECTURA]);
//		$frm->AfegeixText('document_id', 'Clau forana', 20, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixEnter('versio', 'Versió', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('enllac', 'Enllaç', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('descripcio_modificacio', 'Descripció modificació', 200, [FormFitxa::offREQUERIT]);
		
		$aClaus = array_keys(self::ESTAT);
		$aValors = array_values(self::ESTAT);
		$frm->AfegeixLlista('estat', 'Estat', 60, $aClaus, $aValors);
		
		$frm->AfegeixData('data_creacio', 'Data creació', [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixData('data_modificacio', 'Data modificació', [FormFitxa::offNOMES_LECTURA]);

		$aDocumentadors = ObteCodiValorDesDeSQL($this->Connexio, $this->CreaSQLDocumentadorsQualitat(), "usuari_id", "nom");
		array_unshift($aDocumentadors[0] , '');
		array_unshift($aDocumentadors[1] , '');
	
		$aProfessors = ObteCodiValorDesDeSQL($this->Connexio, $this->CreaSQLProfessorsQualitat(), "usuari_id", "nom");
		array_unshift($aProfessors[0] , '');
		array_unshift($aProfessors[1] , '');

		$frm->AfegeixLlista('usuari_realitzat', 'Usuari realitzat', 75, $aDocumentadors[0], $aDocumentadors[1]);
		$frm->AfegeixData('data_realitzat', 'Data realitzat');
		$frm->AfegeixLlista('usuari_revisat', 'Usuari revisat', 75, $aProfessors[0], $aProfessors[1]);
		$frm->AfegeixData('data_revisat', 'Data revisat');
		$frm->AfegeixLlista('usuari_aprovat', 'Usuari aprovat', 75, $aProfessors[0], $aProfessors[1]);
		$frm->AfegeixData('data_aprovat', 'Data aprovat');
		
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL per obtenir els professors de qualitat.
     * @return string Sentència SQL.
	 */
	private function CreaSQLProfessorsQualitat() {
		$SQL = "
			SELECT usuari_id, FormataCognom1Cognom2Nom(U.nom, U.cognom1, U.cognom2) AS nom
			FROM PROFESSOR_EQUIP PE
			LEFT JOIN EQUIP E ON (E.equip_id=PE.equip_id)
			LEFT JOIN USUARI U ON (U.usuari_id=PE.professor_id)
			WHERE E.tipus='CQ' AND E.any_academic_id=".$this->Sistema->any_academic_id;		
		return $SQL;
	}	

	/**
	 * Crea la SQL per obtenir els professors de qualitat.
     * @return string Sentència SQL.
	 */
	private function CreaSQLDocumentadorsQualitat() {
		$SQL = "
			SELECT usuari_id, FormataCognom1Cognom2Nom(U.nom, U.cognom1, U.cognom2) AS nom
			FROM USUARI U
			WHERE U.es_professor=1
			ORDER BY U.cognom1, U.cognom2, U.nom
		";
		return $SQL;
	}	
}

 ?>