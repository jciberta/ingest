/** 
 * ConsolaSQL.js
 *
 * Accions AJAX per a la consola SQL.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */


/**
 * ExecutaSQL
 * @param element Element que ha fet la crida.
 */
function ExecutaSQL(element) { 
console.log('-> ExecutaSQL');
	var AreaText = document.getElementById('AreaText');
console.log('  SQL: ' + AreaText.value);
    $.ajax( {
        type: 'POST',
        url: 'lib/LibConsolaSQL.ajax.php',
        data:{
			'accio': 'ExecutaSQL',
            'sql': AreaText.value
            },
        success: function(data) {
			$('#taula').html(data);
        }, 
		error: function (data) {
			$('#taula').html('Hi ha hagut un error.');
		}
    } );
}
