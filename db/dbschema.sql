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
    nivell INT CHECK (nivell IN (1, 2)),
    modul_professional_id INT NOT NULL,
	data_inici DATE,
	data_final DATE,
    orientativa BIT,

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
    matricula_id INT NOT NULL AUTO_INCREMENT,
    curs_id INT NOT NULL, 
    alumne_id INT NOT NULL,
    cicle_formatiu_id INT NOT NULL,
    nivell INT CHECK (nivell IN (1, 2)),
    grup CHAR(1) CHECK (grup IN ('A', 'B', 'C')),
    grup_tutoria VARCHAR(2),
    baixa BIT,

    CONSTRAINT MatriculaPK PRIMARY KEY (matricula_id),
    CONSTRAINT MAT_CursFK FOREIGN KEY (curs_id) REFERENCES CURS(curs_id),
    CONSTRAINT MAT_UsuariFK FOREIGN KEY (alumne_id) REFERENCES USUARI(usuari_id),

    CONSTRAINT MAT_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id)
);

CREATE TABLE NOTES
(
    /* N */
    /* Notes: NP: -1, A: 100, NA: -100 */
    notes_id INT NOT NULL AUTO_INCREMENT,
    matricula_id INT NOT NULL,
    uf_id INT NOT NULL,
    nota1 INT CHECK (nota2 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),
    nota2 INT CHECK (nota2 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),
    nota3 INT CHECK (nota2 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),
    nota4 INT CHECK (nota2 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),
    nota5 INT CHECK (nota2 IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)), /* Gràcia */
    exempt BIT,
    convalidat BIT,
    junta BIT,
    baixa BIT, /* Baixa d'una UF */
    convocatoria INT, /* 0 (aprovat), 1, 2, 3, 4, 5 */

    CONSTRAINT NotesPK PRIMARY KEY (notes_id),
    CONSTRAINT N_MatriculaFK FOREIGN KEY (matricula_id) REFERENCES MATRICULA(matricula_id),
    CONSTRAINT N_UnitatFormativaFK FOREIGN KEY (uf_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id)
);

/*
 * CreaMatricula
 *
 * Crea la matrícula per a un alumne. Quan es crea la matrícula:
 *   1. Pel nivell que sigui, es creen les notes, una per cada UF d'aquell cicle
 *   2. Si l'alumne és a 2n, l'aplicació ha de buscar les que li han quedar de primer per afegir-les
 *
 * Ús:
 *   CALL CreaMatricula(1, 1013, 1, 1, 'A', @retorn);
 *   SELECT @retorn; 
 *
 * @param integer CursId Id del curs.
 * @param integer AlumneId Id de l'alumne.
 * @param integer CicleId Id del cicle.
 * @param integer Nivell Nivell (1r o 2n).
 * @param integer Grup Grup (cap, A, B, C).
 * @return integer Retorn Valor de retorn: 0 Ok, -1 Alumne ja matriculat.
 */
DELIMITER //
CREATE PROCEDURE CreaMatricula
(
    IN CursId INT, 
    IN AlumneId INT, 
    IN CicleId INT, 
    IN Nivell INT, 
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
        INSERT INTO MATRICULA (curs_id, alumne_id, cicle_formatiu_id, nivell, grup, grup_tutoria) 
            VALUES (CursId, AlumneId, CicleId, Nivell, Grup, GrupTutoria);
        SET @MatriculaId = LAST_INSERT_ID();
        SELECT 0 INTO Retorn;
            INSERT INTO NOTES (matricula_id, uf_id, convocatoria)
            SELECT @MatriculaId, UF.unitat_formativa_id, 1 
            FROM UNITAT_FORMATIVA UF
            LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id)
            LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id)
            WHERE CF.cicle_formatiu_id=CicleId
            AND UF.nivell=Nivell;
    END;
    END IF;
END //
DELIMITER ;


