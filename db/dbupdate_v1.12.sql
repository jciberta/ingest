/*
Actualització de la DB a partir de la versió 1.12
*/

DELIMITER //
CREATE DEFINER=`root`@`localhost` TRIGGER `AU_ActualitzaHoresMPE` AFTER UPDATE ON `UNITAT_PLA_ESTUDI` FOR EACH ROW BEGIN
IF OLD.hores <> new.hores THEN
 SET @new_hores = NEW.hores;
 SET @old_hores = OLD.hores;
 SET @old_modul_pla_estudi_id = OLD.modul_pla_estudi_id;
 SET @old_unitat_pla_estudi_id = OLD.unitat_pla_estudi_id;
 UPDATE `MODUL_PLA_ESTUDI` SET `hores` = (SELECT SUM(hores) FROM UNITAT_PLA_ESTUDI WHERE modul_pla_estudi_id = @old_modul_pla_estudi_id)  WHERE (`modul_pla_estudi_id` = @old_modul_pla_estudi_id);
 INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge)
	VALUES (1,'Taula UNITAT_PLA_ESTUDI',NOW(),'127.0.0.1','Trigger', CONCAT('AU_ActualitzaHoresMPE RegistreId:',@old_unitat_pla_estudi_id,' Valor:',@new_hores,'->',@old_hores));
END IF;
END //
DELIMITER ;