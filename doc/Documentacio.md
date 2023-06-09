# Guia d'estil

## Idioma

  * L'idioma de l'aplicació web, el codi i la documentació és el català.
  * Interacció:
    * Humà-màquina: 2a persona singular de l'imperatiu: obre, tanca, surt, desa, edita, ves, etc.
    * Màquina-humà: 2a persona plural del present: desitgeu, esteu segurs, comproveu, etc.
  * [Guia d'estil de SoftCatalà](https://www.softcatala.org/guia-estil-de-softcatala/tota-la-guia/).

## Documentació del codi

 * Nomenclatura de variables, funcions, classes, mètodes, atributs, etc. estil Pascal (totes les inicials en majúscules): NomDeLaVariable, NomDeLaFuncio, etc. No estil Java (nomDeLaVariable) 

### Classes

```
/**
 * Descripció de la classe.
 */
 ```

### Propietats

```
/**
 * Descripció de la propietat.
 * @var tipus
 */
 ```

### Mètodes

Cal documentar les línies que s'escauen.

```
/**
 * Descripció del mètode.
 * @param tipus $param1 Descripció del paràmetre 1.
 * @param tipus $param2 Descripció del paràmetre 2.
 * @return tipus Descripció del retorn.
 * @throws Excepció
 */
 ```

### Codi

Si s'escau, cal documentar el que ha de fer el codi, no el que fa.
