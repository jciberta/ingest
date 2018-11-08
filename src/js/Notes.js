/** 
 * Notes.js
 *
 * Accions AJAX per a les notes.
 */


/**
 * MatriculaUF
 *
 * Donat un checkbox, matricula o desmatricula una UF.
 * Bàsicament posa el camp baixa de NOTES al valor que li toca. Conserva les notes que hi havia.
 *
 * @param element Checkbox que ha fet la crida.
 */
function ActualitzaNota(element) { 
	$('#debug').html('Executant ActualitzaNota...');
//console.log(element.value);
//console.dir(element);
    $.ajax( {
        type: 'POST',
        url: 'AccionsAJAX.php',
        data:{
			'accio': 'ActualitzaNota',
            'nom': element.name,
            'valor': element.value
            },
        success: function(data) {
            $('#debug').html(data);
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error.');
		}
    } );
}
