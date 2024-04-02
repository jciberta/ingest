/*
 * Actualització de la DB a partir de la versió 1.20
 */

ALTER TABLE UNITAT_PLA_ESTUDI ADD hores_fetes INT;

ALTER TABLE MATRICULA ADD beca BIT DEFAULT 0;

DELIMITER //
CREATE TRIGGER AU_CalculaNotaMitjanaCurs AFTER UPDATE ON CURS FOR EACH ROW
BEGIN
    IF OLD.estat = 'A' AND NEW.estat = 'J' THEN
        /* Quan es passi d’actiu a junta, aquelles mitjanes de mòdul no calculades es faran de forma automàtica. */
        CALL CalculaNotaMitjanaCurs(OLD.curs_id);
        INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge)
            VALUES (1, 'Taula CURS', now(), '127.0.0.1', 'Trigger', CONCAT('Càlcul mitjanes de mòdul. curs_id=', OLD.curs_id));
    END IF;
END //
DELIMITER ;

DROP FUNCTION PercentatgeAprovat;

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
    WHERE N.matricula_id=MatriculaId AND UPE.es_uf_addicional = 0;
    
    RETURN Percentatge;  	
	
END //
DELIMITER ;

/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.21';
