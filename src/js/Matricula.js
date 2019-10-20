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
        url: 'lib/LibMatricula.ajax.php',
        data:{
			'accio': 'MatriculaUF',
            'nom': element.name,
            'check': element.checked
            },
        success: function(data) {
            $('#debug').html(data);
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
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
					url: 'lib/LibMatricula.ajax.php',
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
						$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
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
 * Baixa de la matrícula d'un alumne.
 * @param element Checkbox que ha fet la crida.
 * @param matricula_id Identificador de la matrícula.
 */
function BaixaMatricula(element, matricula_id) { 
	var sCerca = $('input[name="edtRecerca"]').val();	
	var frm = document.getElementById('frm');
	var sFrm = frm.value;	
	bootbox.confirm({
	//	title: "Suprimeix",
		message: "Esteu segur que voleu donar de baixa l'alumne?",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Dona de baixa',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if (result) {
				$.ajax( {
					type: 'POST',
					url: 'lib/LibUsuari.ajax.php',
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
						$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
					}
				} );
			}
			else 
				element.checked = false;
		}
	});	
}

/**
 * EliminaMatriculaCurs
 * Elimina totes les matrícules d'un curs.
 * @param curs_id Identificador del curs.
 */
function EliminaMatriculaCurs(curs_id) { 
console.log('EliminaMatriculaCurs '+curs_id);
	bootbox.confirm({
		message: "Esteu segur que voleu eliminar totes les matrícules d'aquest curs?",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Elimina totes les matrícules',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if (result) {
				$.ajax( {
					type: 'POST',
					url: 'lib/LibMatricula.ajax.php',
					data:{
						'accio': 'EliminaMatriculaCurs',
						'id': curs_id
						},
					success: function(data) {
						$('#debug').html(data);
					}, 
					error: function (data) {
						$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
					}
				} );
			}
		}
	});	
}

/**
 * EliminaMatriculaAlumne
 * Elimina la matrícula d'un alumne.
 * @param matricula_id Identificador de la matrícula.
 */
function EliminaMatriculaAlumne(matricula_id) { 
console.log('EliminaMatriculaAlumne '+matricula_id);
	bootbox.confirm({
		message: "Esteu segur que voleu eliminar la matrícula d'aquest alumne?",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Elimina la matrícula',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if (result) {
				$.ajax( {
					type: 'POST',
					url: 'lib/LibMatricula.ajax.php',
					data:{
						'accio': 'EliminaMatriculaAlumne',
						'id': matricula_id
						},
					success: function(data) {
						$('#debug').html(data);
					}, 
					error: function (data) {
						$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
					}
				} );
			}
		}
	});	
}

/**
 * BloquejaUsuari
 * Bloqueja un usuari (no pot fer login).
 * @param element Checkbox que ha fet la crida.
 * @param usuari_id Identificador de l'usuari.
 */
function BloquejaUsuari(element, usuari_id) { 
console.log('BloquejaUsuari');
	var sCerca = $('input[name="edtRecerca"]').val();	
	var filtre = document.getElementById('filtre');
	var sFiltre = CreaFiltreJSON(filtre);
	var frm = document.getElementById('frm');
	var sFrm = frm.value;	
    $.ajax( {
        type: 'POST',
        url: 'lib/LibUsuari.ajax.php',
        data:{
			'accio': 'BloquejaUsuari',
            'id': usuari_id,
			'check': element.checked,
			'cerca': sCerca,
			'filtre': sFiltre,
			'frm': sFrm
            },
        success: function(data) {
            $('#taula').html(data);
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

function AssignaGrup(element, grup) { 
	var data = (element.name).split('_');
	var CursId = data[1];
	var AlumneId = data[2];
    $.ajax( {
        type: 'POST',
        url: 'lib/LibUsuari.ajax.php',
        data:{
			'accio': 'AssignaGrup',
            'curs': CursId,
			'alumne': AlumneId,
			'grup': element.value
            },
        success: function(data) {
            $('#debug').html(data);
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

function AssignaGrupTutoria(element, grup_tutoria) { 
	var data = (element.name).split('_');
	var CursId = data[1];
	var AlumneId = data[2];
    $.ajax( {
        type: 'POST',
        url: 'lib/LibUsuari.ajax.php',
        data:{
			'accio': 'AssignaGrupTutoria',
            'curs': CursId,
			'alumne': AlumneId,
			'grup_tutoria': element.value
            },
        success: function(data) {
            $('#debug').html(data);
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}
