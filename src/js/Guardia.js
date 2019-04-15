/** 
 * Guardia.js
 *
 * Accions JavaScript de suport a la llibreria de guàrdies.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * GeneraProperDia
 *
 * @param element Botó que ha fet la crida.
 * @param dia Dia sobre el qual s'ha de generar les següents guàrdies (1: dl, 2: dm, ...).
 * @param data Data de les guàrdies actuals.
 */
function GeneraProperDia(element, dia, data) { 
	// Agafem tots els <input> dins del <div>
	var divTaula = document.getElementById('taula');
	var gp = divTaula.querySelectorAll("input");
//console.log('Llista de INPUTS');
//console.dir(gp);
console.log('Data: ' + data + ', Dia: ' + dia);


	// Fem una llista dels professors que han fet guàrdia (marcats al checkbox)
	var sGuardies = "";
	for (i = 0; i < gp.length; i++) {
		var node = gp[i];
		if (node.checked)
			sGuardies += (node.name).replace("pg_", "") + ",";
	}
	sGuardies = sGuardies.slice(0, -1); // Treiem la darrera coma
console.log('sGuardies: ' + sGuardies);

	$.ajax( {
		type: 'POST',
		url: 'lib/LibGuardia.ajax.php',
		data: {
			'accio': 'GeneraProperDia',
			'dia': dia,
			'data': data,
			'guardies' : sGuardies
//			'cerca': sCerca,
//			'sql': sSQL,
//			'camps': sCamps,
//			'descripcions': sDescripcions,
//			'taula': jsonTaula
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
 * CanviaDia
 *
 * @param element Desplegable que ha fet la crida.
 */
function CanviaDia(element) { 
    var dia = element.value;
//console.log('Dia: ' + dia);
	$.ajax( {
		type: 'POST',
		url: 'lib/LibGuardia.ajax.php',
		data:{
			'accio': 'CanviaDia',
			'dia': dia
		},
        success: function(data) {
            $('#taula').html(data);
			$('#debug').html('Ok. Canviat a dia ' + dia);
        }, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}
