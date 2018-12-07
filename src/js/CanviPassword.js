/** 
 * CanviPassword.js
 *
 * Canvi de contrasenya de l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * ComprovaCamps
 *
 * Comprova els camps del canvi de contrasenya per la part client.
 */
function ComprovaCamps() { 
    if (document.CanviPassword.contrasenya1.value != document.CanviPassword.contrasenya2.value) {
        alert("Les contrasenyes són diferents.");
        return false;
    } else {
        return true;
    }
}
