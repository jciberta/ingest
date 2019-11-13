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
 * EnEntrarCellaNota
 * @param element Cel·la que ha fet la crida.
 */
function EnEntrarCellaNota(element) { 
	ObteNota(element);
	ResaltaFila(element, 'dodgerblue', 'dodgerblue');
	ResaltaColumna(element, 'dodgerblue', 'dodgerblue');
}

/**
 * EnSortirCellaNota
 * @param element Cel·la que ha fet la crida.
 */
function EnSortirCellaNota(element) { 
	if (element.value!='' && element.value>=0 && element.value<5)
		element.style.color = 'red';
	else
		element.style.color = 'black';
	ActualitzaNota(element);
	ResaltaFila(element, '#A9A9A9', 'black');
	ResaltaColumna(element, '#A9A9A9', 'black');
}

/**
 * MostraGraellaNotes
 * Mostra/Oculta la graella de notes.
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
 * Mostra/Oculta els alumnes que s'han donat de baixa.
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
 * MostraTotAprovat
 * Mostra/Oculta els alumnes que ho tenen tot aprovat.
 * @param obj Objecte que ha provocat la crida.
 */
function MostraTotAprovat(obj) {
	// Seleccionem els que ho tenen tot aprovat
	var tr = $('tr.Aprovat100');

	if (obj.checked)
		tr.show()
	else
		tr.hide();
}

/**
 * MostraGrup
 * Mostra/Oculta els alumnes d'un grup
 * @param obj Objecte que ha provocat la crida.
 * @param grup Grup en format numèric (1=A, 2=B, etc).
 */
function MostraGrup(obj, grup) {
	switch (grup) {
		case 1: sGrup = 'A'; break;
		case 2: sGrup = 'B'; break;
		case 3: sGrup = 'C'; break;
		default: sGrup = '';
	}
	var tr = $('tr.Grup'+sGrup);
	if (obj.checked)
		tr.show()
	else
		tr.hide();
}

/**
 * MostraTutoria
 * Mostra/Oculta els alumnes d'una tutoria
 * @param obj Objecte que ha provocat la crida.
 * @param tutoria Tutoria en format numèric (1=AB, 2=BC, etc).
 */
