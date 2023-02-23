/** 
 * Expedient.js
 *
 * Accions JavaScript per a l'expedient.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * EnSortirCellaComentari
 * @param element Element que ha fet la crida.
 */
function EnSortirCellaComentari(element) { 
    console.log('->EnSortirCellaComentari');
    var MatriculaId = (element.name).split('_')[1];
    console.log('MatriculaId: '+MatriculaId);
    
    //console.dir(element);
    $.ajax( {
        type: 'POST',
        url: 'lib/LibExpedient.ajax.php',
        data:{
            'accio': 'ActualitzaComentari',
            'MatriculaId': MatriculaId,
            'valor': element.value
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
 * EnSortirCasellaUF2n
 * @param element Element que ha fet la crida.
 */
function EnSortirCasellaUF2n(element) { 
    console.log('->EnSortirCasellaUF2n');
    var MatriculaId = (element.name).split('_')[1];
    var UFId = (element.name).split('_')[2];
    console.log('MatriculaId: '+MatriculaId);
    console.log('UFId: '+UFId);
    
    //console.dir(element);
    $.ajax( {
        type: 'POST',
        url: 'lib/LibExpedient.ajax.php',
        data:{
            'accio': 'ActualitzaCasellaUF2n',
            'MatriculaId': MatriculaId,
            'UFId': UFId,
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
