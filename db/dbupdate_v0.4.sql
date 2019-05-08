/*
Actualització de la DB a partir de la versió 0.4
*/

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
