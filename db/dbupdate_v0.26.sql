/*
Actualització de la DB a partir de la versió 0.26
*/

ALTER TABLE USUARI ADD data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE USUARI ADD data_modificacio DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
