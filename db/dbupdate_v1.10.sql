/*
Actualització de la DB a partir de la versió 1.10
*/

ALTER TABLE USUARI ADD es_administratiu BIT NOT NULL DEFAULT 0;
