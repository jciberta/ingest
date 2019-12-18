<?php

/** 
 * Libsistema.php
 *
 * Llibreria d'utilitats del sistema.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */


/**
 * Esborra un directori recursivament.
 * https://stackoverflow.com/questions/1653771/how-do-i-remove-a-directory-that-is-not-empty
 * @param integer $dir Directori a esborrar.
 * @return bool Cert si ha anat tot bé.
 */
function EsborraDirectori($dir): bool {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!EsborraDirectori($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

/**
 * Dona format a un valor en bytes.
 * https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
 * @return string Valor formatat.
 */
function FormataBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

?>