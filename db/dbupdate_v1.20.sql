/*
 * Actualització de la DB a partir de la versió 1.20
 */

ALTER TABLE UNITAT_PLA_ESTUDI ADD hores_fetes INT;

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



/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.21';
