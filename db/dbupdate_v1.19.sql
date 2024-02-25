/*
 * Actualització de la DB a partir de la versió 1.19
 */

ALTER TABLE UNITAT_PLA_ESTUDI ADD es_uf_addicional BIT(1) DEFAULT 0;

ALTER TABLE DOCUMENT MODIFY solicitant CHAR(1); /* Tutor, Alumne */
ALTER TABLE DOCUMENT MODIFY lliurament CHAR(2); /* TUtor, Tutor Fct, Tutor Dual, SEcretaria, Cap Estudis, Coordinador Fp, Coordinador Dual */
ALTER TABLE DOCUMENT MODIFY custodia CHAR(2); /* TUtor, Tutor Fct, Tutor Dual, SEcretaria, Cap Estudis, Coordinador Fp, Coordinador Dual */

DROP FUNCTION FormataNomCognom1Cognom2;

/*
 * FormataNomCognom1Cognom2
 * Formata el nom d'una persona a l'estil NCC.
 * @param string Nom.
 * @param string Cognom1.
 * @param string Cognom2.
 * @return string Nom formatat NCC.
 */
DELIMITER //
CREATE FUNCTION FormataNomCognom1Cognom2(Nom VARCHAR(100), Cognom1 VARCHAR(100), Cognom2 VARCHAR(100))
RETURNS VARCHAR(255)
BEGIN 
    RETURN TRIM(CONCAT(nom, ' ', IFNULL(cognom1, ''), ' ', IFNULL(cognom2, '')));
END //
DELIMITER ;

DROP FUNCTION FormataCognom1Cognom2Nom;

/*
 * FormataCognom1Cognom2Nom
 * Formata el nom d'una persona a l'estil CC,N.
 * @param string Nom.
 * @param string Cognom1.
 * @param string Cognom2.
 * @return string Nom formatat CC,N.
 */
DELIMITER //
CREATE FUNCTION FormataCognom1Cognom2Nom(Nom VARCHAR(100), Cognom1 VARCHAR(100), Cognom2 VARCHAR(100))
RETURNS VARCHAR(255)
BEGIN 
    RETURN CONCAT(TRIM(CONCAT(IFNULL(cognom1, ''), ' ', IFNULL(cognom2, ''))), ', ', nom);
END //
DELIMITER ;

/*
 * ObteNotaConvocatoriaI0
 * Retorna la nota corresponent a la convocatòria actual, i també si està aprovada (convocatòria=0).
 * Ús:
 *   SELECT notes_id, ObteNotaConvocatoriaI0(nota1, nota2, nota3, nota4, nota5, convocatoria) FROM NOTES;
 * @param integer nota1 Nota de la 1a convocatòria.
 * @param integer nota2 Nota de la 2a convocatòria.
 * @param integer nota3 Nota de la 3a convocatòria.
 * @param integer nota4 Nota de la 4a convocatòria.
 * @param integer nota5 Nota de la 5a convocatòria.
 * @param integer convocatoria Convocatòria actual.
 * @return integer Nota corresponent a la convocatòria actual.
 */
DELIMITER //
CREATE FUNCTION ObteNotaConvocatoriaI0(nota1 INT, nota2 INT, nota3 INT, nota4 INT, nota5 INT, convocatoria INT)
RETURNS INT
BEGIN 
    DECLARE Nota INT;
    IF convocatoria = 5 THEN SET Nota = nota5;
    ELSEIF convocatoria = 4 THEN SET Nota = nota4;
    ELSEIF convocatoria = 3 THEN SET Nota = nota3;
    ELSEIF convocatoria = 2 THEN SET Nota = nota2;
    ELSEIF convocatoria = 1 THEN SET Nota = nota1;
    ELSEIF convocatoria = 0 THEN SET Nota = IFNULL(nota5, IFNULL(nota4, IFNULL(nota3, IFNULL(nota2, IFNULL(nota1, NULL)))));
    ELSE SET Nota = NULL;
    END IF;
    RETURN Nota;
END //
DELIMITER ;

/*
 * CalculaNotaMitjanaModul
 * Calcula la nota mitjana d'un mòdul per a una matrícula. Si la mitjana ja ha estat calculada, no es toca.
 * Càlcul de la nota mitjana de mòdul:
 *   1. Si hi ha alguna UF que no té nota, no es calcula.
 *   2. Si totes les UF estan aprovades, es fa la mitja ponderada amb les hores.
 *   3. Si totes les UF tenen notes i hi ha alguna suspesa, es fa la mitja ponderada amb les hores, i si aquesta és superior a 4, es queda un 4.
 * @param integer MatriculaId Identificador de la matrícula.
 * @param integer ModulPlaEstudiId Identificador del mòdul.
 */