function MostraTutoria(obj, tutoria) {
	switch (tutoria) {
		case 1: sTutoria = 'AB'; break;
		case 2: sTutoria = 'BC'; break;
		default: sTutoria = '';
	}
	var tr = $('tr.Tutoria'+sTutoria);
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
 * Marca una nota com a anterior, és a dir, posa la convocatòria a 0 (ha de ser >=5).
 * @param obj Objecte que ha provocat la crida (INPUT).
 */
function MarcaComNotaAnterior(obj) {
console.log('->MarcaComNotaAnterior');
	element = obj[0];
	$.ajax( {
		type: 'POST',
		url: 'lib/LibNotes.ajax.php',
		data:{
			'accio': 'MarcaComNotaAnterior',
			'nom': element.name
			},
		success: function(data) {
			element.style.backgroundColor = 'black';
			element.style.color = 'white';
			element.disabled = true;
			$('#debug').html(data);
		}, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
	} );
}

/**
 * Convalida aquella UF. Posa la nota a 5.
 * @param obj Objecte que ha provocat la crida (INPUT).
 */
function Convalida(obj) {
console.log('->Convalida');
	element = obj[0];
	$.ajax( {
		type: 'POST',
		url: 'lib/LibNotes.ajax.php',
		data:{
			'accio': 'Convalida',
			'nom': element.name
			},
		success: function(data) {
			element.value = 5;
			element.style.backgroundColor = 'blue';
			element.style.color = 'white';
			$('#debug').html(data);
		}, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
	} );
}

/**
 * NotaKeyDown
 * Funció per moure's per la graella.
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
 * Obté la nota d'un input i la manté per comprovar si ha canviat en sortir de l'element.
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
 * ResaltaFila
 * @param element Cel·la que ha fet la crida.
 * @param color Color amb que es vol resaltar la fila.
 * @param colorAlumne Color amb que es vol resaltar el nom de l'alumne.
 */
function ResaltaFila(element, color, colorAlumne) { 
	var data = (element.id).split('_');
	var grd = data[0];
	var x = data[1];
	var y = data[2];

	var alumne = document.getElementById('alumne_' + x);
	alumne.style.color = colorAlumne;

	var i = 0;
	var CellaId = grd + '_' + x + '_' + i;
	while (document.getElementById(CellaId) !== null) {
		Cella = document.getElementById(CellaId);
		Cella.style.border = '1px solid ' + color;
		Cella.style.margin = '1px';
		i++;
		CellaId = grd + '_' + x + '_' + i;
	}
}

/**
 * ResaltaColumna
 * @param element Cel·la que ha fet la crida.
 * @param color Color amb que es vol resaltar la columna.
 * @param colorUF Color amb que es vol resaltar la unitat formativa.
 */
function ResaltaColumna(element, color, colorUF) { 
	var data = (element.id).split('_');
	var grd = data[0];
	var x = data[1];
	var y = data[2];

	var uf = document.getElementById('uf_' + y);
	uf.style.color = colorUF;

	var i = 0;
	var CellaId = grd + '_' + i + '_' + y;
	while (document.getElementById(CellaId) !== null) {
		Cella = document.getElementById(CellaId);
		Cella.style.border = '1px solid ' + color;
		Cella.style.margin = '1px';
		i++;
		CellaId = grd + '_' + i + '_' + y;
	}
}

/**
 * CalculaTotalFila
 * Calcula els totalitzadors d'aquella fila.
 * @param grd Nom de la graella.
 * @param y Fila.
 */
function CalculaTotalFila(grd, y) { 
//console.dir(document.getElementById(grd + '_ArrayHores'));
	var objArrayHores = document.getElementById(grd + '_ArrayHores');

	if (objArrayHores !== null) {	
		var ArrayHores = JSON.parse(objArrayHores.value);	
		var TotalHores = document.getElementById(grd + '_TotalHores').value;	
		var Nivell = document.getElementById(grd + '_Nivell').value;	
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
		TotalPercentatge = 100*Total/TotalHores;
		objTotalPercentatge.textContent = TotalPercentatge.toLocaleString("en-US", {maximumFractionDigits:2, minimumFractionDigits:2}) + '%';
		if ((TotalPercentatge>=60 && Nivell==1) || (TotalPercentatge>=100 && Nivell==2))
			objTotalPercentatge.style.backgroundColor = 'lightgreen'
		else
			objTotalPercentatge.style.backgroundColor = '';
	}
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
 * Actualitza la nota d'un input.
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
		
		$('input[name="TempNota"]').val(sNota);	
		$.ajax( {
			type: 'POST',
			url: 'lib/LibNotes.ajax.php',
			data:{
				'accio': 'ActualitzaNota',
				'nom': element.name,
				'valor': element.value
				},
			success: function(data) {
				$('#debug').html(data);
				CalculaTotalFila(data[0], x);
//		CalculaTotalColumna(data[0], y);
			}, 
			error: function (data) {
				$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
			}
		} );
	}
}

/**
 * AugmentaConvocatoria
 * Augmenta la convocatòria d'una nota.
 * @param NotaId Identificador de la nota.
 * @param Convocatoria Número de convocatòria.
 */
function AugmentaConvocatoria(NotaId, Convocatoria) { 
	$.ajax( {
		type: 'POST',
		url: 'lib/LibNotes.ajax.php',
		data:{
			'accio': 'AugmentaConvocatoria',
			'id': NotaId,
			'convocatoria': Convocatoria
		},
		success: function(data) {
			$('#debug').html(data);
		}, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
	});		
}

/**
 * RedueixConvocatoria
 * Redueix la convocatòria d'una nota.
 * @param NotaId Identificador de la nota.
 * @param Convocatoria Número de convocatòria.
 */
