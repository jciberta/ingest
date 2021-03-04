/*
Actualització de la DB a partir de la versió 0.27
*/

ALTER TABLE CURS ADD estat char(1) NOT NULL DEFAULT 'A';
ALTER TABLE CURS DROP COLUMN butlleti_visible;
ALTER TABLE CURS DROP COLUMN finalitzat;

