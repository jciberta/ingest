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

	// Categoria
	const CATEGORIA = array(
		'D' => 'Document de centre',
		'I' => 'Imprès de funcionament'
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

	/**
	 * Genera el formulari de la recerca.
	 * @param integer $Modalitat Modalitat del formulari.
	 */
	public function EscriuFormulariRecerca($Modalitat = FormRecerca::mfLLISTA) {
		$Professor = new Professor($this->Connexio, $this->Usuari, $this->Sistema);

		$frm = new FormRecerca($this->Connexio, $this->Usuari, $this->Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Impresos de funcionament';
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
		$frm->AfegeixEnllacImatge('enllac', FormRecerca::tiPDF);

		if ($this->Usuari !== null) {
			$aClaus = array_keys(self::VISIBILITAT); array_unshift($aClaus, '');
			$aValors = array_values(self::VISIBILITAT); array_unshift($aValors, 'Tots');
			$frm->Filtre->AfegeixLlista('visibilitat', 'Visibilitat', 20, $aClaus, $aValors);
		}

		$aClaus = array_keys(self::ESTUDI); array_unshift($aClaus, '');
		$aValors = array_values(self::ESTUDI); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('estudi', 'Estudi', 60, $aClaus, $aValors);

		$aClaus = array_keys(self::SUBESTUDI); array_unshift($aClaus, '');
		$aValors = array_values(self::SUBESTUDI); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('subestudi', 'Nivell', 30, $aClaus, $aValors);

		$aClaus = array_keys(self::CATEGORIA); array_unshift($aClaus, '');
		$aValors = array_values(self::CATEGORIA); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('categoria', 'Categoria', 50, $aClaus, $aValors);

		$aClaus = array_keys(self::SOLICITANT); array_unshift($aClaus, '');
		$aValors = array_values(self::SOLICITANT); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('solicitant', 'Sol·licitant', 30, $aClaus, $aValors);

		$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); array_unshift($aClaus, '');
		$aValors = array_values(self::LLIURAMENT_CUSTODIA); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('lliurament', 'Lliurament', 40, $aClaus, $aValors);

		$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); array_unshift($aClaus, '');
		$aValors = array_values(self::LLIURAMENT_CUSTODIA); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('custodia', 'Custòdia', 40, $aClaus, $aValors);
		
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL pel formulari de la recerca de material.
     * @return string Sentència SQL.
	 */
	private function CreaSQL() {
		$SQL = "
			SELECT 
				D.document_id, D.codi, D.nom, D.visibilitat, D.observacions,
				DV.document_versio_id, DV.versio, DV.enllac, ".
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
		";
		if ($this->Usuari === null) {
			$SQL .= " AND visibilitat='B' ";
		}
		$SQL .= " GROUP BY DV.document_id ";

		// En el cas de nou, cal afegir els que no tenen cap registre a DOCUMENT_VERSIO
		if ($this->Usuari !== null) {
			$SQL2 = "
				SELECT
					D.document_id, D.codi, D.nom, D.visibilitat, D.observacions,
					NULL AS document_versio_id, NULL AS versio, NULL AS enllac, ".
					SQL::CreaCase('estudi', self::ESTUDI)." AS estudi, ".
					SQL::CreaCase('subestudi', self::SUBESTUDI)." AS subestudi, ".
					SQL::CreaCase('categoria', self::CATEGORIA)." AS categoria, ".
					SQL::CreaCase('visibilitat', self::VISIBILITAT)." AS visibilitat2, ".
					SQL::CreaCase('solicitant', self::SOLICITANT)." AS solicitant, ".
					SQL::CreaCase('lliurament', self::LLIURAMENT_CUSTODIA)." AS lliurament, ".
					SQL::CreaCase('custodia', self::LLIURAMENT_CUSTODIA)." AS custodia ".
					"
				FROM DOCUMENT D
				WHERE document_id NOT IN (SELECT document_id FROM DOCUMENT_VERSIO)
			";
			$SQL .= " UNION $SQL2";
		}
//echo "<hr>$SQL<hr>";
		return $SQL;
	}	
	
	public function EscriuFormulariFitxa() {
		$frm = new FormFitxaDetall($this->Connexio, $this->Usuari, $this->Sistema);
		$frm->Titol = 'Documents';
		$frm->Taula = 'DOCUMENT';
		$frm->ClauPrimaria = 'document_id';
		$frm->AutoIncrement = true;
		$frm->Id = $this->Id;
		$frm->AfegeixText('document_id', 'Id', 20, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('codi', 'Codi', 50, [FormFitxa::offREQUERIT]);
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
		$aClaus = array_keys(self::CATEGORIA); array_unshift($aClaus, '');
		$aValors = array_values(self::CATEGORIA); array_unshift($aValors, 'Tots');
		$frm->AfegeixLlista('categoria', 'Categoria', 50, $aClaus, $aValors);
		$frm->FinalitzaColumnes();

		$frm->IniciaColumnes();
		$aClaus = array_keys(self::SOLICITANT); //array_unshift($aClaus, '');
		$aValors = array_values(self::SOLICITANT); //array_unshift($aValors, 'Tots');
		$frm->AfegeixLlista('solicitant', 'Sol·licitant', 30, $aClaus, $aValors);
		$frm->SaltaColumna();
		$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); //array_unshift($aClaus, '');
		$aValors = array_values(self::LLIURAMENT_CUSTODIA); //array_unshift($aValors, 'Tots');
		$frm->AfegeixLlista('lliurament', 'Lliurament', 40, $aClaus, $aValors);
		$frm->SaltaColumna();
		$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); //array_unshift($aClaus, '');
		$aValors = array_values(self::LLIURAMENT_CUSTODIA); //array_unshift($aValors, 'Tots');
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
			data_creacio:Creació:date:0:r,
			data_modificacio:Creació:date:0:r,
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
	
		$aProfessors = ObteCodiValorDesDeSQL($this->Connexio, $this->CreaSQLProfessorsQualitat(), "usuari_id", "nom");
		array_unshift($aProfessors[0] , '');
		array_unshift($aProfessors[1] , '');

		$frm->AfegeixLlista('usuari_realitzat', 'Usuari realitzat', 75, $aProfessors[0], $aProfessors[1]);
		$frm->AfegeixData('data_realitzat', 'Data realitzat');
		$frm->AfegeixLlista('usuari_revisat', 'Usuari revisat', 75, $aProfessors[0], $aProfessors[1]);
		$frm->AfegeixData('data_revisat', 'Data revisat');
		$frm->AfegeixLlista('usuari_aprovat', 'Usuari aprovat', 75, $aProfessors[0], $aProfessors[1]);
		$frm->AfegeixData('data_aprovat', 'Data aprovat');
		
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL peper obtenir els professors de qualitat.
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
	
}

 ?>