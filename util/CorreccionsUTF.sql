
/* Correcció de text UTF */

UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, concat(char(195,131 using utf8mb4), char(194,160 using utf8mb4)), 'à');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã€', 'À');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã¨', 'è');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã©', 'é');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'â€™', '´');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã§', 'ç');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã­', 'í');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã¯', 'ï');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã²', 'ò');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã³', 'ó');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã’', 'Ò');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã“', 'Ó');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ãº', 'ú');
UPDATE MODUL_PLA_ESTUDI SET metodologia = REPLACE(metodologia, 'Ã¼', 'ü');

UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, concat(char(195,131 using utf8mb4), char(194,160 using utf8mb4)), 'à');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã€', 'À');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã¨', 'è');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã©', 'é');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'â€™', '´');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã§', 'ç');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã­', 'í');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã¯', 'ï');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã³', 'ó');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã²', 'ò');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã’', 'Ò');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã“', 'Ó');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ãº', 'ú');
UPDATE MODUL_PLA_ESTUDI SET criteris_avaluacio = REPLACE(criteris_avaluacio, 'Ã¼', 'ü');

UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, concat(char(195,131 using utf8mb4), char(194,160 using utf8mb4)), 'à');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã€', 'À');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã¨', 'è');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã©', 'é');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'â€™', '´');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã§', 'ç');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã­', 'í');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã¯', 'ï');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã²', 'ò');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã³', 'ó');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã’', 'Ò');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã“', 'Ó');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ãº', 'ú');
UPDATE MODUL_PLA_ESTUDI SET recursos = REPLACE(recursos, 'Ã¼', 'ü');

UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, concat(char(195,131 using utf8mb4), char(194,160 using utf8mb4)), 'à');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã€', 'À');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã¨', 'è');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã©', 'é');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'â€™', '´');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã§', 'ç');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã­', 'í');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã¯', 'ï');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã²', 'ò');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã³', 'ó');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã’', 'Ò');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã“', 'Ó');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ãº', 'ú');
UPDATE MODUL_PLA_ESTUDI SET planificacio = REPLACE(planificacio, 'Ã¼', 'ü');


/* Altres correccions */

DELIMITER //
CREATE PROCEDURE CorreccioCSSModulPlaEstudi (IN PropietatCSS VARCHAR(50))
BEGIN
    CALL SuprimeixPropietatCSS('MODUL_PLA_ESTUDI', 'metodologia', PropietatCSS);
    CALL SuprimeixPropietatCSS('MODUL_PLA_ESTUDI', 'criteris_avaluacio', PropietatCSS);
    CALL SuprimeixPropietatCSS('MODUL_PLA_ESTUDI', 'recursos', PropietatCSS);
    CALL SuprimeixPropietatCSS('MODUL_PLA_ESTUDI', 'planificacio', PropietatCSS);
END //
DELIMITER ;

/* Propietat que no haurien de fer-se servir, per homogeneïtzar el format de les programacions */ 
CALL CorreccioCSSModulPlaEstudi('font-family:');
CALL CorreccioCSSModulPlaEstudi('font-size:');
CALL CorreccioCSSModulPlaEstudi('margin-left:');
CALL CorreccioCSSModulPlaEstudi('margin-right:');
CALL CorreccioCSSModulPlaEstudi('margin-top:');
CALL CorreccioCSSModulPlaEstudi('margin-bottom:');
CALL CorreccioCSSModulPlaEstudi('line-height:');
CALL CorreccioCSSModulPlaEstudi('text-align:');
CALL CorreccioCSSModulPlaEstudi('vertical-align:');
CALL CorreccioCSSModulPlaEstudi('color:');
CALL CorreccioCSSModulPlaEstudi('background-color:');
CALL CorreccioCSSModulPlaEstudi('font-weight:');
CALL CorreccioCSSModulPlaEstudi('font-style:');
CALL CorreccioCSSModulPlaEstudi('font-variant:');
CALL CorreccioCSSModulPlaEstudi('text-decoration:');
CALL CorreccioCSSModulPlaEstudi('text-transform:');
CALL CorreccioCSSModulPlaEstudi('white-space:');
CALL CorreccioCSSModulPlaEstudi('word-spacing:');
CALL CorreccioCSSModulPlaEstudi('letter-spacing:');
CALL CorreccioCSSModulPlaEstudi('text-indent:');
CALL CorreccioCSSModulPlaEstudi('float:');
