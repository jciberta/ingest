<?php

/** 
 * LibArray.php
 *
 * Llibreria d'utilitats per als arrays.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

 /**
 * EliminaEnArray
 *
 * Elimina un element de l'array d'una determinada posició.
 *
 * @param array $Array Array del que s'ha d'eliminar un element.
 * @param integer $Index Índex de l'element a eliminar.
 */
function EliminaEnArray(&$Array, $Index)
{
	for ($i=$Index; $i<count($Array); $i++) {
		$Array[$i] = $Array[$i+1];
	}
	array_pop($Array);
}

/**
 * InsertaEnArray
 *
 * Inserta un element en un array a una determinada posició.
 *
 * @param array $Array Array on s'ha d'insertar un element.
 * @param mixed $Element Element a insertar.
 * @param integer $Index Posició a insertar l'element.
 */
function InsertaEnArray(& $Array, $Element, $Index)
{
	array_push($Array, $Element);
	for ($i=count($Array)-1; $i<$Index; $i--) {
		$Array[$i] = $Array[$i-1];
	}
	$Array[$Index+1] = $Element;
}

?>
