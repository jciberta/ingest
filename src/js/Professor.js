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
        url: 'lib/LibProfessor.ajax.php',
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

/**
 * ActualitzaTaulaProfessorsUF
 * @param element Element que ha fet la crida.
 */
function ActualitzaTaulaProfessorsUF(element) { 
console.log('-> ActualitzaTaulaProfessorsUF');
	var sAnyAcademicId = document.getElementById('cmb_any_academic_id').value;	
console.log('sAnyAcademicId: ' + sAnyAcademicId);	

	$.ajax( {
		type: 'POST',
		url: 'lib/LibProfessor.ajax.php',
		data:{
			'accio': 'ActualitzaTaulaProfessorsUF',
			'any_academic_id': sAnyAcademicId
		},
        success: function(data) {
            $('#taula').html(data);
            //$('#debug').html('<textarea disabled>'+data+'</textarea>');
        }, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

/**
 * ActualitzaTaulaProfessorsAssignacioUF
 * @param element Element que ha fet la crida.
 */
function ActualitzaTaulaProfessorsAssignacioUF(element) { 
console.log('-> ActualitzaTaulaProfessorsAssignacioUF');
	var sProfessorId = document.getElementById('hdn_professor_id').value;	
	var sAnyAcademicId = document.getElementById('cmb_any_academic_id').value;	
console.log('sAnyAcademicId: ' + sAnyAcademicId);	

	$.ajax( {
		type: 'POST',
		url: 'lib/LibProfessor.ajax.php',
		data:{
			'accio': 'ActualitzaTaulaProfessorsAssignacioUF',
			'professor_id': sProfessorId,
			'any_academic_id': sAnyAcademicId
		},
        success: function(data) {
            $('#taula').html(data);
            //$('#debug').html('<textarea disabled>'+data+'</textarea>');
        }, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

/**
 * ActualitzaTaulaGrupProfessorsAssignacioUF
 * @param element Element que ha fet la crida.
 */
function ActualitzaTaulaGrupProfessorsAssignacioUF(element) { 
console.log('-> ActualitzaTaulaGrupProfessorsAssignacioUF');
	var sAnyAcademicId = document.getElementById('cmb_any_academic_id').value;	
	var sCodiCiclePlaEstudi = document.getElementById('cmb_CPE.codi').value;	
console.log('sAnyAcademicId: ' + sAnyAcademicId);	
console.log('sCodiCiclePlaEstudi: ' + sCodiCiclePlaEstudi);	

	$.ajax( {
		type: 'POST',
		url: 'lib/LibProfessor.ajax.php',
		data:{
			'accio': 'ActualitzaTaulaGrupProfessorsAssignacioUF',
			'codi_cicle_pla_estudi': sCodiCiclePlaEstudi,
			'any_academic_id': sAnyAcademicId
		},
        success: function(data) {
            $('#taula').html(data);
            //$('#debug').html('<textarea disabled>'+data+'</textarea>');
        }, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}
