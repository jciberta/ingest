/*
 * Actualització de la DB a partir de la versió 1.16
 */

DROP PROCEDURE CreaMatricula;

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

CREATE TABLE DOCUMENT (
    document_id INT NOT NULL AUTO_INCREMENT,
    document VARCHAR(255) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    solicitant VARCHAR(50) NOT NULL,
    lliurament VARCHAR(50) NOT NULL,
    custodia VARCHAR(50) NOT NULL,
    observacions TEXT NOT NULL,
    filtre VARCHAR(15) NOT NULL,

    CONSTRAINT DocumentPK PRIMARY KEY (document_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP PROCEDURE CopiaNotesAnteriorMatricula;

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



/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.17';
