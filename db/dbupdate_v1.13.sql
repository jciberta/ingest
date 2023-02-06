/*
Actualització de la DB a partir de la versió 1.13
*/

ALTER TABLE NOTES ADD comentari_trimestre1 VARCHAR(100);
ALTER TABLE NOTES ADD comentari_trimestre2 VARCHAR(100);
ALTER TABLE NOTES ADD comentari_trimestre3 VARCHAR(100);
ALTER TABLE NOTES ADD comentari_ordinaria VARCHAR(100);
ALTER TABLE NOTES ADD comentari_extraordinaria VARCHAR(100);
ALTER TABLE NOTES ADD comentari_matricula_seguent VARCHAR(100);

ALTER TABLE SISTEMA ADD capcalera_login VARCHAR(1000);
ALTER TABLE SISTEMA ADD peu_login VARCHAR(1000);


UPDATE SISTEMA SET versio_db='1.14';
