/** 
 * Usuari.js
 *
 * Accions AJAX diverses.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */


/**
 * ActualitzaTaulaOrla
 * @param element Element que ha fet la crida.
 */
function ActualitzaTaulaOrla(element) { 
console.log('-> ActualitzaTaulaOrla');
	var sAnyAcademicId = document.getElementById('cmb_any_academic_id').value;	
	var sCicleFormatiuId = document.getElementById('cmb_cicle_formatiu_id').value;	
	var sNivell = document.getElementById('cmb_nivell').value;	
	var sGrup = document.getElementById('cmb_grup').value;	
//console.log('sAnyAcademicId: ' + sAnyAcademicId);	
//console.log('sCicleFormatiuId: ' + sCicleFormatiuId);	
//console.log('sNivell: ' + sNivell);	
//console.log('sGrup: ' + sGrup);	

	$.ajax( {
		type: 'POST',
		url: 'lib/LibUsuari.ajax.php',
		data:{
			'accio': 'ActualitzaTaulaOrla',
			'any_academic_id': sAnyAcademicId,
			'cicle_formatiu_id': sCicleFormatiuId,
			'nivell': sNivell,
			'grup': sGrup
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

