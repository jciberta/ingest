
/* Correcció CSS */

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
