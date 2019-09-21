/*
Actualització de la DB a partir de la versió 0.7
*/

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
    DECLARE _exempt, _convalidat, _junta, _baixa BIT;
    DECLARE done INT DEFAULT FALSE;

    DROP TABLE IF EXISTS NotesTemp;
    CREATE TEMPORARY TABLE NotesTemp AS (SELECT * FROM NOTES WHERE matricula_id=MatriculaAnteriorId);

	BEGIN
		DECLARE curNotes CURSOR FOR SELECT uf_id, nota1, nota2, nota3, nota4, nota5, exempt, convalidat, junta, baixa, convocatoria FROM NotesTemp;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

		OPEN curNotes;

		read_loop: LOOP
			FETCH curNotes INTO _uf_id, _nota1, _nota2, _nota3, _nota4, _nota5, _exempt, _convalidat, _junta, _baixa, _convocatoria;
			IF done THEN
				LEAVE read_loop;
			END IF;
			UPDATE NOTES SET nota1=_nota1, nota2=_nota2, nota3=_nota3, nota4=_nota4, nota5=_nota5, exempt=_exempt, convalidat=_convalidat, junta=_junta, baixa=_baixa, convocatoria=_convocatoria 
				WHERE matricula_id=MatriculaId AND uf_id=_uf_id;
		END LOOP;

		CLOSE curNotes;
	END;
    DROP TABLE NotesTemp;
    
    UPDATE NOTES SET convocatoria=0 WHERE matricula_id=MatriculaId AND convocatoria<>0 AND UltimaNota(notes_id)>=5;
    UPDATE NOTES SET convocatoria=convocatoria+1 WHERE matricula_id=MatriculaId AND convocatoria<>0 AND UltimaNota(notes_id)<5;
END //
DELIMITER ;

DROP PROCEDURE CreaMatricula;

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
