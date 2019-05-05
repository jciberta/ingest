/** 
/** 
 * Notes.js
 *
 * Accions AJAX per a les notes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * MostraGraellaNotes
 *
 * Mostra/Oculta la graella de notes.
 *
 * @param obj Objecte que ha provocat la crida.
 * @param int nivell Nivell del curs.
 */
function MostraGraellaNotes(obj, nivell) {
	if (obj.checked)
		$("#notes"+nivell).show()
	else
		$("#notes"+nivell).hide();
}

/**
 * MostraBaixes
 *
 * Mostra/Oculta els alumnes que s'han donat de baixa.
 *
 * @param obj Objecte que ha provocat la crida.
 */
function MostraBaixes(obj) {
	// Seleccionem totes les baixes
	var tr = $('tr[name=Baixa1]')
	
	if (obj.checked)
		tr.show()
	else
		tr.hide();
}

/**
 * Introdueix una nota de recuperació
 * @param obj Objecte que ha provocat la crida (INPUT).
 */
function IntrodueixRecuperacio(obj) {
console.dir(obj[0]);
//alert(obj[0].value);


bootbox.prompt({
    title: "Introdueix la nota de recuperació",
    inputType: 'number',
    callback: function (result) {
        console.log(result);
		if (result>0 && result <=10) {
			ActualitzaNotaRecuperacio(obj[0], result);
		}
		
    }
});

}

/**
 * NotaKeyDown
 *
 * Funció per moure's per la graella.
 *
 * @param obj Objecte que ha provocat la crida.
 * @param event Event que ha provocat la crida.
 */
function NotaKeyDown(obj, event) {
	const TECLES_PERMESES = [
		KeyCode.KEY_TAB,
		KeyCode.KEY_BACK_SPACE,
		KeyCode.KEY_DELETE,	
		KeyCode.KEY_0, 
		KeyCode.KEY_1, 
		KeyCode.KEY_2, 
		KeyCode.KEY_3, 
		KeyCode.KEY_4, 
		KeyCode.KEY_5, 
		KeyCode.KEY_6, 
		KeyCode.KEY_7, 
		KeyCode.KEY_8, 
		KeyCode.KEY_9, 
		KeyCode.KEY_NUMPAD0,
		KeyCode.KEY_NUMPAD1,
		KeyCode.KEY_NUMPAD2,
		KeyCode.KEY_NUMPAD3,
		KeyCode.KEY_NUMPAD4,
		KeyCode.KEY_NUMPAD5,
		KeyCode.KEY_NUMPAD6,
		KeyCode.KEY_NUMPAD7,
		KeyCode.KEY_NUMPAD8,
		KeyCode.KEY_NUMPAD9,
		KeyCode.KEY_A, 	
		KeyCode.KEY_N, 	
		KeyCode.KEY_P,	
		KeyCode.KEY_END,
		KeyCode.KEY_F5
	];
	
	var data = (obj.id).split('_');
console.log(event.keyCode);
	if ((event.keyCode === KeyCode.KEY_RETURN) || (event.keyCode === KeyCode.KEY_DOWN)) {
		// Avall
		data[1]++;
		var grd = data[0] + '_' + data[1] + '_' + data[2];
		while ((document.getElementById(grd) !== null) && (document.getElementById(grd).disabled)) {
			data[1]++;
			grd = data[0] + '_' + data[1] + '_' + data[2];
		}
		if (document.getElementById(grd) !== null)
			document.getElementById(grd).focus();
		event.preventDefault();
	}
	else if (event.keyCode === KeyCode.KEY_UP) {
		// Amunt
		if (data[1] > 0) {
			data[1]--;
			var grd = data[0] + '_' + data[1] + '_' + data[2];
			while ((document.getElementById(grd) !== null) && (document.getElementById(grd).disabled)) {
				data[1]--;
				grd = data[0] + '_' + data[1] + '_' + data[2];
			}
			if (document.getElementById(grd) !== null)
				document.getElementById(grd).focus();
		}
		event.preventDefault();
	}
	else if (event.keyCode === KeyCode.KEY_LEFT) {
		// Esquerra
		if (data[2] > 0) {
			data[2]--;
			var grd = data[0] + '_' + data[1] + '_' + data[2];
			while ((document.getElementById(grd) !== null) && (document.getElementById(grd).disabled)) {
				data[2]--;
				grd = data[0] + '_' + data[1] + '_' + data[2];
			}
			if (document.getElementById(grd) !== null)
				document.getElementById(grd).focus();
		}
		event.preventDefault();
	}
	else if (event.keyCode === KeyCode.KEY_RIGHT) {
		// Dreta
		data[2]++;
		var grd = data[0] + '_' + data[1] + '_' + data[2];
		while ((document.getElementById(grd) !== null) && (document.getElementById(grd).disabled)) {
			data[2]++;
			grd = data[0] + '_' + data[1] + '_' + data[2];
		}
		if (document.getElementById(grd) !== null)
			document.getElementById(grd).focus();
		event.preventDefault();
	}
	else if (event.ctrlKey || event.shiftKey || event.altKey || event.metaKey) {
		event.preventDefault();
	}
	else if (TECLES_PERMESES.indexOf(event.keyCode) === -1) {
		event.preventDefault();
	}
}

