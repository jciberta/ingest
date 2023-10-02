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




/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.17';