DELIMITER //
CREATE PROCEDURE CalculaNotaMitjanaModul(IN MatriculaId INT, IN ModulPlaEstudiId INT)
BEGIN
    DECLARE HiHaNotaNulla INT DEFAULT 0;
    DECLARE HiHaNotaSuspesa INT DEFAULT 0;
    DECLARE NotaMitjana REAL DEFAULT 0;
    DECLARE NotaMitjanaEntera INT DEFAULT 0;
    DECLARE NotaMP INT DEFAULT 0;
    DECLARE HoresTotals INT DEFAULT 0;
    DECLARE _NotaConvocatoria INT;
    DECLARE _hores INT;
    DECLARE done INT DEFAULT FALSE;

    BEGIN
        /* Càlcul de la nota mitjana de mòdul */
        DECLARE cur CURSOR FOR
            SELECT ObteNotaConvocatoriaI0(nota1, nota2, nota3, nota4, nota5, convocatoria) AS NotaConvocatoria, UPE.hores
            FROM NOTES N
            LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=N.uf_id)
            LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
            WHERE N.matricula_id=MatriculaId AND MPE.modul_pla_estudi_id=ModulPlaEstudiId;        
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        OPEN cur;
        read_loop: LOOP
            FETCH cur INTO _NotaConvocatoria, _hores;
            IF done THEN
                LEAVE read_loop;
            END IF;
            SET HoresTotals = HoresTotals + _hores;
            IF _NotaConvocatoria IS NOT NULL THEN
                SET NotaMitjana = NotaMitjana + CONVERT(_NotaConvocatoria*_hores, FLOAT);
                IF _NotaConvocatoria<5 THEN
                    SET HiHaNotaSuspesa = 1;
                END IF;
            ELSE
                SET HiHaNotaNulla = 1;
            END IF;
        END LOOP;
        CLOSE cur;
        SET NotaMitjana = NotaMitjana / CONVERT(HoresTotals, FLOAT);
        SET NotaMitjanaEntera = ROUND(NotaMitjana);
        IF HiHaNotaSuspesa=1 THEN
            SET NotaMitjanaEntera = LEAST(NotaMitjanaEntera, 4);
        END IF;
        /* Actualitzem la nota si no és nul·la (NotaMitjanaEntera<>NULL) i a la taula NOTES_MP no hi ha posada una nota */
        SET NotaMP = (SELECT NMP.nota FROM NOTES_MP NMP
            WHERE NMP.matricula_id=MatriculaId AND modul_professional_id=(SELECT modul_professional_id FROM MODUL_PLA_ESTUDI WHERE modul_pla_estudi_id=ModulPlaEstudiId)
        );
        IF NotaMP IS NULL AND HiHaNotaNulla<>1 THEN
            INSERT INTO NOTES_MP (matricula_id, modul_professional_id, nota) VALUES (MatriculaId, ModulPlaEstudiId, NotaMitjanaEntera);
        END IF;
    END;
END //
DELIMITER ;

/*
 * CalculaNotaMitjanaMatricula
 * Calcula les notes mitjanes d'una matrícula. Ho fa mòdul a mòdul.
 * @param integer IdMatricula Identificador de la matrícula.
 */
DELIMITER //
CREATE PROCEDURE CalculaNotaMitjanaMatricula(IN IdMatricula INT)
BEGIN
    DECLARE _ModulId INT;
    DECLARE done INT DEFAULT FALSE;

    BEGIN
        DECLARE cur CURSOR FOR
		    SELECT DISTINCT(MPE.modul_pla_estudi_id)
		    FROM NOTES N
		    LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=N.uf_id)
		    LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
		    WHERE N.matricula_id=IdMatricula;        
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        OPEN cur;
        read_loop: LOOP
            FETCH cur INTO _ModulId;
            IF done THEN
                LEAVE read_loop;
            END IF;
		CALL CalculaNotaMitjanaModul(IdMatricula, _ModulId);
        END LOOP;
        CLOSE cur;
    END;
END //
DELIMITER ;

/*
 * CalculaNotaMitjanaCurs
 * Calcula les notes mitjanes de mòdul per a un curs. Ho fa matrícula per matrícula.
 * @param integer CursId Identificador del curs.
 */
DELIMITER //
CREATE PROCEDURE CalculaNotaMitjanaCurs(IN CursId INT)
BEGIN
    DECLARE _MatriculaId INT;
    DECLARE done INT DEFAULT FALSE;

    BEGIN
        DECLARE cur CURSOR FOR
			SELECT M.matricula_id FROM MATRICULA M
			WHERE curs_id = CursId;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        OPEN cur;
        read_loop: LOOP
            FETCH cur INTO _MatriculaId;
            IF done THEN
                LEAVE read_loop;
            END IF;
           CALL CalculaNotaMitjanaMatricula(_MatriculaId);
        END LOOP;
        CLOSE cur;
    END;
END //
DELIMITER ;


/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.20';