/**
 * Envia el cursor al final del text (per a INPUT)
 * @param element Objecte que s'ha d'enviar el cursor al final del text.
 */
function EnviaCursorAlFinalDelText(element) {
	// https://codepen.io/chrisshaw/pen/yNOVaz
	function setCaretPosition(ctrl, pos) {
		// Modern browsers
		if (ctrl.setSelectionRange) {
			ctrl.focus();
			ctrl.setSelectionRange(pos, pos);
		}
		// IE8 and below
		else if (ctrl.createTextRange) {
			var range = ctrl.createTextRange();
			range.collapse(true);
			range.moveEnd('character', pos);
			range.moveStart('character', pos);
			range.select();
		}
	}	
	setCaretPosition(element, element.value.length);
}

/**
 * ObteNota
 *
 * Obté la nota d'un input i la manté per comprovar si ha canviat en sortir de l'element.
 *
 * @param element Input que ha fet la crida.
 */
function ObteNota(element) { 
	sText = 'Executant ObteNota... ';
//	$('#debug').html(sText);
	var sNota = element.value;
	$('input[name="TempNota"]').val(sNota);
	sText = sText + 'Valor desat: ' + sNota;
//	$('#debug').html(sText);
	EnviaCursorAlFinalDelText(element);
}

/**
 * CalculaTotalFila
 * Calcula els totalitzadors d'aquella fila.
 * @param grd Nom de la graella.
 * @param y Fila.
 */
function CalculaTotalFila(grd, y) { 
//console.dir(document.getElementById(grd + '_ArrayHores'));
	var ArrayHores = JSON.parse(document.getElementById(grd + '_ArrayHores').value);	
	var ToralHores = document.getElementById(grd + '_TotalHores').value;	
//console.dir(ArrayHores);

	var Total = 0;
	var i = 0;
	var CellaId = grd + '_' + y + '_' + i;
	while (document.getElementById(CellaId) !== null) {
		Valor = document.getElementById(CellaId).value;
		if (Valor >= 5)
			Total += ArrayHores[i];
	
		i++;
		CellaId = grd + '_' + y + '_' + i;
	}
	CellaId = grd + '_TotalHores_' + y;
	var objTotalHores = document.getElementById(CellaId);
//console.dir(objTotalHores);
	objTotalHores.textContent = Total;

	CellaId = grd + '_TotalPercentatge_' + y;
	var objTotalPercentatge = document.getElementById(CellaId);
	TotalPercentatge = 100*Total/ToralHores;
	objTotalPercentatge.textContent = TotalPercentatge.toLocaleString("en-US", {maximumFractionDigits:2, minimumFractionDigits:2}) + '%';
	if (TotalPercentatge >= 60)
		objTotalPercentatge.style.backgroundColor = 'lightgreen'
	else
		objTotalPercentatge.style.backgroundColor = '';
}

/**
 * CalculaTotalColumna
 * Calcula els totalitzadors d'aquella columna.
 * @param grd Nom de la graella.
 * @param x Columna.
 */
function CalculaTotalColumna(grd, x) { 
}

/**
 * ActualitzaNota
 *
 * Actualitza la nota d'un input.
 *
 * @param element Input que ha fet la crida.
 */
function ActualitzaNota(element) { 
	sText = 'Executant ActualitzaNota... ';
	$('#debug').html(sText);
console.log(sText);
	
	var sNota = $('input[name="TempNota"]').val();	
//console.log(sNota);
//console.log(element.value);
	if (sNota == element.value) {
		sText = sText + 'No ha calgut actualitzar';
		$('#debug').html(sText);
console.log(sText);
	}
	else {
		// <INPUT>
		// name: conté id i convocatòria
		// id: conté les coordenades x, y. Inici a (0, 0).
			
console.log(element.name);
console.dir(element.id);

		var data = (element.id).split('_');
		var x = data[1];
		var y = data[2];
		CalculaTotalFila(data[0], x);
//		CalculaTotalColumna(data[0], y);
		
		
		$('input[name="TempNota"]').val(sNota);	
		$.ajax( {
			type: 'POST',
			url: 'AccionsAJAX.php',
			data:{
				'accio': 'ActualitzaNota',
				'nom': element.name,
				'valor': element.value
				},
			success: function(data) {
				$('#debug').html(data);
			}, 
			error: function (data) {
				$('#debug').html('Hi ha hagut un error.');
			}
		} );
	}
}

