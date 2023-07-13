
/* Comprovació hores mòduls */

SELECT MP.modul_professional_id, MP.nom, MP.hores, SUM(UF.hores), IF(MP.hores=SUM(UF.hores), 'Ok', 'ERROR') 
FROM UNITAT_FORMATIVA UF 
LEFT JOIN MODUL_PROFESSIONAL MP ON (UF.modul_professional_id=MP.modul_professional_id)
GROUP BY UF.modul_professional_id;

SELECT MPE.modul_pla_estudi_id, MPE.nom, MPE.hores, SUM(UPE.hores), IF(MPE.hores=SUM(UPE.hores), 'Ok', 'ERROR') 
FROM UNITAT_PLA_ESTUDI UPE 
LEFT JOIN MODUL_PLA_ESTUDI MPE ON (UPE.modul_pla_estudi_id=MPE.modul_pla_estudi_id)
GROUP BY UPE.modul_pla_estudi_id;


/* Correcció hores mòduls */

UPDATE MODUL_PROFESSIONAL MP SET MP.hores=(SELECT SUM(UF.hores) FROM UNITAT_FORMATIVA UF WHERE UF.modul_professional_id=MP.modul_professional_id);

UPDATE MODUL_PLA_ESTUDI MPE SET MPE.hores=IFNULL((SELECT SUM(UPE.hores) FROM UNITAT_PLA_ESTUDI UPE WHERE UPE.modul_pla_estudi_id=MPE.modul_pla_estudi_id), 0);


/* Correcció hores setmanals mòdul */

UPDATE MODUL_PROFESSIONAL SET hores_setmana=hores/33 WHERE es_fct=0 OR es_fct IS NULL;
UPDATE MODUL_PROFESSIONAL SET hores_setmana=NULL WHERE nom LIKE '%centres de treball%';

UPDATE MODUL_PLA_ESTUDI SET hores_setmana=hores/33 WHERE es_fct=0 OR es_fct IS NULL;
UPDATE MODUL_PLA_ESTUDI SET hores_setmana=NULL WHERE nom LIKE '%centres de treball%';


/* Comprovació hores unitats - pla d'estudis */

SELECT 
    CPE.codi, MPE.codi, 
    UPE.unitat_pla_estudi_id, UPE.codi, UPE.nom, UPE.hores,
    UF.unitat_formativa_id, UF.codi, UF.nom, UF.hores, 
    IF(UPE.hores=UF.hores, '', 'Error') AS Missatge
FROM UNITAT_PLA_ESTUDI UPE
LEFT JOIN UNITAT_FORMATIVA UF  ON (UPE.unitat_formativa_id=UF.unitat_formativa_id)
LEFT JOIN MODUL_PLA_ESTUDI MPE ON (UPE.modul_pla_estudi_id=MPE.modul_pla_estudi_id)
LEFT JOIN CICLE_PLA_ESTUDI CPE ON (MPE.cicle_pla_estudi_id=CPE.cicle_pla_estudi_id)
WHERE CPE.any_academic_id=5
ORDER BY CPE.codi, MPE.codi, UPE.codi;

SELECT 
    CPE.codi,
    MPE.modul_pla_estudi_id, MPE.codi, MPE.nom, MPE.hores,
    MP.modul_professional_id, MP.codi, MP.nom, MP.hores, 
    IF(MPE.hores=MP.hores, '', 'Error') AS Missatge
FROM MODUL_PLA_ESTUDI MPE
LEFT JOIN MODUL_PROFESSIONAL MP ON (MPE.modul_professional_id=MP.modul_professional_id)
LEFT JOIN CICLE_PLA_ESTUDI CPE ON (MPE.cicle_pla_estudi_id=CPE.cicle_pla_estudi_id)
WHERE CPE.any_academic_id=5
ORDER BY CPE.codi, MPE.codi;
