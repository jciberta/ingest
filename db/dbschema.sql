CREATE DATABASE InGest DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

USE InGest;

CREATE TABLE FAMILIA_FP
(
    /* FFP */
    familia_fp_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,

    CONSTRAINT FamiliaFPPK PRIMARY KEY (familia_fp_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE CICLE_FORMATIU
(
    /* CF */
    cicle_formatiu_id INT NOT NULL,
    familia_fp_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    grau CHAR(2) NOT NULL,
    codi CHAR(3) NOT NULL,
    codi_xtec CHAR(4) NOT NULL,
    actiu BIT NOT NULL DEFAULT 1,
    llei CHAR(2) NOT NULL DEFAULT 'LO', /* LoGse, LOe */

    CONSTRAINT CicleFormatiuPK PRIMARY KEY (cicle_formatiu_id),
    CONSTRAINT CF_FamiliaFPFK FOREIGN KEY (familia_fp_id) REFERENCES FAMILIA_FP(familia_fp_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE MODUL_PROFESSIONAL
(
    /* MP */
    modul_professional_id INT NOT NULL,
    cicle_formatiu_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    codi VARCHAR(5) NOT NULL,
    hores INT NOT NULL,
    hores_setmana INT,
    especialitat VARCHAR(20),
    cos CHAR(1),
    es_fct BIT,
    actiu BIT NOT NULL DEFAULT 1,

    CONSTRAINT ModulProfessionalPK PRIMARY KEY (modul_professional_id),
    CONSTRAINT MP_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE UNITAT_FORMATIVA
(
    /* UF */
    unitat_formativa_id INT NOT NULL,
    modul_professional_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    codi VARCHAR(5) NOT NULL,
    hores INT NOT NULL,
    nivell INT CHECK (nivell IN (1, 2)),
	data_inici DATE,
	data_final DATE,
    orientativa BIT,
    es_fct BIT,
    activa BIT NOT NULL DEFAULT 1,

    CONSTRAINT UnitatFormativaPK PRIMARY KEY (unitat_formativa_id),
    CONSTRAINT UF_ModulProfessionalFK FOREIGN KEY (modul_professional_id) REFERENCES MODUL_PROFESSIONAL(modul_professional_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE USUARI
(
    /* U */
    usuari_id          INT NOT NULL,
    username           VARCHAR(100) NOT NULL,
    password           VARCHAR(255) NOT NULL DEFAULT '*',
    nom          	   VARCHAR(100),
    cognom1            VARCHAR(100), 
    cognom2            VARCHAR(100),
    nom_complet        VARCHAR(255), 
	codi               VARCHAR(20), /* Codi professor, IDALU per alumne */
	sexe               CHAR(1), /* Home, Dona, Neutre */
	tipus_document     CHAR(1), /* Dni, Nie, Passaport */
	document           VARCHAR(15),
    email              VARCHAR(100), 
    email_ins          VARCHAR(100), /* @inspalamos.cat */
    telefon            VARCHAR(255),  
    adreca             VARCHAR(255), 
    codi_postal		   VARCHAR(10), 
    poblacio           VARCHAR(120),
    municipi           VARCHAR(120),
    provincia          VARCHAR(25),
    data_naixement     DATE,
    municipi_naixement VARCHAR(100),
	nacionalitat       VARCHAR(20),
    es_admin           BIT NOT NULL DEFAULT 0,
    es_direccio        BIT NOT NULL DEFAULT 0,
    es_cap_estudis     BIT NOT NULL DEFAULT 0,
    es_administratiu   BIT NOT NULL DEFAULT 0,
    es_cap_departament BIT NOT NULL DEFAULT 0,
    es_tutor           BIT NOT NULL DEFAULT 0,
    es_professor       BIT NOT NULL DEFAULT 0,
    es_alumne          BIT NOT NULL DEFAULT 0,
    es_pare            BIT NOT NULL DEFAULT 0,
    permet_tutor       BIT NOT NULL DEFAULT 0,
	inscripcio_borsa_treball BIT NOT NULL DEFAULT 1,
    titol_angles       VARCHAR(5),
    perfil_aicle       BIT DEFAULT 0,
    imposa_canvi_password BIT,
    usuari_bloquejat BIT,
    pare_id INT,
    mare_id INT,
    data_creacio       DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_modificacio   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data_ultim_login DATETIME,
    ip_ultim_login VARCHAR(15),
 
    CONSTRAINT UsuariPK PRIMARY KEY (usuari_id),
    CONSTRAINT U_PareFK FOREIGN KEY (pare_id) REFERENCES USUARI(usuari_id),
    CONSTRAINT U_MareFK FOREIGN KEY (mare_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE UNIQUE INDEX U_NifIX ON USUARI (document);

CREATE TABLE ANY_ACADEMIC
(
    /* AA */
    any_academic_id INT NOT NULL AUTO_INCREMENT,
    any_inici INT NOT NULL,
    any_final INT NOT NULL,
	data_inici DATE,
	data_final DATE,
    nom VARCHAR(20),
    actual BIT, /* Indica l'any acadèmic actual. Només n'hi pot haver 1 */

    CONSTRAINT AnyAcademicPK PRIMARY KEY (any_academic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE SISTEMA
(
    /* S */
	/* Ha de contenir un únic registre que conté la configuració */
    sistema_id INT NOT NULL AUTO_INCREMENT,
	nom VARCHAR(100), /* Nom institut */ 
    aplicacio VARCHAR(10) DEFAULT 'InGest',
	any_academic_id INT NOT NULL,
    director_id INT NOT NULL,
    gestor_borsa_treball_id INT,
    versio_db VARCHAR(5),
    google_client_id VARCHAR(100),
    google_client_secret VARCHAR(100),
    moodle_url VARCHAR(100),
    moodle_ws_token VARCHAR(100),
    ipdata_api_key VARCHAR(100),
    clickedu_api_key VARCHAR(100),
    clickedu_id int,
    clickedu_secret VARCHAR(100),
    capcalera_login VARCHAR(1000),
    peu_login VARCHAR(1000),

    CONSTRAINT SistemaPK PRIMARY KEY (sistema_id),
    CONSTRAINT S_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id),
    CONSTRAINT S_DirectorFK FOREIGN KEY (director_id) REFERENCES USUARI(usuari_id),		
    CONSTRAINT S_GestorBorsaTreballFK FOREIGN KEY (gestor_borsa_treball_id) REFERENCES USUARI(usuari_id)		
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE CICLE_PLA_ESTUDI
(
    /* CPE */
    cicle_pla_estudi_id INT NOT NULL AUTO_INCREMENT,
    cicle_formatiu_id INT NOT NULL,
    any_academic_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    grau CHAR(2) NOT NULL,
    codi CHAR(3) NOT NULL,
    codi_xtec CHAR(4) NOT NULL,

    CONSTRAINT CiclePlaEstudiPK PRIMARY KEY (cicle_pla_estudi_id),
    CONSTRAINT CPE_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id),
    CONSTRAINT CPE_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE MODUL_PLA_ESTUDI
(
    /* MPE */
    modul_pla_estudi_id INT NOT NULL AUTO_INCREMENT,
    modul_professional_id INT NOT NULL,
    cicle_pla_estudi_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    codi VARCHAR(5) NOT NULL,
    hores INT NOT NULL,
    hores_setmana INT,
    especialitat VARCHAR(20),
    cos CHAR(1),
    es_fct BIT,
	estat CHAR(1) NOT NULL DEFAULT 'E', /* Elaboració, Departament, Acceptada */
	metodologia TEXT,
	criteris_avaluacio TEXT,
	recursos TEXT,
	planificacio TEXT,
	unitats_didactiques TEXT,
	seguiment TEXT,

    CONSTRAINT ModulPlaEstudiPK PRIMARY KEY (modul_pla_estudi_id),
    CONSTRAINT MPE_ModulProfessionalFK FOREIGN KEY (modul_professional_id) REFERENCES MODUL_PROFESSIONAL(modul_professional_id),
    CONSTRAINT MPE_CiclePlaEstudiFK FOREIGN KEY (cicle_pla_estudi_id) REFERENCES CICLE_PLA_ESTUDI(cicle_pla_estudi_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE UNITAT_PLA_ESTUDI
(
    /* UPE */
    unitat_pla_estudi_id INT NOT NULL AUTO_INCREMENT,
    unitat_formativa_id INT NOT NULL,
    modul_pla_estudi_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    codi VARCHAR(5) NOT NULL,
    hores INT NOT NULL,
    nivell INT CHECK (nivell IN (1, 2)),
    data_inici DATE,
    data_final DATE,
    orientativa BIT,
    es_fct BIT,
    lms CHAR(1) NOT NULL DEFAULT 'M', /* Moodle, Classroom */
	metode_importacio_notes CHAR(1) NOT NULL DEFAULT 'F', /* Fitxer, servei Web */
    nota_maxima INT NOT NULL DEFAULT 100, /* Nota sobre 100 */
    nota_inferior_5 CHAR(1) NOT NULL DEFAULT 'T', /* Trunca, Arrodoneix */
    nota_superior_5 CHAR(1) NOT NULL DEFAULT 'A', /* Trunca, Arrodoneix */
    categoria_moodle_importacio_notes VARCHAR(50),
    es_uf_addicional BIT(1) DEFAULT 0,

    CONSTRAINT UnitatPlaEstudiPK PRIMARY KEY (unitat_pla_estudi_id),
    CONSTRAINT UPE_UnitatFormativaFK FOREIGN KEY (unitat_formativa_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id),
    CONSTRAINT UPE_ModulPlaEstudiFK FOREIGN KEY (modul_pla_estudi_id) REFERENCES MODUL_PLA_ESTUDI(modul_pla_estudi_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE CURS
(
    /* C */
    curs_id INT NOT NULL AUTO_INCREMENT,
    any_academic_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    codi VARCHAR(10) NOT NULL,
    cicle_formatiu_id INT NOT NULL,
    nivell INT CHECK (nivell IN (1, 2)),
    grups_classe VARCHAR(100),
    grups_tutoria VARCHAR(100),
    avaluacio CHAR(3) NOT NULL DEFAULT 'ORD', /* ORD, EXT */
    trimestre INT NOT NULL DEFAULT 1, /* 1, 2, 3 */
    estat char(1) NOT NULL DEFAULT 'A', /* Actiu, Junta, Inactiu, Obertura, Tancat */
    data_inici DATE,
    data_final DATE,    
    data_tancament DATETIME,

    CONSTRAINT CursPK PRIMARY KEY (curs_id),
    CONSTRAINT C_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id),
    CONSTRAINT C_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_PLA_ESTUDI(cicle_pla_estudi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE MATRICULA
(
    /* MAT */
    matricula_id INT NOT NULL AUTO_INCREMENT,
    curs_id INT NOT NULL, 
    alumne_id INT NOT NULL,
    grup CHAR(1) CHECK (grup IN ('A', 'B', 'C')),
    grup_tutoria VARCHAR(2),
    baixa BIT,
    comentari_trimestre1 VARCHAR(100),
    comentari_trimestre2 VARCHAR(100),
    comentari_trimestre3 VARCHAR(100),
    comentari_ordinaria VARCHAR(100),
    comentari_extraordinaria VARCHAR(100),
    comentari_matricula_seguent VARCHAR(100),

    CONSTRAINT MatriculaPK PRIMARY KEY (matricula_id),
    CONSTRAINT MAT_CursFK FOREIGN KEY (curs_id) REFERENCES CURS(curs_id),
    CONSTRAINT MAT_UsuariFK FOREIGN KEY (alumne_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE NOTES
(
    /* N */
    /* Notes: NP: -1, A: 100, NA: -100 */
    notes_id INT NOT NULL AUTO_INCREMENT,
    matricula_id INT NOT NULL,
    uf_id INT NOT NULL,
    nota1 INT CHECK (nota1 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),
    nota2 INT CHECK (nota2 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),
    nota3 INT CHECK (nota3 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),
    nota4 INT CHECK (nota4 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),
    nota5 INT CHECK (nota5 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)), /* Gràcia */
	nota_t1 INT, /* 1r trimestre */
	nota_t2 INT, /* 2n trimestre */
	nota_t3 INT, /* 3r trimestre */
    exempt BIT,
    convalidat BIT,
    junta BIT,
    baixa BIT, /* Baixa d'una UF */
    convocatoria INT, /* 0 (aprovat), 1, 2, 3, 4, 5 */

    CONSTRAINT NotesPK PRIMARY KEY (notes_id),
    CONSTRAINT N_MatriculaFK FOREIGN KEY (matricula_id) REFERENCES MATRICULA(matricula_id),
    CONSTRAINT N_UnitatFormativaFK FOREIGN KEY (uf_id) REFERENCES UNITAT_PLA_ESTUDI(unitat_pla_estudi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE PROFESSOR_UF
(
    /* PUF */
    professor_uf_id INT NOT NULL AUTO_INCREMENT,
    professor_id    INT NOT NULL,
    uf_id           INT NOT NULL,
    grups           VARCHAR(5),

    CONSTRAINT ProfessorUFPK PRIMARY KEY (professor_uf_id),
    CONSTRAINT PUF_UsuariFK FOREIGN KEY (professor_id) REFERENCES USUARI(usuari_id),
    CONSTRAINT PUF_UnitatFormativaFK FOREIGN KEY (uf_id) REFERENCES UNITAT_PLA_ESTUDI(unitat_pla_estudi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE EQUIP
(
    /* EQP */
    equip_id INT NOT NULL AUTO_INCREMENT,
	any_academic_id INT NOT NULL,
    tipus CHAR(2), /* DP: Departament, EQ: Equip docent, EX: Equip documentació, CO: Comissió, CQ: Comissió Qualitat, CM: Comissió Mobilitat */
    nom VARCHAR(50) NOT NULL,
    cap INT NULL,
	es_permanent BIT NOT NULL DEFAULT 0,

    CONSTRAINT EquipPK PRIMARY KEY (equip_id),
    CONSTRAINT EQP_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id),
    CONSTRAINT EQP_CapFK FOREIGN KEY (cap) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE PROFESSOR_EQUIP
(
    /* PEQ */
    professor_equip_id INT NOT NULL AUTO_INCREMENT,
    professor_id INT NOT NULL,
    equip_id INT NOT NULL,

    CONSTRAINT ProfessorEquipPK PRIMARY KEY (professor_equip_id),
    CONSTRAINT PEQ_UsuariFK FOREIGN KEY (professor_id) REFERENCES USUARI(usuari_id),
    CONSTRAINT PEQ_DepartamentFK FOREIGN KEY (equip_id) REFERENCES EQUIP(equip_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE FESTIU
(
    /* F */
	festiu_id INT NOT NULL AUTO_INCREMENT,
    data DATE NOT NULL,
    motiu VARCHAR(50) NOT NULL,

    CONSTRAINT FestiuPK PRIMARY KEY (festiu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE UNIQUE INDEX F_DataIX ON FESTIU (data);

CREATE TABLE DIA_GUARDIA
(
    /* DG */
    dia INT NOT NULL, /* 1, 2, 3, 4, 5 */
    nom VARCHAR(10) NOT NULL, /* Dilluns, ... divendres */
    codi CHAR(2) NOT NULL, /* Dl, dm, dc, dj, dv, ds, dg */
    punter_data DATE,

    CONSTRAINT DiaGuardiaPK PRIMARY KEY (dia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE BLOC_GUARDIA
(
    /* BG */
    dia INT NOT NULL, 
    hora INT NOT NULL, 
    hora_inici TIME NOT NULL,
    hora_final TIME NOT NULL,
	professor_lavabo_id INT NULL,

    CONSTRAINT BlocGuardiaPK PRIMARY KEY (dia, hora),
    CONSTRAINT BG_DiaGuardiaFK FOREIGN KEY (dia) REFERENCES DIA_GUARDIA(dia),
    CONSTRAINT BG_ProfessorLavaboFK FOREIGN KEY (professor_lavabo_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE PROFESSOR_GUARDIA
(
    /* PG */
    professor_guardia_id INT NOT NULL AUTO_INCREMENT,
    dia INT NOT NULL, 
    hora INT NOT NULL,
    professor_id INT NOT NULL,
    guardies INT NOT NULL DEFAULT 0,
    ordre INT NOT NULL,

    CONSTRAINT ProfessorGuardiaPK PRIMARY KEY (professor_guardia_id),
    CONSTRAINT PG_BlocGuardiaFK FOREIGN KEY (dia, hora) REFERENCES BLOC_GUARDIA(dia, hora),
    CONSTRAINT PG_UsuariFK FOREIGN KEY (professor_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE REGISTRE
(
    /* R */
	registre_id INT NOT NULL AUTO_INCREMENT,
    usuari_id INT NOT NULL,
    nom_usuari VARCHAR(100) NOT NULL, 
    data DATETIME NOT NULL,
    ip VARCHAR(15) NOT NULL,
    seccio VARCHAR(20) NOT NULL, 
    missatge VARCHAR(255) NOT NULL,
	
    CONSTRAINT RegistrePK PRIMARY KEY (registre_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE TUTOR
(
    /* TUT */
    tutor_id INT NOT NULL AUTO_INCREMENT,
    curs_id INT NOT NULL, 
    professor_id INT NOT NULL,
    grup_tutoria VARCHAR(2),

    CONSTRAINT TutorPK PRIMARY KEY (tutor_id),
    CONSTRAINT TUT_CursFK FOREIGN KEY (curs_id) REFERENCES CURS(curs_id),
    CONSTRAINT TUT_ProfessorFK FOREIGN KEY (professor_id) REFERENCES USUARI(usuari_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE NOTES_MP
(
    /* NMP */
    notes_mp_id INT NOT NULL AUTO_INCREMENT,
    matricula_id INT NOT NULL, 
    modul_professional_id INT NOT NULL,
    nota INT CHECK (nota IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),

    CONSTRAINT NotesMPPK PRIMARY KEY (notes_mp_id),
    CONSTRAINT NMP_MatriculaFK FOREIGN KEY (matricula_id) REFERENCES MATRICULA(matricula_id),
	CONSTRAINT NMP_ModulProfessionalFK FOREIGN KEY (modul_professional_id) REFERENCES MODUL_PLA_ESTUDI(modul_pla_estudi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE PROJECTE
(
    /* PRJ */
    projecte_id INT NOT NULL AUTO_INCREMENT,
    codi VARCHAR(50) NOT NULL,
    nom VARCHAR(200) NOT NULL,
    tipus CHAR(2) NOT NULL, /* E+ */
    descripcio TEXT,
    periode VARCHAR(10) NOT NULL,
    data_inici DATE,
    data_final DATE,
    coordinador_id INT NOT NULL,

    CONSTRAINT ProjectePK PRIMARY KEY (projecte_id),
    CONSTRAINT PRJ_UsuariFK FOREIGN KEY (coordinador_id) REFERENCES USUARI(usuari_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE PASSWORD_RESET_TEMP
(
    /* PRT */
    email           VARCHAR(250) NOT NULL,
    clau            VARCHAR(250) NOT NULL,
    data_expiracio  DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE RESULTAT_APRENENTATGE
(
    /* RA */
    resultat_aprenentatge_id INT NOT NULL,
    unitat_formativa_id INT NOT NULL,
    descripcio VARCHAR(500) NOT NULL,

    CONSTRAINT ResultatAprenentatgePK PRIMARY KEY (resultat_aprenentatge_id),
    CONSTRAINT RA_UnitatFormativaFK FOREIGN KEY (unitat_formativa_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE CRITERI_AVALUACIO
(
    /* CAV */
    criteri_avaluacio_id INT NOT NULL,
    resultat_aprenentatge_id INT NOT NULL,
    descripcio VARCHAR(500) NOT NULL,

    CONSTRAINT CriteriAvaluacioPK PRIMARY KEY (criteri_avaluacio_id),
    CONSTRAINT CAV_ResultatAprenentatgeFK FOREIGN KEY (resultat_aprenentatge_id) REFERENCES RESULTAT_APRENENTATGE(resultat_aprenentatge_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE CONTINGUT_UF
(
    /* CUF */
    contingut_uf_id INT NOT NULL,
    unitat_formativa_id INT NOT NULL,
    descripcio VARCHAR(500) NOT NULL,

    CONSTRAINT ContingutUFPK PRIMARY KEY (contingut_uf_id),
    CONSTRAINT CUF_UnitatFormativaFK FOREIGN KEY (unitat_formativa_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE SUBCONTINGUT_UF
(
    /* SCUF */
    subcontingut_uf_id INT NOT NULL,
    contingut_uf_id INT NOT NULL,
    descripcio VARCHAR(500) NOT NULL,

    CONSTRAINT SubContingutUFPK PRIMARY KEY (subcontingut_uf_id),
    CONSTRAINT SCUF_ContingutUFFK FOREIGN KEY (contingut_uf_id) REFERENCES CONTINGUT_UF(contingut_uf_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE GEOLOCALITZACIO_IP
(
    /* GIP */
    ip VARCHAR(15) NOT NULL,
    is_eu BIT,
    city VARCHAR(100),
    region VARCHAR(100),
    region_code CHAR(2),
    country_name VARCHAR(100),
    country_code CHAR(2),
    latitude REAL,
    longitude REAL,
    postal VARCHAR(10),
    calling_code VARCHAR(5),
    flag_url VARCHAR(100),
    asn VARCHAR(20),
    asn_name VARCHAR(100),
    asn_domain VARCHAR(50),
    asn_route VARCHAR(20),
    asn_type VARCHAR(5),
    is_tor BIT,
    is_proxy BIT,
    is_anonymous BIT,
    is_known_attacker BIT,
    is_known_abuser BIT,
    is_threat BIT,
    is_bogon BIT,
    data_modificacio DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	
    CONSTRAINT GeolocalitzacioIPPK PRIMARY KEY (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE TIPUS_MATERIAL
(
    /* TM */
    tipus_material_id INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(200) NOT NULL,

    CONSTRAINT TipusMaterialPK PRIMARY KEY (tipus_material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE MATERIAL
(
    /* M */
    material_id INT NOT NULL,
    tipus_material_id INT NOT NULL,
    familia_fp_id INT,
    responsable_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
	codi VARCHAR(15) NOT NULL,
    descripcio TEXT,
    ambit VARCHAR(50),
    ubicacio VARCHAR(100),
    data_compra DATE,
    es_obsolet BIT NOT NULL DEFAULT 0,
    es_prestec BIT NOT NULL DEFAULT 1,
	
	CONSTRAINT MaterialPK PRIMARY KEY (material_id),
	CONSTRAINT M_TipusMaterialFK FOREIGN KEY (tipus_material_id) REFERENCES TIPUS_MATERIAL(tipus_material_id),
	CONSTRAINT M_FamiliaFPFK FOREIGN KEY (familia_fp_id) REFERENCES FAMILIA_FP(familia_fp_id),
	CONSTRAINT M_UsuariFK FOREIGN KEY (responsable_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE PRESTEC_MATERIAL
(
    /* PM */
    prestec_material_id INT NOT NULL AUTO_INCREMENT,
    material_id INT NOT NULL,
	usuari_id INT NOT NULL,
	responsable_id INT NOT NULL,
    data_sortida DATE,
    data_entrada DATE,
    nota VARCHAR(200),
	
	CONSTRAINT PrestecMaterialPK PRIMARY KEY (prestec_material_id),
	CONSTRAINT PM_MaterialFK FOREIGN KEY (material_id) REFERENCES MATERIAL(material_id),
    CONSTRAINT PM_UsuariFK FOREIGN KEY (usuari_id) REFERENCES USUARI(usuari_id),
    CONSTRAINT PM_ResponsableFK FOREIGN KEY (responsable_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE RESERVA_MATERIAL
(
    /* RM */
    reserva_material_id INT NOT NULL,
    material_id INT NOT NULL,
	usuari_id INT NOT NULL,
    data_sortida DATE,
    data_entrada DATE,
    nota VARCHAR(200),
	
	CONSTRAINT ReservaMaterialPK PRIMARY KEY (reserva_material_id),
	CONSTRAINT RM_MaterialFK FOREIGN KEY (material_id) REFERENCES MATERIAL(material_id),
    CONSTRAINT RM_UsuariFK FOREIGN KEY (usuari_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE BORSA_TREBALL
(
    /* BT */
    borsa_treball_id INT NOT NULL AUTO_INCREMENT,
    cicle_formatiu_id INT NOT NULL,
    data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP,
    empresa VARCHAR(100) NOT NULL,
    contacte VARCHAR(100),	
    telefon VARCHAR(25),  
    poblacio VARCHAR(120),
    email VARCHAR(100), 
    web VARCHAR(100), 
	descripcio TEXT,
    ip VARCHAR(15),
	publicat BIT NOT NULL DEFAULT 0,
	
    CONSTRAINT BorsaTreballPK PRIMARY KEY (borsa_treball_id),
    CONSTRAINT BT_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE OBJECTIU_CONTINGUT
(
    /* OC LOGSE
       Objectius terminals (O)
       Continguts de fets, conceptes i sistemes conceptuals (F)
       Continguts de procediments (P)
       Continguts d'actituds (A)
       Activitats formatives (M)
       Criteris d'avaluació (V)
    */
    objectiu_contingut_id INT NOT NULL,
    modul_professional_id INT NOT NULL,
    tipus CHAR(1) NOT NULL CHECK (tipus IN ('O', 'F', 'P', 'A', 'M', 'V')),
    descripcio VARCHAR(500) NOT NULL,

    CONSTRAINT ObjectiuContingutPK PRIMARY KEY (objectiu_contingut_id),
    CONSTRAINT OC_ModulProfessionalFK FOREIGN KEY (modul_professional_id) REFERENCES MODUL_PROFESSIONAL(modul_professional_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE PREU_MATRICULA 
(
    /* PM */
    preu_matricula_id INT NOT NULL AUTO_INCREMENT,
    any_academic_id INT NOT NULL,
    cicle_formatiu_id INT NOT NULL,
    nivell INT CHECK (nivell IN (0, 1, 2)),
    nom VARCHAR(20) NOT NULL,
    preu REAL NOT NULL,
    numero_uf INT NOT NULL,

    CONSTRAINT PreuMatriculaPK PRIMARY KEY (preu_matricula_id),
    CONSTRAINT PM_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id),
    CONSTRAINT PM_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE BONIFICACIO_MATRICULA 
(
    /* BM */
    bonificacio_matricula_id INT NOT NULL AUTO_INCREMENT,
    any_academic_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    valor REAL NOT NULL,
    tipus CHAR NOT NULL, /* Percentatge, Euros */
    unitat_formativa_id INT NULL,

    CONSTRAINT BonificacioMatriculaPK PRIMARY KEY (bonificacio_matricula_id),
    CONSTRAINT BM_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id),
    CONSTRAINT BM_UnitatFormativaFK FOREIGN KEY (unitat_formativa_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE PROPOSTA_MATRICULA
(
    /* PM */
    proposta_matricula_id INT NOT NULL AUTO_INCREMENT,
    matricula_id INT NOT NULL,
    unitat_formativa_id INT NOT NULL, 
	baixa BIT NOT NULL DEFAULT 0,
	
    CONSTRAINT PropostaMatriculaPK PRIMARY KEY (proposta_matricula_id),
    CONSTRAINT PM_MatriculaFK FOREIGN KEY (matricula_id) REFERENCES MATRICULA(matricula_id),
    CONSTRAINT PM_UnitatFormativaFK FOREIGN KEY (unitat_formativa_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE DOCUMENT (
    /* D */
    document_id INT NOT NULL AUTO_INCREMENT,
	codi VARCHAR(100) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    estudi CHAR(3) NOT NULL DEFAULT 'GEN' CHECK (estudi IN ('GEN', 'ESO', 'BAT', 'CF0,', 'CFB', 'CFM', 'CFS')),
    subestudi CHAR(3), /* FPB, APD, CAI, DAM, FIP, HBD, SMX, ... */
    categoria CHAR(1), /* Document de centre, Imprès de funcionament */
    visibilitat CHAR(1) NOT NULL DEFAULT 'V', /* priVat, púBlic */
    solicitant CHAR(1) NOT NULL, /* Tutor, Alumne */
    lliurament CHAR(2) NOT NULL, /* TUtor, Tutor Fct, Tutor Dual, SEcretaria, Cap Estudis, Coordinador Fp, Coordinador Dual */
    custodia CHAR(2) NOT NULL, /* TUtor, Tutor Fct, Tutor Dual, SEcretaria, Cap Estudis, Coordinador Fp, Coordinador Dual */
    observacions TEXT,
    data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_modificacio DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT DocumentPK PRIMARY KEY (document_id),
	CONSTRAINT DocumentUC_Codi UNIQUE (codi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE DOCUMENT_VERSIO (
    /* DV */
    document_versio_id INT NOT NULL AUTO_INCREMENT,
    document_id INT NOT NULL,
    versio INT NOT NULL DEFAULT 0,
    descripcio_modificacio VARCHAR(255) NOT NULL,
    enllac VARCHAR(255) NOT NULL,
    estat char(1) NOT NULL DEFAULT 'E' CHECK (estat IN ('E', 'R', 'V', 'A')), /* Elaboració, Realitzat, reVisió, Aprovat */
    data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_modificacio DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    usuari_realitzat INT,
    data_realitzat DATE,
    usuari_revisat INT,
    data_revisat DATE,
    usuari_aprovat INT,
    data_aprovat DATE,

	CONSTRAINT DocumentPK PRIMARY KEY (document_versio_id),
    CONSTRAINT DV_DocumentFK FOREIGN KEY (document_id) REFERENCES DOCUMENT(document_id),
    CONSTRAINT DV_UsuariRealitzatFK FOREIGN KEY (usuari_realitzat) REFERENCES USUARI(usuari_id),
    CONSTRAINT DV_UsuariRevisatFK FOREIGN KEY (usuari_revisat) REFERENCES USUARI(usuari_id),
    CONSTRAINT DV_UsuariAprovatFK FOREIGN KEY (usuari_aprovat) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE VIEW CURS_ACTUAL AS
	SELECT C.* 
    FROM CURS C 
    LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id)
    LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id)
    WHERE AA.actual=1
;


/*
 * Edat
 *
 * Calcula l'edat donada una data de naixement.
 *
 * Ús:
 *   SELECT nom, cognom1, data_naixement, Edat(data_naixement) AS edat FROM USUARI;
 *
 * @param datetime data_naixement Data per calcular l'edat.
 * @return integer Anys entre la data de naixement i ara.
 */
DELIMITER //
CREATE FUNCTION Edat(data_naixement DATETIME)
RETURNS INT
BEGIN
    RETURN TIMESTAMPDIFF (YEAR, data_naixement, CURDATE());
END //
DELIMITER ;

/*
 * ObteNotaConvocatoria
 *
 * Retorna la nota corresponent a la convocatòria actual.
 *
 * Ús:
 *   SELECT notes_id, ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria) FROM NOTES;
 *
 * @param integer nota1 Nota de la 1a convocatòria.
 * @param integer nota2 Nota de la 2a convocatòria.
 * @param integer nota3 Nota de la 3a convocatòria.
 * @param integer nota4 Nota de la 4a convocatòria.
 * @param integer nota5 Nota de la 5a convocatòria.
 * @param integer convocatoria Convocatòria actual.
 * @return integer Nota corresponent a la convocatòria actual.
 */
DELIMITER //
CREATE FUNCTION ObteNotaConvocatoria(nota1 INT, nota2 INT, nota3 INT, nota4 INT, nota5 INT, convocatoria INT)
RETURNS INT
BEGIN 
    DECLARE Nota INT;
    IF convocatoria = 5 THEN SET Nota = nota5;
    ELSEIF convocatoria = 4 THEN SET Nota = nota4;
    ELSEIF convocatoria = 3 THEN SET Nota = nota3;
    ELSEIF convocatoria = 2 THEN SET Nota = nota2;
    ELSEIF convocatoria = 1 THEN SET Nota = nota1;
    ELSE SET Nota = NULL;
    END IF;
    RETURN Nota;
END //
DELIMITER ;

/*
 * UltimaMatriculaAlumne
 *
 * Retorna la última matrícula d'un alumne per a aquell curs que es vol matricular (comprova cicle).
 *
 * Ús:
 *   SELECT UltimaMatriculaAlumne(AlumneId, CursId);
 *
 * @param integer AlumneId Identificador de l'alumne.
 * @param integer CursId Identificador del curs.
 * @return integer Identificador de la darrera matrícula.
 */
DELIMITER //
CREATE FUNCTION UltimaMatriculaAlumne(AlumneId INT, CursId INT)
RETURNS INT
BEGIN 
    DECLARE MatriculaId INT;
    SET MatriculaId = -1;
    
    SELECT IFNULL(matricula_id, -1) AS matricula_id 
        INTO MatriculaId
        FROM MATRICULA M
        LEFT JOIN CURS C ON (M.curs_id=C.curs_id)
        LEFT JOIN CICLE_PLA_ESTUDI CPE ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id)
        LEFT JOIN ANY_ACADEMIC AA ON (CPE.any_academic_id=AA.any_academic_id)
        WHERE alumne_id=AlumneId AND AA.actual<>1
        AND CPE.cicle_formatiu_id IN (
            SELECT CPE.cicle_formatiu_id 
            FROM CURS C
            LEFT JOIN CICLE_PLA_ESTUDI CPE ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id)
            WHERE curs_id=CursId
        )
        ORDER BY any_inici DESC
        LIMIT 1;

    RETURN MatriculaId;    
END //
DELIMITER ;

/*
 * UltimaNota
 *
 * Donat un registre de notes, torna la nota de la última convocatòria.
 *
 * @param integer NotesId Identificador del registre de notes.
 * @return integer Nota de la última convocatòria.
 */
DELIMITER //
CREATE FUNCTION UltimaNota(NotesId INT)
RETURNS INT
BEGIN 
	DECLARE Nota INT;

	SELECT IFNULL(nota5, IFNULL(nota4, IFNULL(nota3, IFNULL(nota2, IFNULL(nota1, -1))))) 
	    INTO Nota
	    FROM NOTES 
		WHERE notes_id=NotesId;    

    RETURN Nota;	
END //
DELIMITER ;


/*
 * CreaPlaEstudisModul
 * Crea el pla d'estudis per a un mòdul (copiant unitats actives).
 * @param integer ModulPlaEstudiId Identificador del mòdul.
 */
DELIMITER //
CREATE PROCEDURE CreaPlaEstudisModul(IN ModulPlaEstudiId INT)
BEGIN
    DECLARE _unitat_formativa_id INT;
    DECLARE _nom VARCHAR(200);
    DECLARE _codi VARCHAR(5);
    DECLARE _hores INT;
    DECLARE _nivell INT;
    DECLARE _es_fct BIT;
    DECLARE done INT DEFAULT FALSE;

	BEGIN
		DECLARE cur CURSOR FOR 
		    SELECT unitat_formativa_id, nom, codi, hores, nivell, es_fct 
            FROM UNITAT_FORMATIVA 
            WHERE modul_professional_id in (SELECT modul_professional_id FROM MODUL_PLA_ESTUDI WHERE modul_pla_estudi_id=ModulPlaEstudiId)
			AND activa=1;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN cur;
		read_loop: LOOP
			FETCH cur INTO _unitat_formativa_id, _nom, _codi, _hores, _nivell, _es_fct ;
			IF done THEN
				LEAVE read_loop;
			END IF;
            INSERT INTO UNITAT_PLA_ESTUDI (unitat_formativa_id, modul_pla_estudi_id, nom, codi, hores, nivell, orientativa, es_fct ) 
                VALUES (_unitat_formativa_id, ModulPlaEstudiId, _nom, _codi, _hores, _nivell, 0, _es_fct );
		END LOOP;
		CLOSE cur;
	END;

END //
DELIMITER ;

/*
 * CreaPlaEstudisCicle
 * Crea el pla d'estudis per a un cicle (copiant mòduls i unitats actius).
 * @param integer CiclePlaEstudiId Identificador del cicle.
 */
DELIMITER //
CREATE PROCEDURE CreaPlaEstudisCicle(IN CiclePlaEstudiId INT)
BEGIN
    DECLARE _modul_professional_id INT;
    DECLARE _nom VARCHAR(200);
    DECLARE _codi VARCHAR(5);
    DECLARE _hores INT;
    DECLARE _hores_setmana INT;
    DECLARE _especialitat VARCHAR(30);
    DECLARE _cos CHAR(1);
    DECLARE _es_fct BIT;
    DECLARE done INT DEFAULT FALSE;

	BEGIN
		DECLARE cur CURSOR FOR 
		    SELECT modul_professional_id, nom, codi, hores, hores_setmana, especialitat, cos, es_fct 
            FROM MODUL_PROFESSIONAL 
            WHERE cicle_formatiu_id in (SELECT cicle_formatiu_id FROM CICLE_PLA_ESTUDI WHERE cicle_pla_estudi_id=CiclePlaEstudiId)
			AND actiu=1;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN cur;
		read_loop: LOOP
			FETCH cur INTO _modul_professional_id, _nom, _codi, _hores, _hores_setmana, _especialitat, _cos, _es_fct;
			IF done THEN
				LEAVE read_loop;
			END IF;
            INSERT INTO MODUL_PLA_ESTUDI (modul_professional_id, cicle_pla_estudi_id, nom, codi, hores, hores_setmana, especialitat, cos, es_fct) 
                VALUES (_modul_professional_id, CiclePlaEstudiId, _nom, _codi, _hores, _hores_setmana, _especialitat, _cos, _es_fct);
            SET @ModulPlaEstudiId = LAST_INSERT_ID();
			CALL CreaPlaEstudisModul(@ModulPlaEstudiId);
		END LOOP;
		CLOSE cur;
	END;

END //
DELIMITER ;

/*
 * CreaPlaEstudis
 * Crea el pla d'estudis per a un any acadèmic (copiant cicles, mòduls i unitats actius).
 * @param integer AnyAcademicId Identificador de l'any acadèmic.
 */
DELIMITER //
CREATE PROCEDURE CreaPlaEstudis(IN AnyAcademicId INT)
BEGIN
    DECLARE _cicle_formatiu_id INT;
    DECLARE _nom VARCHAR(200);
    DECLARE _grau CHAR(2);
    DECLARE _codi CHAR(3);
    DECLARE _codi_xtec CHAR(4);
    DECLARE done INT DEFAULT FALSE;

	BEGIN
		DECLARE cur CURSOR FOR SELECT cicle_formatiu_id, nom, grau, codi, codi_xtec FROM CICLE_FORMATIU WHERE actiu=1;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN cur;
		read_loop: LOOP
			FETCH cur INTO _cicle_formatiu_id, _nom, _grau, _codi, _codi_xtec;
			IF done THEN
				LEAVE read_loop;
			END IF;
            INSERT INTO CICLE_PLA_ESTUDI (cicle_formatiu_id, any_academic_id, nom, grau, codi, codi_xtec) 
                VALUES (_cicle_formatiu_id, AnyAcademicId, _nom, _grau, _codi, _codi_xtec);
            SET @CiclePlaEstudiId = LAST_INSERT_ID();
			CALL CreaPlaEstudisCicle(@CiclePlaEstudiId);
		END LOOP;
		CLOSE cur;
	END;

END //
DELIMITER ;

/*
 * SuprimeixPlaEstudis
 * Suprimeix el pla d'estudis d'un any acadèmic.
 * @param integer AnyAcademicId Identificador de l'any acadèmic.
 */
DELIMITER //
CREATE PROCEDURE SuprimeixPlaEstudis(IN AnyAcademicId INT)
BEGIN
    DELETE FROM PROFESSOR_UF WHERE uf_id IN (
        SELECT unitat_pla_estudi_id
        FROM UNITAT_PLA_ESTUDI UPE
        LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
        LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
        WHERE CPE.any_academic_id=AnyAcademicId
    );
    DELETE FROM UNITAT_PLA_ESTUDI WHERE modul_pla_estudi_id IN (
        SELECT modul_pla_estudi_id
        FROM MODUL_PLA_ESTUDI MPE
        LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
        WHERE CPE.any_academic_id=AnyAcademicId
    );
    DELETE FROM MODUL_PLA_ESTUDI WHERE cicle_pla_estudi_id IN (
        SELECT cicle_pla_estudi_id
        FROM CICLE_PLA_ESTUDI CPE
        WHERE CPE.any_academic_id=AnyAcademicId
    );
    DELETE FROM CURS WHERE cicle_formatiu_id IN (
        SELECT cicle_pla_estudi_id
        FROM CICLE_PLA_ESTUDI CPE
        WHERE CPE.any_academic_id=AnyAcademicId
    );
    DELETE FROM CICLE_PLA_ESTUDI WHERE any_academic_id=AnyAcademicId;
END //
DELIMITER ;

/*
 * UFPlaEstudiActual
 *
 * Retorna l'identificador de la UF del pla d'estudis actual a partir de l'identificador d'una UF dels plans d'estudis anteriors.
 *
 * Ús:
 *   SELECT UFPlaEstudiActual(UFId);
 *
 * @param integer UFId Identificador de la UF d'un pla d'estudis no actual.
 * @return integer Identificador de la UF del pla d'estudis actual.
 */
DELIMITER //
CREATE FUNCTION UFPlaEstudiActual(UFId INT)
RETURNS INT
BEGIN 
    DECLARE UFIdActual INT;
    SET UFIdActual = -1;

    SELECT IFNULL(UPE.unitat_pla_estudi_id, -1) AS unitat_pla_estudi_id 
        INTO UFIdActual
        FROM UNITAT_PLA_ESTUDI UPE
        LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
        LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
        LEFT JOIN ANY_ACADEMIC AA ON (CPE.any_academic_id=AA.any_academic_id)
        WHERE AA.actual=1
        AND UPE.unitat_formativa_id IN (
            SELECT UPE.unitat_formativa_id
            FROM UNITAT_PLA_ESTUDI UPE
            LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=UPE.unitat_formativa_id)
            WHERE unitat_pla_estudi_id=UFId
        );

    RETURN UFIdActual;    
END //
DELIMITER ;

/*
 * CopiaNotesAnteriorMatricula
 *
 * Copia les notes de l'anterior matrícula.
 *
 * @param integer AlumneId Identificador de l'alumne.
 * @param integer MatriculaId Identificador de la matrícula actual.
 * @param integer MatriculaAnteriorId Identificador de l'anterior matrícula.
 */
DELIMITER //
CREATE PROCEDURE CopiaNotesAnteriorMatricula(IN AlumneId INT, MatriculaId INT, IN MatriculaAnteriorId INT)
BEGIN
    DECLARE _uf_id, _nota1, _nota2, _nota3, _nota4, _nota5, _convocatoria INT;
    DECLARE _exempt, _convalidat, _junta BIT;
    DECLARE done INT DEFAULT FALSE;

    DROP TABLE IF EXISTS NotesTemp;
    CREATE TEMPORARY TABLE NotesTemp AS (SELECT * FROM NOTES WHERE matricula_id=MatriculaAnteriorId);

	BEGIN
		DECLARE curNotes CURSOR FOR SELECT uf_id, nota1, nota2, nota3, nota4, nota5, exempt, convalidat, junta, convocatoria FROM NotesTemp;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

		OPEN curNotes;

		read_loop: LOOP
			FETCH curNotes INTO _uf_id, _nota1, _nota2, _nota3, _nota4, _nota5, _exempt, _convalidat, _junta, _convocatoria;
			IF done THEN
				LEAVE read_loop;
			END IF;
			UPDATE NOTES SET nota1=_nota1, nota2=_nota2, nota3=_nota3, nota4=_nota4, nota5=_nota5, exempt=_exempt, convalidat=_convalidat, junta=_junta, convocatoria=_convocatoria 
				WHERE matricula_id=MatriculaId AND uf_id=UFPlaEstudiActual(_uf_id);
		END LOOP;

		CLOSE curNotes;
	END;
    DROP TABLE NotesTemp;
    
    UPDATE NOTES SET convocatoria=0 
        WHERE matricula_id=MatriculaId AND convocatoria<>0 AND UltimaNota(notes_id)>=5;
		
    UPDATE NOTES SET convocatoria=convocatoria+1 
        WHERE matricula_id=MatriculaId AND convocatoria<>0 AND UltimaNota(notes_id)<5 AND UltimaNota(notes_id)!=-1 AND nota1 IS NOT NULL;		
END //
DELIMITER ;

/*
 * CreaMatricula
 *
 * Crea la matrícula per a un alumne. Quan es crea la matrícula:
 *   1. Pel nivell que sigui, es creen les notes, una per cada UF d'aquell cicle
 *   2. Si l'alumne és a 2n, l'aplicació ha de buscar les que li han quedar de primer per afegir-les
 *
 * Ús:
 *   CALL CreaMatricula(1, 1013, 'A', 'AB', @retorn);
 *   SELECT @retorn; 
 *
 * @param integer CursId Id del curs.
 * @param integer AlumneId Id de l'alumne.
 * @param string Grup Grup (cap, A, B, C).
 * @param string GrupTutoria Grup de tutoria.
 * @return integer Retorn Valor de retorn: 0 Ok, -1 Alumne ja matriculat.
 */
DELIMITER //
CREATE PROCEDURE CreaMatricula
(
    IN CursId INT, 
    IN AlumneId INT, 
    IN Grup CHAR(1), 
    IN GrupTutoria VARCHAR(2), 
    OUT Retorn INT
)
BEGIN
    IF EXISTS (SELECT * FROM MATRICULA WHERE curs_id=CursId AND alumne_id=AlumneId) THEN
    BEGIN
        SELECT -1 INTO Retorn;
    END;
    ELSE
    BEGIN
		SET @MatriculaAnteriorId = (SELECT UltimaMatriculaAlumne(AlumneId, CursId));
        INSERT INTO MATRICULA (curs_id, alumne_id, grup, grup_tutoria) 
            VALUES (CursId, AlumneId, Grup, GrupTutoria);
        SET @MatriculaId = LAST_INSERT_ID();
		SET @CicleId = (SELECT cicle_formatiu_id FROM CURS WHERE curs_id=CursId);
		SET @Nivell = (SELECT nivell FROM CURS WHERE curs_id=CursId);
		SELECT 0 INTO Retorn;
        INSERT INTO NOTES (matricula_id, uf_id, convocatoria)
            SELECT @MatriculaId, UPE.unitat_pla_estudi_id, 1 
            FROM UNITAT_PLA_ESTUDI UPE
            LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
            LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
            WHERE CPE.cicle_pla_estudi_id=@CicleId
            AND UPE.nivell<=@Nivell;
		CALL CopiaNotesAnteriorMatricula(AlumneId, @MatriculaId, @MatriculaAnteriorId);
        /* Aplica proposta matrícula */
        UPDATE NOTES N
		LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (N.uf_id=UPE.unitat_pla_estudi_id)
        SET baixa=1
        WHERE matricula_id=@MatriculaId AND unitat_formativa_id IN (SELECT unitat_formativa_id FROM PROPOSTA_MATRICULA WHERE matricula_id=@MatriculaAnteriorId AND baixa=1);
    END;
    END IF;
END //
DELIMITER ;

/*
 * CreaMatriculaDNI
 *
 * Crea la matrícula per a un alumne a partir del DNI.
 *
 * Ús:
 *   CALL CreaMatriculaDNI(1, '12345678A', 'A', 'AB', @retorn);
 *   SELECT @retorn; 
 *
 * @param integer CursId Id del curs.
 * @param string DNI DNI de l'alumne.
 * @param string Grup Grup (cap, A, B, C).
 * @param string GrupTutoria Grup de tutoria.
 * @return integer Retorn Valor de retorn: 
 *    0 Ok.
 *   -1 Alumne ja matriculat.
 *   -2 DNI inexistent.
 */
DELIMITER //
CREATE PROCEDURE CreaMatriculaDNI
(
    IN CursId INT, 
    IN DNI VARCHAR(15), 
    IN Grup CHAR(1), 
    IN GrupTutoria VARCHAR(2), 
    OUT Retorn INT
)
BEGIN
    IF NOT EXISTS (SELECT * FROM USUARI WHERE document=DNI AND es_alumne=1) THEN
    BEGIN
        SELECT -2 INTO Retorn;
    END;
    ELSE
    BEGIN
		SET @AlumneId = (SELECT usuari_id FROM USUARI WHERE document=DNI AND es_alumne=1);
        CALL CreaMatricula(CursId, @AlumneId, Grup, GrupTutoria, Retorn);
    END;
    END IF;
END //
DELIMITER ;

/*
 * CopiaTutors
 * Copia els tutors d'un any acadèmic a un altre.
 * @param integer AnyAcademicIdOrigen Identificador de l'any acadèmic origen.
 * @param integer AnyAcademicIdDesti Identificador de l'any acadèmic destí.
 */
DELIMITER //
CREATE PROCEDURE CopiaTutors(IN AnyAcademicIdOrigen INT, IN AnyAcademicIdDesti INT)
BEGIN
    DECLARE _curs_id, _professor_id, _curs_id_desti INT;
    DECLARE _grup_tutoria VARCHAR(2);
    DECLARE done INT DEFAULT FALSE;

    BEGIN
        DECLARE cur CURSOR FOR
            SELECT T.curs_id, T.professor_id, T.grup_tutoria, 
            (   SELECT curs_id 
                FROM CURS C2 
                LEFT JOIN CICLE_PLA_ESTUDI CPE2 ON (CPE2.cicle_pla_estudi_id=C2.cicle_formatiu_id) 
                WHERE CPE2.cicle_formatiu_id=CPE.cicle_formatiu_id AND C2.nivell=C.nivell AND CPE2.any_academic_id=AnyAcademicIdDesti
            ) AS CursIdDesti
            FROM TUTOR T
            LEFT JOIN CURS C ON (C.curs_id=T.curs_id)
            LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id)
            WHERE CPE.any_academic_id=AnyAcademicIdOrigen;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        OPEN cur;
        read_loop: LOOP
            FETCH cur INTO _curs_id, _professor_id, _grup_tutoria, _curs_id_desti;
            IF done THEN
                LEAVE read_loop;
            END IF;
            IF _curs_id_desti<>NULL THEN
                INSERT INTO TUTOR (curs_id, professor_id, grup_tutoria) VALUES (_curs_id_desti, _professor_id, _grup_tutoria);
            END IF;
        END LOOP;
        CLOSE cur;
    END;
END //
DELIMITER ;

/*
 * CopiaProgramacions
 * Copia les programacions d'un any acadèmic a un altre.
 * @param integer AnyAcademicIdOrigen Identificador de l'any acadèmic origen.
 * @param integer AnyAcademicIdDesti Identificador de l'any acadèmic destí.
 */
DELIMITER //
CREATE PROCEDURE CopiaProgramacions(IN AnyAcademicIdOrigen INT, IN AnyAcademicIdDesti INT)
BEGIN
    DECLARE _modul_pla_estudi_id, _modul_pla_estudi_id_desti INT;
    DECLARE _metodologia, _criteris_avaluacio, _recursos TEXT;
    DECLARE done INT DEFAULT FALSE;

    BEGIN
        DECLARE cur CURSOR FOR
        SELECT MPE.modul_pla_estudi_id, MPE.metodologia, MPE.criteris_avaluacio, MPE.recursos,
            (   SELECT modul_pla_estudi_id 
                FROM MODUL_PLA_ESTUDI MPE2
                LEFT JOIN CICLE_PLA_ESTUDI CPE2 ON (CPE2.cicle_pla_estudi_id=MPE2.cicle_pla_estudi_id)
                WHERE MPE2.modul_professional_id=MPE.modul_professional_id AND CPE2.any_academic_id=AnyAcademicIdDesti
                LIMIT 1
            ) AS ModulPlaEstudiIdDesti			
            FROM MODUL_PLA_ESTUDI MPE
            LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
            WHERE CPE.any_academic_id=AnyAcademicIdOrigen;        
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        OPEN cur;
        read_loop: LOOP
            FETCH cur INTO _modul_pla_estudi_id, _metodologia, _criteris_avaluacio, _recursos, _modul_pla_estudi_id_desti;
            IF done THEN
                LEAVE read_loop;
            END IF;
            UPDATE MODUL_PLA_ESTUDI SET metodologia=_metodologia, criteris_avaluacio=_criteris_avaluacio, recursos=_recursos WHERE modul_pla_estudi_id=_modul_pla_estudi_id_desti;
        END LOOP;
        CLOSE cur;
    END;
END //
DELIMITER ;

/*
 * SuprimeixPropietatCSS
 * Suprimeix la propietat CSS especificada del camp d'una taula.
 * Ús: CALL SuprimeixPropietatCSS('MODUL_PLA_ESTUDI', 'metodologia', 'font-family:');
 * @param string Taula.
 * @param string Camp.
 * @param string Propietat.
 */
DELIMITER //
CREATE PROCEDURE SuprimeixPropietatCSS
(
    IN Taula VARCHAR(50), 
    IN Camp VARCHAR(50), 
    IN Propietat VARCHAR(50) 
)
BEGIN
    SET @SQL = CONCAT("UPDATE ", Taula, " SET ", Camp, "=");
    SET @LocatePropietat = CONCAT("locate('", Propietat, "', ", Camp, ")");
    SET @SubStr = CONCAT("substr(", Camp, ", ", @LocatePropietat, ", locate(';', ", Camp, ", ", @LocatePropietat, ")-", @LocatePropietat, "+1)"); 
    SET @SQL = CONCAT(@SQL, "replace(", Camp, ", ", @SubStr, ", '')");
    PREPARE stmt FROM @SQL;
    EXECUTE stmt;
END //
DELIMITER ;

/*
 * FormataData
 *
 * Donat un camp de tipus data, el retorna en el format dd/mm/yyyy.
 *
 * @param date DataMySQL Data.
 * @return string Data en el format dd/mm/yyyy.
 */
DELIMITER //
CREATE FUNCTION FormataData(DataMySQL DATE)
RETURNS VARCHAR(10)
BEGIN 
    RETURN DATE_FORMAT(DataMySQL, "%d/%m/%Y");
END //
DELIMITER ;

/*
 * FormataNomCognom1Cognom2
 *
 * Formata el nom d'una persona a l'estil NCC.
 *
 * @param string Nom.
 * @param string Cognom1.
 * @param string Cognom2.
 * @return string Nom formatat NCC.
 */
DELIMITER //
CREATE FUNCTION FormataNomCognom1Cognom2(Nom VARCHAR(100), Cognom1 VARCHAR(100), Cognom2 VARCHAR(100))
RETURNS VARCHAR(255)
BEGIN 
    RETURN TRIM(CONCAT(nom, ' ', cognom1, ' ', IFNULL(cognom2, '')));
END //
DELIMITER ;

/*
 * FormataCognom1Cognom2Nom
 *
 * Formata el nom d'una persona a l'estil CC,N.
 *
 * @param string Nom.
 * @param string Cognom1.
 * @param string Cognom2.
 * @return string Nom formatat CC,N.
 */
DELIMITER //
CREATE FUNCTION FormataCognom1Cognom2Nom(Nom VARCHAR(100), Cognom1 VARCHAR(100), Cognom2 VARCHAR(100))
RETURNS VARCHAR(255)
BEGIN 
    RETURN CONCAT(TRIM(CONCAT(cognom1, ' ', IFNULL(cognom2, ''))), ', ', nom);
END //
DELIMITER ;

/*
 * PercentatgeAprovat
 *
 * Retorna el percentatge aprovat d'una matrícula.
 *
 * @param integer MatriculaId Id de la matrícula.
 * @return real Percentatge aprovat.
 */
DELIMITER //
CREATE FUNCTION PercentatgeAprovat(MatriculaId INT)
RETURNS REAL
BEGIN 
    DECLARE Percentatge REAL;

    SELECT SUM(CASE 
	    WHEN IFNULL(nota5, IFNULL(nota4, IFNULL(nota3, IFNULL(nota2, IFNULL(nota1, -1))))) >=5 THEN hores
        ELSE 0
        END)/SUM(hores)*100 AS PercentatgeAprovat
    INTO Percentatge
    FROM NOTES N
    LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=N.uf_id)
    WHERE N.matricula_id=MatriculaId;
    
    RETURN Percentatge;  	
	
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER AU_CopiaTrimestre AFTER UPDATE ON CURS FOR EACH ROW
BEGIN
    IF NEW.trimestre = 2 AND OLD.trimestre = 1 THEN
        /* Pas del 1r trimestre al 2n */
        UPDATE NOTES set nota_t1 = ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria) WHERE matricula_id IN (SELECT matricula_id FROM MATRICULA WHERE curs_id=OLD.curs_id);
        INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge)
            VALUES (1, 'Taula CURS', now(), '127.0.0.1', 'Trigger', CONCAT('Pas del 1r trimestre al 2n. curs_id=', OLD.curs_id));
    ELSEIF NEW.trimestre = 3 AND OLD.trimestre = 2 THEN
        /* Pas del 2n trimestre al 3r */
        UPDATE NOTES set nota_t2 = ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria) WHERE matricula_id IN (SELECT matricula_id FROM MATRICULA WHERE curs_id=OLD.curs_id);
        INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge)
            VALUES (1, 'Taula CURS', now(), '127.0.0.1', 'Trigger', CONCAT('Pas del 2n trimestre al 3r. curs_id=', OLD.curs_id));
	ELSEIF NEW.avaluacio = 'EXT' AND OLD.avaluacio = 'ORD' THEN
        /* Pas del 3r trimestre a extraordinària */
        UPDATE NOTES set nota_t3 = ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria) WHERE matricula_id IN (SELECT matricula_id FROM MATRICULA WHERE curs_id=OLD.curs_id);
        INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge)
            VALUES (1, 'Taula CURS', now(), '127.0.0.1', 'Trigger', CONCAT('Pas del 3r trimestre a extraordinària. curs_id=', OLD.curs_id));
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER `AU_ActualitzaHoresMPE` AFTER UPDATE ON `UNITAT_PLA_ESTUDI` FOR EACH ROW BEGIN
    IF OLD.hores <> new.hores THEN
        SET @new_hores = NEW.hores;
        SET @old_hores = OLD.hores;
        SET @old_modul_pla_estudi_id = OLD.modul_pla_estudi_id;
        SET @old_unitat_pla_estudi_id = OLD.unitat_pla_estudi_id;
        UPDATE `MODUL_PLA_ESTUDI` SET `hores` = (SELECT SUM(hores) FROM UNITAT_PLA_ESTUDI WHERE modul_pla_estudi_id = @old_modul_pla_estudi_id)  WHERE (`modul_pla_estudi_id` = @old_modul_pla_estudi_id);
        INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge)
            VALUES (1,'Taula UNITAT_PLA_ESTUDI',NOW(),'127.0.0.1','Trigger', CONCAT('AU_ActualitzaHoresMPE RegistreId:',@old_unitat_pla_estudi_id,' Valor:',@new_hores,'->',@old_hores));
    END IF;
END //
DELIMITER ;
