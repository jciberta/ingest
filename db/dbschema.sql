CREATE DATABASE InGest;

USE InGest;

CREATE TABLE FAMILIA_FP
(
    /* FFP */
    familia_fp_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,

    CONSTRAINT FamiliaFPPK PRIMARY KEY (familia_fp_id)
);

CREATE TABLE CICLE_FORMATIU
(
    /* CF */
    cicle_formatiu_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    grau CHAR(2) NOT NULL,
    codi CHAR(3) NOT NULL,
    codi_xtec CHAR(4) NOT NULL,
    familia_fp_id INT NOT NULL,

    CONSTRAINT CicleFormatiuPK PRIMARY KEY (cicle_formatiu_id),
    CONSTRAINT CF_FamiliaFPFK FOREIGN KEY (familia_fp_id) REFERENCES FAMILIA_FP(familia_fp_id) 
);

CREATE TABLE MODUL_PROFESSIONAL
(
    /* MP */
    modul_professional_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    codi VARCHAR(5) NOT NULL,
    hores INT NOT NULL,
    hores_setmana INT,
    especialitat VARCHAR(20),
    cos CHAR(1),
    cicle_formatiu_id INT NOT NULL,

    CONSTRAINT ModulProfessionalPK PRIMARY KEY (modul_professional_id),
    CONSTRAINT CF_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id) 
);

CREATE TABLE UNITAT_FORMATIVA
(
    /* UF */
    unitat_formativa_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    codi VARCHAR(5) NOT NULL,
    hores INT NOT NULL,
    nivell INT, /* NULL, 1, 2 */
    modul_professional_id INT NOT NULL,

    CONSTRAINT UnitatFormativaPK PRIMARY KEY (unitat_formativa_id),
    CONSTRAINT MP_ModulProfessionalFK FOREIGN KEY (modul_professional_id) REFERENCES MODUL_PROFESSIONAL(modul_professional_id) 
);

CREATE TABLE USUARI
(
    /* U */
    usuari_id INT NOT NULL,
    username           VARCHAR(100) NOT NULL,
    password           VARCHAR(255) NOT NULL,
    nom          	   VARCHAR(100),
    cognom1            VARCHAR(100), 
    cognom2            VARCHAR(100),
    email              VARCHAR(100), 
    telefon            VARCHAR(20),  
    adreca             VARCHAR(255), 
    codi_postal		   VARCHAR(10), 
    poblacio           VARCHAR(120),
    pais               VARCHAR(2),
    es_admin BIT,
    es_direccio BIT,
    es_cap_estudis BIT,
    es_cap_departament BIT,
    es_tutor BIT,
    es_professor BIT,
    es_alumne BIT,
    es_pare BIT,
 
    CONSTRAINT UsuariPK PRIMARY KEY (usuari_id)
);

CREATE TABLE PROFESSOR_UF
(
    /* PUF */
    professor_uf_id 	INT NOT NULL AUTO_INCREMENT,
    professor_id 	INT NOT NULL,
    uf_id 		INT NOT NULL,
    grups 		VARCHAR(5),

    CONSTRAINT ProfessorUFPK PRIMARY KEY (professor_uf_id),
    CONSTRAINT PUF_UsuariFK FOREIGN KEY (professor_id) REFERENCES USUARI(usuari_id),
    CONSTRAINT PUF_UnitatFormativaFK FOREIGN KEY (uf_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id)
);

CREATE TABLE CURS
(
    /* C */
    curs_id 	INT NOT NULL AUTO_INCREMENT,
    any_inici 	INT NOT NULL,
    any_final 	INT NOT NULL,
    nom	 	VARCHAR(20),

    CONSTRAINT CursPK PRIMARY KEY (curs_id)
);

CREATE TABLE MATRICULA
(
    /* MAT */
    matricula_id 	INT NOT NULL AUTO_INCREMENT,
    curs_id 		INT NOT NULL, 
    alumne_id 		INT NOT NULL,
    cicle_formatiu_id 	INT NOT NULL,
    nivell 		INT NOT NULL,
    grup 		CHAR(1),

    CONSTRAINT MatriculaPK PRIMARY KEY (matricula_id),
    CONSTRAINT MAT_CursFK FOREIGN KEY (curs_id) REFERENCES CURS(curs_id),
    CONSTRAINT MAT_UsuariFK FOREIGN KEY (alumne_id) REFERENCES USUARI(usuari_id),

    CONSTRAINT MAT_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id)
);

CREATE TABLE NOTES
(
    /* N */
    notes_id 		INT NOT NULL AUTO_INCREMENT,
    matricula_id 	INT NOT NULL,
    uf_id 		INT NOT NULL,
    nota1  		INT, /* NP: -1, A: -10, A: -11 */
    nota2  		INT,
    nota3  		INT,
    nota4  		INT,
    nota5  		INT, /* Gràcia */
    exempt 		BIT,
    convalidat 		BIT,
    junta 		BIT,
    convocatoria	INT,

    CONSTRAINT NotesPK PRIMARY KEY (notes_id),
    CONSTRAINT N_MatriculaFK FOREIGN KEY (matricula_id) REFERENCES MATRICULA(matricula_id),
    CONSTRAINT N_UnitatFormativaFK FOREIGN KEY (uf_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id)
);


