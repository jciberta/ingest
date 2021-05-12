/** 
 * PlaEstudis.js
 *
 * Accions AJAX per al pla d'estudis.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * ActualitzaTaulaPlaEstudisAny
 * @param element Element que ha fet la crida.
 */
function ActualitzaTaulaPlaEstudisAny(element) { 
console.log('-> ActualitzaTaulaPlaEstudisAny');
	var sAnyAcademicId = document.getElementById('cmb_any_academic_id').value;	
console.log('sAnyAcademicId: ' + sAnyAcademicId);	

	$.ajax( {
		type: 'POST',
		url: 'lib/LibPlaEstudis.ajax.php',
		data:{
			'accio': 'ActualitzaTaulaAny',
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
 * ActualitzaTaulaPlaEstudisCicle
 * @param element Element que ha fet la crida.
 */
function ActualitzaTaulaPlaEstudisCicle(element) { 
console.log('-> ActualitzaTaulaPlaEstudisCicle');
	var sCicleFormatiuId = document.getElementById('cmb_cicle_formatiu_id').value;	
console.log('sCicleFormatiuId: ' + sCicleFormatiuId);	

	$.ajax( {
		type: 'POST',
		url: 'lib/LibPlaEstudis.ajax.php',
		data:{
			'accio': 'ActualitzaTaulaCicle',
			'cicle_formatiu_id': sCicleFormatiuId
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
