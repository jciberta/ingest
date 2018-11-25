/** 
 * Professor.js
 *
 * Accions AJAX diverses.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */


/**
 * AssignaUF
 *
 * Donat un checkbox, assigna o desassigna una UF a un professor.
 * Bàsicament crea o esborra un registre a la taula PROFESSOR_UF.
 *
 * @param element Checkbox que ha fet la crida.
 */
function AssignaUF(element) { 
    $.ajax( {
        type: 'POST',
        url: 'AccionsAJAX.php',
        data:{
			'accio': 'AssignaUF',
            'nom': element.name,
            'check': element.checked
            },
        success: function(data) {
            $('#debug').html(data);
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error.');
		}
    } );
}
