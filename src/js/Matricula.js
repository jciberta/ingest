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

/**
 * ConvalidaUF
 *
 * Donat un checkbox, convalida una UF (només funciona en un sentit).
 * Posa el camp convalidat de NOTES a cert, posa una nota de 5 i el camp convocatòria a 0.
 *
 * @param element Checkbox que ha fet la crida.
 * @param alumne Id de l'alumne.
 */
function ConvalidaUF(element, alumne) { 
	bootbox.confirm({
	//	title: "Suprimeix",
		message: "Esteu segur que voleu convalidar la UF?",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Convalida',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if (result) {
				$.ajax( {
						type: 'POST',
						url: 'AccionsAJAX.php',
						data:{
							'accio': 'ConvalidaUF',
							'alumne': alumne,
							'nom': element.name
				//            'check': element.checked
							},
						success: function(data) {
							$('#debug').html(data);
						}, 
						error: function (data) {
							$('#debug').html('Hi ha hagut un error.');
						}
					} );
			}
			else 
				element.checked = false;
		}
	});	
}

/**
 * BaixaMatricula
 *
 * Baixa de la matrícula d'un alumne.
 *
 * @param matricula_id Identificador de la matrícula.
 */
function BaixaMatricula(matricula_id) { 
console.log('BaixaMatricula');
	var sCerca = $('input[name="edtRecerca"]').val();	
	var frm = document.getElementById('frm');
	var sFrm = frm.value;	
    $.ajax( {
        type: 'POST',
        url: 'lib/LibForms.ajax.php',
        data:{
			'accio': 'BaixaMatricula',
            'id': matricula_id,
			'cerca': sCerca,
			'frm': sFrm
            },
        success: function(data) {
            $('#taula').html(data);
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error.');
		}
    } );
}

