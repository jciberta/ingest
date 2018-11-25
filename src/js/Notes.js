/** 
 * Notes.js
 *
 * Accions AJAX per a les notes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */


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
console.log(sNota);
console.log(element.value);
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
		return -100;
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
			var s='input[name="txtNotaId_1_1"]'; $(s).val('XXX');
			for (i in jsonData.notes) {
				sNota = 'nota' + jsonData.notes[i].convocatoria;
				//console.dir(sNota);
				iNota = jsonData.notes[i][sNota];
				iNotaId = jsonData.notes[i].notes_id;
				sTxtNotaId = 'txtNotaId_' + iNotaId + '_' + jsonData.notes[i].convocatoria;
				//console.dir(sTxtNotaId + ': ' + iNota);
				sNota = NumeroANota(iNota);
				console.dir(sNota);
				$('input[name="' + sTxtNotaId + '"]').val(sNota);
			}		
        }, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error.');
		}
    } );
}
