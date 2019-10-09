/*
Actualització de la DB a partir de la versió 0.11
*/

DROP PROCEDURE CopiaNotesAnteriorMatricula;

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
        WHERE matricula_id=MatriculaId AND convocatoria<>0 AND UltimaNota(notes_id)<5 AND UltimaNota(notes_id)!=-1;
END //
DELIMITER ;
