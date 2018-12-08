/** 
 * Matricula.js
 *
 * Accions AJAX diverses.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */


/**
 * MatriculaUF
 *
 * Donat un checkbox, matricula o desmatricula una UF.
 * Bàsicament posa el camp baixa de NOTES al valor que li toca. Conserva les notes que hi havia.
 *
 * @param element Checkbox que ha fet la crida.
 */
function MatriculaUF(element) { 
    $.ajax( {
        type: 'POST',
        url: 'AccionsAJAX.php',
        data:{
			'accio': 'MatriculaUF',
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

