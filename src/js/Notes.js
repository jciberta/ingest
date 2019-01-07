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
 * MostraOculta la graella de notes.
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
 * NotaKeyDown
 *
 * Funció per moure's per la graella.
 *
 * @param obj Objecte que ha provocat la crida.
 * @param event Event que ha provocat la crida.
 */
function NotaKeyDown(obj, event) {
	var data = (obj.id).split('_');
console.log(event.keyCode);
	if ((event.keyCode === 13) || (event.keyCode === 40)) {
		// Avall
		data[1]++;
		var grd = data[0] + '_' + data[1] + '_' + data[2];
		while ((document.getElementById(grd) !== null) && (document.getElementById(grd).disabled)) {
			data[1]++;
			grd = data[0] + '_' + data[1] + '_' + data[2];
		}
		if (document.getElementById(grd) !== null)
			document.getElementById(grd).focus();
	}
	else if (event.keyCode === 38) {
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
	}
	else if (event.keyCode === 37) {
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
	}
	else if (event.keyCode === 39) {
		// Dreta
		data[2]++;
		var grd = data[0] + '_' + data[1] + '_' + data[2];
		while ((document.getElementById(grd) !== null) && (document.getElementById(grd).disabled)) {
			data[2]++;
			grd = data[0] + '_' + data[1] + '_' + data[2];
		}
		if (document.getElementById(grd) !== null)
			document.getElementById(grd).focus();
	}
	else if ([8, 9, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 65, 78, 80].indexOf(event.keyCode) === -1) {
		// Tecles permeses: BS, TAB, DEL, 0..9, A, N, P
		event.preventDefault();
	}
}

/**
 * ObteNota
 *
 * Obté la nota d'un input i la manté per comprovar si ha canviat en sortir de l'element.
 *
 * @param element Input que ha fet la crida.
 */
function ObteNota(element) { 
//alert(1);
	sText = 'Executant ObteNota... ';
//	$('#debug').html(sText);
	var sNota = element.value;
	$('input[name="TempNota"]').val(sNota);
	sText = sText + 'Valor desat: ' + sNota;
//	$('#debug').html(sText);
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
	
	var sNota = $('input[name="TempNota"]').val();	
//console.log(sNota);
//console.log(element.value);
	if (sNota == element.value) {
		sText = sText + 'No ha calgut actualitzar';
		$('#debug').html(sText);
	}
	else {
		$('input[name="TempNota"]').val(sNota);	
//console.log(element.value);
//console.dir(element);
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
 * ActualitzaTaulaNotes
 *
 * Actualitza la nota d'un input.
 *
 * @param element Input que ha fet la crida.
 */
function ActualitzaTaulaNotes(element) { 
	$('#debug').html('Executant ActualitzaTaulaNotes...');
//console.log($('input#CicleId').val());
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
					sNota = NumeroANota(iNota);
	//				console.dir(sNota);
					$('input[name="' + sTxtNotaId + '"]').val(sNota);
				}
			}		
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error.');
		}
    } );
}
