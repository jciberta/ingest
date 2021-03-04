/** 
 * Avaluacio.js
 *
 * Accions AJAX per a l'avaluació.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Retorna el text de l'estat del curs.
 * @param obj Objecte que ha provocat la crida.
 * @param curs_id Identificador del curs.
 * @param estat Identificador del curs.
 */
function TextEstat(sEstat) {
	switch (sEstat) {
		case "A":
			return 'Actiu';
			break;
		case "J":
			return 'Junta';
			break;
		case "I":
			return 'Inactiu';
			break;
		case "O":
			return 'Obert';
			break;
		case "T":
			return 'Tancat';
			break;
		default:
			return '';
	}
}

/**
 * Deshabilita els botons d'estat.
 */
function DeshabilitaBotons() {
	var btnActiu = document.getElementById('btn_A');
	var btnJunta = document.getElementById('btn_J');
	var btnInactiu = document.getElementById('btn_I');
	var btnObert = document.getElementById('btn_O');
	var spanEstat = document.getElementById('estat');
	btnActiu.disabled = false;
	btnJunta.disabled = false;
	btnInactiu.disabled = false;
	btnObert.disabled = false;
}

/**
 * PosaEstatCurs
 * Posa el curs a un estat determinat.
 * @param obj Objecte que ha provocat la crida.
 * @param curs_id Identificador del curs.
 * @param estat Identificador del curs.
 */
function PosaEstatCurs(obj, curs_id, estat) {
console.log('->PosaEstatCurs');

	//var sCerca = $('input[name="edtRecerca"]').val();	
	var btn = document.getElementById('btn_'+estat);
	DeshabilitaBotons();
/*	var btnActiu = document.getElementById('btn_A');
	var btnJunta = document.getElementById('btn_J');
	var btnInactiu = document.getElementById('btn_I');
	var btnObert = document.getElementById('btn_O');
	var spanEstat = document.getElementById('estat');
	btnActiu.disabled = false;
	btnJunta.disabled = false;
	btnInactiu.disabled = false;
	btnObert.disabled = false;*/

    $.ajax( {
        type: 'POST',
        url: 'lib/LibAvaluacio.ajax.php',
        data:{
			'accio': 'PosaEstatCurs',
            'nom': obj.name,
            'curs_id': curs_id,
            'estat': estat
            },
        success: function(data) {
            $('#debug').html(data);
			btn.disabled = true;
			spanEstat.value = "Estat: " + TextEstat(estat);
			//chb.checked = !chb.checked;
//console.dir(obj);
			//if (chb.checked)
			//	obj.innerHTML = 'Amaga butlletins'
			//else
			//	obj.innerHTML = 'Mostra butlletins';
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

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
	bootbox.confirm({
		message: "Esteu segur que voleu tancar l'avaluació?<br>Aquesta acció <B>no</B> es pot desfer.",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Tanca',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if (result) {
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
						DeshabilitaBotons();
						btn_A.disabled = true;
					}, 
					error: function (data) {
						$('#MissatgeError').html("L'avaluació no s'ha tancat correctament.");
						$('#MissatgeError').show();
						$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
					}
				} );
			}
		}
	});	
}

/**
 * TancaCurs
 * Tanca el curs.
 * @param obj Objecte que ha provocat la crida.
 * @param curs_id Identificador del curs.
 */
function TancaCurs(obj, curs_id) {
console.log('TancaCurs');
	bootbox.confirm({
		message: "Esteu segur que voleu tanca el curs?<br>Aquesta acció <B>no</B> es pot desfer.",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Tanca',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if (result) {
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
		}
	});	
}