function RedueixConvocatoria(NotaId, Convocatoria) { 
	$.ajax( {
		type: 'POST',
		url: 'lib/LibNotes.ajax.php',
		data:{
			'accio': 'RedueixConvocatoria',
			'id': NotaId,
			'convocatoria': Convocatoria
		},
		success: function(data) {
			$('#debug').html(data);
		}, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
	});		
}

/**
 * ConvocatoriaA0
 * Posa la convocatòria d'una nota a 0 (aprovat).
 * @param NotaId Identificador de la nota.
 */
function ConvocatoriaA0(NotaId) { 
	$.ajax( {
		type: 'POST',
		url: 'lib/LibNotes.ajax.php',
		data:{
			'accio': 'ConvocatoriaA0',
			'id': NotaId
		},
		success: function(data) {
			$('#debug').html(data);
		}, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
	});		
}

/**
 * AugmentaConvocatoriaFila
 * Augmenta la convocatòria d'una fila de notes.
 * @param fila Fila de notes.
 * @param IdGraella Identificador de la graella de notes.
 */
function AugmentaConvocatoriaFila(Fila, IdGraella) { 
	bootbox.confirm({
		message: "Esteu segur que voleu augmentar la convocatòria?",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Augmenta',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if (result) {
				var grd = 'grd' + IdGraella;
				var i = 0;
				var CellaId = grd + '_' + Fila + '_' + i;
				var NotesFila = '';
				while (document.getElementById(CellaId) !== null) {
					obj = document.getElementById(CellaId);
					Valor = obj.value;
					Nom = obj.name;
					NotesFila += '"' + Nom + '": "' + Valor + '", '; 
					i++;
					CellaId = grd + '_' + Fila + '_' + i;
				}
				if (NotesFila != '') {
					NotesFila = NotesFila.slice(0, -2); // Treiem la darrera coma
					NotesFila = NotesFila.trim();
					NotesFila = '{' + NotesFila + '}';
				}
console.log("NotesFila: " + NotesFila);				

				$.ajax( {
					type: 'POST',
					url: 'lib/LibNotes.ajax.php',
					data:{
						'accio': 'AugmentaConvocatoriaFila',
						'dades': NotesFila,
					},
					success: function(data) {
						//$('#taula').html(data);
						//$('#debug').html('<textarea disabled>'+data+'</textarea>');
						$('#debug').html(data);
					}, 
					error: function(data) {
						$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
					}
				});		
			}
		}
	});	
}

/**
 * ActualitzaNotaRecuperacio
 * Actualitza la nota de recuperació d'un input.
 * @param element Input que ha fet la crida.
 * @param nota Nota de recuperació.
 */
function ActualitzaNotaRecuperacio(element, nota) { 
	sText = 'Executant ActualitzaNotaRecuperacio... ';
	$('#debug').html(sText);
console.dir(element);
	$.ajax( {
		type: 'POST',
		url: 'lib/LibNotes.ajax.php',
		data:{
			'accio': 'ActualitzaNotaRecuperacio',
			'nom': element.name,
			'valor': nota
			},
		success: function(data) {
			element.value = nota;
			element.style.backgroundColor = 'lime';
			element.disabled = true;
			$('#debug').html(data);
		}, 
		error: function (data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
	} );
}

/**
 * NumeroANota
 * Transforma una nota numèrica al seu valor de text. Valors numèrics:
 *   - 1, 2, 3, 4, 5, 6, 7, 8, 9, 10.
 *   - NP: -1, A: 100, NA: -100.
 *   - NULL passa a ser la cadena nul·la.
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
 * Actualitza la nota d'un input.
 * @param element Input que ha fet la crida.
 */
function ActualitzaTaulaNotes(element) { 
	$('#debug').html('Executant ActualitzaTaulaNotes...');
//console.log('ActualitzaTaulaNotes');
//console.log($('input#Nivell').val());
    $.ajax( {
        type: 'POST',
        url: 'lib/LibNotes.ajax.php',
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
