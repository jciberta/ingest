/** 
 * ProgramacioDidactica.js
 *
 * Accions AJAX per a la programació didàctica.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * ActualitzaTaulaResultatsAprenentatge
 * @param element Element que ha fet la crida.
 */
function ActualitzaTaulaResultatsAprenentatge(element) { 
console.log('-> ActualitzaTaulaResultatsAprenentatge');
	var sCicleFormatiuId = document.getElementById('cmb_cicle_formatiu_id').value;	
console.log('sCicleFormatiuId: ' + sCicleFormatiuId);	

	$.ajax( {
		type: 'POST',
		url: 'lib/LibProgramacioDidactica.ajax.php',
		data:{
			'accio': 'ActualitzaTaulaResultatsAprenentatge',
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