/**
 * ActualitzaNotaRecuperacio
 *
 * Actualitza la nota de recuperació d'un input.
 *
 * @param element Input que ha fet la crida.
 * @param nota Nota de recuperació.
 */
function ActualitzaNotaRecuperacio(element, nota) { 
	sText = 'Executant ActualitzaNotaRecuperacio... ';
	$('#debug').html(sText);
/*	
	var sNota = $('input[name="TempNota"]').val();	
//console.log(sNota);
//console.log(element.value);
	if (sNota == element.value) {
		sText = sText + 'No ha calgut actualitzar';
		$('#debug').html(sText);
	}
	else { */
	//	$('input[name="TempNota"]').val(sNota);	
//console.log(element.value);
console.dir(element);
		$.ajax( {
			type: 'POST',
			url: 'AccionsAJAX.php',
			data:{
				'accio': 'ActualitzaNotaRecuperacio',
				'nom': element.name,
				'valor': nota
				},
			success: function(data) {
				element.value = nota;
				alert(data);
				$('#debug').html(data);
			}, 
			error: function (data) {
				$('#debug').html('Hi ha hagut un error.');
			}
		} );
//	}
}

/**
 * NumeroANota
 *
 * Transforma una nota numèrica al seu valor de text. Valors numèrics:
 *   - 1, 2, 3, 4, 5, 6, 7, 8, 9, 10.
 *   - NP: -1, A: 100, NA: -100.
 *   - NULL passa a ser la cadena nul·la.
 *
 * @param int Valor Valor numèric o NULL.
 * @return string Retorna la nota tal com s'entra a l'aplicació (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, NP, A, NA).
 */
function NumeroANota(Valor)
{
	if (Valor == -1) 
		return 'NP';
	else if (Valor == 100) 
		return 'A';
	else if (Valor == -100) 
		return 'NA';
	else
		return Valor;
}

/**
 * Donat un registre de notes, torna la última convocatòria amb nota.
 * @param element Registre amb les dades de la nota.
 * @return Última convocatòria.
 */
function UltimaConvocatoriaNota(element) {
	if (element.nota5 != '') 
		return 5;
	else if (element.nota4 != '') 
		return 4;
	else if (element.nota3 != '') 
		return 3;
	else if (element.nota2 != '') 
		return 2;
	else if (element.nota1 != '') 
		return 1;
	else 
		return -999;
}

/**
 * ActualitzaTaulaNotes
 *
 * Actualitza la nota d'un input.
 *
 * @param element Input que ha fet la crida.
 */
function ActualitzaTaulaNotes(element) { 
	$('#debug').html('Executant ActualitzaTaulaNotes...');
//console.log('ActualitzaTaulaNotes');
//console.log($('input#Nivell').val());
    $.ajax( {
        type: 'POST',
        url: 'AccionsAJAX.php',
        data:{
			'accio': 'ActualitzaTaulaNotes',
            'CicleId': $('input#CicleId').val(),
            'Nivell': $('input#Nivell').val()
            },
        success: function(data) {
			$('#debug').html('Executant ActualitzaTaulaNotes... OK');
            //$('#debug2').html(data);
			var jsonData = JSON.parse(data);
//console.dir(jsonData);
			var i, sNota, iNota, iNotaId, sTxtNotaId;
			for (i in jsonData.notes) {
				if (jsonData.notes[i].convocatoria > 0) {
					sNota = 'nota' + jsonData.notes[i].convocatoria;
					//console.dir(sNota);
					iNota = jsonData.notes[i][sNota];
					iNotaId = jsonData.notes[i].notes_id;
					sTxtNotaId = 'txtNotaId_' + iNotaId + '_' + jsonData.notes[i].convocatoria;
					//console.dir(sTxtNotaId + ': ' + iNota);

					// Per si hi han recuperacions
					// iNota = UltimaConvocatoriaNota(jsonData.notes[i]);

					sNota = NumeroANota(iNota);
	//				console.dir(sNota);
	
					casella = $('input[name="' + sTxtNotaId + '"]');
					if(typeof casella[0] === 'undefined' || casella[0].style.backgroundColor == 'lime') {
						// No actualitzem
					}
					else 
						casella.val(sNota);
				}
			}		
        }, 
		error: function (data) {
			$('#debug').html('Executant ActualitzaTaulaNotes... ERROR');
		}
    } );
}
