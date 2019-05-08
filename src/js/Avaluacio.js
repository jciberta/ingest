/** 
/** 
 * Avaluacio.js
 *
 * Accions AJAX per a l'avaluació.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * MostraButlletins
 * Mostra/Oculta els butlletins de notes.
 * @param obj Objecte que ha provocat la crida.
 * @param curs_id Identificador del curs.
 */
function MostraButlletins(obj, curs_id) {
console.log('MostraButlletins');

	//var sCerca = $('input[name="edtRecerca"]').val();	
	var chb = document.getElementById('chb_butlleti_visible');

    $.ajax( {
        type: 'POST',
        url: 'lib/LibAvaluacio.ajax.php',
        data:{
			'accio': 'MostraButlletins',
            'nom': obj.name,
            'curs_id': curs_id,
            'check': chb.checked
            },
        success: function(data) {
            $('#debug').html(data);
			chb.checked = !chb.checked;
//console.dir(obj);
			if (chb.checked)
				obj.innerHTML = 'Amaga butlletins'
			else
				obj.innerHTML = 'Mostra butlletins';
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

/**
 * TancaAvaluacio
 * Tanca l'avaluació ordinària.
 * @param obj Objecte que ha provocat la crida.
 * @param curs_id Identificador del curs.
 */
function TancaAvaluacio(obj, curs_id) {
console.log('TancaAvaluacio');
    $.ajax( {
        type: 'POST',
        url: 'lib/LibAvaluacio.ajax.php',
        data:{
			'accio': 'TancaAvaluacio',
            'curs_id': curs_id
            },
        success: function(data) {
            $('#MissatgeCorrecte').html("L'avaluació s'ha tancat correctament.");
            $('#MissatgeCorrecte').show();
            $('#div_TancaAvaluacio').hide();
            $('#div_TancaCurs').show();
            $('#taula').html(data);
        }, 
		error: function (data) {
            $('#MissatgeError').html("L'avaluació no s'ha tancat correctament.");
            $('#MissatgeError').show();
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

/**
 * TancaCurs
 * Tanca el curs.
 * @param obj Objecte que ha provocat la crida.
 * @param curs_id Identificador del curs.
 */
function TancaCurs(obj, curs_id) {
console.log('TancaCurs');
    $.ajax( {
        type: 'POST',
        url: 'lib/LibAvaluacio.ajax.php',
        data:{
			'accio': 'TancaCurs',
            'curs_id': curs_id
            },
        success: function(data) {
            $('#MissatgeCorrecte').html("El curs s'ha tancat correctament.");
            $('#MissatgeCorrecte').show();
            $('#div_MostraButlletins').hide();
            $('#div_TancaAvaluacio').hide();
            $('#div_TancaCurs').hide();
            $('#taula').html(data);
            $('#botons').html('No es permeten accions.');
        }, 
		error: function (data) {
            $('#MissatgeError').html("El curs no s'ha tancat correctament.");
            $('#MissatgeError').show();
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}