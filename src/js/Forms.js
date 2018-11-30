/** 
 * Forms.js
 *
 * Accions JavaScript de suport a la llibreria de formularis.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 * @version 1.0
 */

/**
 * ActualitzaTaula
 *
 * @param element Botó que ha fet la crida.
 */
function ActualitzaTaula(element) { 
	var sCerca = $('input[name="edtRecerca"]').val();	
	var sSQL = $('input[name="edtSQL"]').val();	
	var sCamps = $('input[name="edtCamps"]').val();	
	var sDescripcions = $('input[name="edtDescripcions"]').val();	
//console.dir(sCerca);
//console.dir(sSQL);
//console.dir(sCamps);
console.dir(sDescripcions);

	$.ajax( {
		type: 'POST',
		url: 'lib/LibFormsAJAX.php',
		data:{
			'accio': 'ActualitzaTaula',
			'cerca': sCerca,
			'sql': sSQL,
			'camps': sCamps,
			'descripcions': sDescripcions
		},
        success: function(data) {
            $('#taula').html(data);
        }, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

/**
 * RecercaKeyPress
 *
 * Funció que comprova si s'ha premut la tecla ENTER per apretar el botó de cercar.
 *
 * @param event Event que ha provocat la crida.
 */
function RecercaKeyPress(event) {
	if (event.keyCode === 13) {
		// Cancel the default action, if needed
		event.preventDefault();
		$('#btnRecerca').click();
	}
}

/**
 * GetFormDataJSON
 *
 * Funció que retorna els elements d'un formulari en format JSON.
 *
 * @param oForm Formulari del que es vol obtenir les dades.
 * @return string Elements del formulari en format JSON.
 */
function GetFormDataJSON(oForm) {
//console.dir('oForm: ' + oForm);
//console.dir('elements: ' + oForm.elements);
	var controls = oForm.elements;
	var msg = "[";
	for (var i=0, iLen=controls.length; i<iLen; i++) {
		msg += '{"name":"' + controls[i].name + '","value":"' + controls[i].value + '"},';
    }
	msg = msg.slice(0, -1); // Treiem la darrera coma
	msg += ']';
	return msg;
}

/**
 * DesaFitxa
 *
 * @param element Botó que desa la fitxa.
 */
function DesaFitxa(element) { 
//console.dir('element: ' + element);
	var frm = document.getElementById('frmFitxa');
console.dir('frm: ' + frm);
	var jsonForm = GetFormDataJSON(frm);
console.dir('jsonForm: ' + jsonForm);
//	var jsonForm2 = JSON.stringify($('#frmFitxa').serializeArray());
//console.dir('jsonForm2: ' + jsonForm2);


	$.ajax( {
		type: 'POST',
		url: 'lib/LibFormsAJAX.php',
		data:{
			'accio': 'DesaFitxa',
			'form': jsonForm
		},
        success: function(data) {
			$('#btnDesa').hide();
			$('#MissatgeCorrecte').show();
            $('#debug').html(data);
        }, 
		error: function(data) {
			$('#MissatgeError').show();
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}
