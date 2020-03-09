CREATE DATABASE InGest DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

USE InGest;

CREATE TABLE FAMILIA_FP
(
    /* FFP */
    familia_fp_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,

    CONSTRAINT FamiliaFPPK PRIMARY KEY (familia_fp_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE USUARI
(
    /* U */
    usuari_id          INT NOT NULL,
    username           VARCHAR(100) NOT NULL,
    password           VARCHAR(255) NOT NULL,
    nom          	   VARCHAR(100),
    cognom1            VARCHAR(100), 
    cognom2            VARCHAR(100),
    nom_complet        VARCHAR(255), 
	codi               VARCHAR(20), /* Codi professor, IDALU per alumne */
	sexe               CHAR(1), /* Home, Dona, Neutre */
	tipus_document     CHAR(1), /* Dni, Nie, Passaport */
	document           VARCHAR(15),
    email              VARCHAR(100), 
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
    es_cap_departament BIT NOT NULL DEFAULT 0,
    es_tutor           BIT NOT NULL DEFAULT 0,
    es_professor       BIT NOT NULL DEFAULT 0,
    es_alumne          BIT NOT NULL DEFAULT 0,
    es_pare            BIT NOT NULL DEFAULT 0,
    permet_tutor       BIT NOT NULL DEFAULT 0,
    imposa_canvi_password BIT,
    usuari_bloquejat BIT,
    pare_id INT,
    mare_id INT,
    data_ultim_login DATETIME,
    ip_ultim_login VARCHAR(15),
 
    CONSTRAINT UsuariPK PRIMARY KEY (usuari_id),
    CONSTRAINT U_PareFK FOREIGN KEY (pare_id) REFERENCES USUARI(usuari_id),
    CONSTRAINT U_MareFK FOREIGN KEY (mare_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE UNIQUE INDEX U_NifIX ON USUARI (document);

CREATE TABLE PROFESSOR_UF
(
    /* PUF */
    professor_uf_id INT NOT NULL AUTO_INCREMENT,
    professor_id    INT NOT NULL,
    uf_id           INT NOT NULL,
    grups           VARCHAR(5),

    CONSTRAINT ProfessorUFPK PRIMARY KEY (professor_uf_id),
    CONSTRAINT PUF_UsuariFK FOREIGN KEY (professor_id) REFERENCES USUARI(usuari_id),
    CONSTRAINT PUF_UnitatFormativaFK FOREIGN KEY (uf_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE SISTEMA
(
    /* S */
	/* Ha de contenir un únic registre 	que conté la configuració */
	nom VARCHAR(100), /* Nom institut */ 
	any_academic_id INT NOT NULL,
	
    CONSTRAINT S_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE CURS
(
    /* C */
    curs_id INT NOT NULL AUTO_INCREMENT,
    any_academic_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    codi VARCHAR(10) NOT NULL,
    cicle_formatiu_id INT NOT NULL,
    nivell INT CHECK (nivell IN (1, 2)),
	avaluacio CHAR(3) NOT NULL DEFAULT 'ORD', /* ORD, EXT */
    trimestre INT NOT NULL DEFAULT 1, /* 1, 2, 3 */
	butlleti_visible BIT NOT NULL DEFAULT 0,
	finalitzat BIT NOT NULL DEFAULT 0,

    CONSTRAINT CursPK PRIMARY KEY (curs_id),
    CONSTRAINT C_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id),
    CONSTRAINT MAT_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE MATRICULA
(
    /* MAT */
    matricula_id INT NOT NULL AUTO_INCREMENT,
    curs_id INT NOT NULL, 
    alumne_id INT NOT NULL,
    grup CHAR(1) CHECK (grup IN ('A', 'B', 'C')),
    grup_tutoria VARCHAR(2),
    baixa BIT,

    CONSTRAINT MatriculaPK PRIMARY KEY (matricula_id),
    CONSTRAINT MAT_CursFK FOREIGN KEY (curs_id) REFERENCES CURS(curs_id),
    CONSTRAINT MAT_UsuariFK FOREIGN KEY (alumne_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    exempt BIT,
    convalidat BIT,
    junta BIT,
    baixa BIT, /* Baixa d'una UF */
    convocatoria INT, /* 0 (aprovat), 1, 2, 3, 4, 5 */

    CONSTRAINT NotesPK PRIMARY KEY (notes_id),
    CONSTRAINT N_MatriculaFK FOREIGN KEY (matricula_id) REFERENCES MATRICULA(matricula_id),
    CONSTRAINT N_UnitatFormativaFK FOREIGN KEY (uf_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EQUIP
(
    /* EQP */
    equip_id INT NOT NULL AUTO_INCREMENT,
    tipus CHAR(2), /* DP: Departament, EQ: Equip docent, CM: Comissió */
    nom VARCHAR(50) NOT NULL,
    cap INT NULL,

    CONSTRAINT EquipPK PRIMARY KEY (equip_id),
    CONSTRAINT EQP_CapFK FOREIGN KEY (cap) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PROFESSOR_EQUIP
(
    /* PEQ */
    professor_equip_id INT NOT NULL AUTO_INCREMENT,
    professor_id INT NOT NULL,
    equip_id INT NOT NULL,

    CONSTRAINT ProfessorEquipPK PRIMARY KEY (professor_equip_id),
    CONSTRAINT PEQ_UsuariFK FOREIGN KEY (professor_id) REFERENCES USUARI(usuari_id),
    CONSTRAINT PEQ_DepartamentFK FOREIGN KEY (equip_id) REFERENCES EQUIP(equip_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE FESTIU
(
    /* F */
    data DATE NOT NULL,
    motiu VARCHAR(50) NOT NULL,

    CONSTRAINT FestiuPK PRIMARY KEY (data)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE DIA_GUARDIA
(
    /* DG */
    dia INT NOT NULL, /* 1, 2, 3, 4, 5 */
    nom VARCHAR(10) NOT NULL, /* Dilluns, ... divendres */
    codi CHAR(2) NOT NULL, /* Dl, dm, dc, dj, dv, ds, dg */
    punter_data DATE,

    CONSTRAINT DiaGuardiaPK PRIMARY KEY (dia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE REGISTRE
(
    /* R */
    usuari_id INT NOT NULL,
    nom_usuari VARCHAR(100) NOT NULL, 
    data DATETIME NOT NULL,
    ip VARCHAR(15) NOT NULL,
    seccio VARCHAR(20) NOT NULL, 
    missatge VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE NOTES_MP
(
    /* NMP */
    notes_mp_id INT NOT NULL AUTO_INCREMENT,
    matricula_id INT NOT NULL, 
    modul_professional_id INT NOT NULL,
    nota INT CHECK (nota IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),

    CONSTRAINT NotesMPPK PRIMARY KEY (notes_mp_id),
    CONSTRAINT NMP_MatriculaFK FOREIGN KEY (matricula_id) REFERENCES MATRICULA(matricula_id),
    CONSTRAINT NMP_ModulProfessionalFK FOREIGN KEY (modul_professional_id) REFERENCES MODUL_PROFESSIONAL(modul_professional_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE VIEW CURS_ACTUAL AS
	SELECT C.* 
    FROM CURS C 
    LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id) 
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
        LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id)
        WHERE alumne_id=AlumneId AND cicle_formatiu_id IN (SELECT cicle_formatiu_id FROM CURS WHERE curs_id=CursId)
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
				WHERE matricula_id=MatriculaId AND uf_id=_uf_id;
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
            SELECT @MatriculaId, UF.unitat_formativa_id, 1 
            FROM UNITAT_FORMATIVA UF
            LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id)
            LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id)
            WHERE CF.cicle_formatiu_id=@CicleId
            AND UF.nivell<=@Nivell;
		CALL CopiaNotesAnteriorMatricula(AlumneId, @MatriculaId, @MatriculaAnteriorId);
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
