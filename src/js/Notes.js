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
	if ($('input#Formulari').val() != 'ExpedientSaga') {
		ResaltaFila(element, 'dodgerblue', 'dodgerblue');
		ResaltaColumna(element, 'dodgerblue', 'dodgerblue');
	}
}

/**
 * EnSortirCellaNota
 * @param element Cel·la que ha fet la crida.
 */
function EnSortirCellaNota(element) { 
console.log('->EnSortirCellaNotaModul');
var convocatoria = (element.name).split('_')[2];
console.log('Convocatòria: '+convocatoria);

//console.dir(element);
	if (convocatoria!=5) {
		if (element.value!='' && element.value>=0 && element.value<5)
			element.style.color = 'red';
		else
			element.style.color = 'black';
	}
	ActualitzaNota(element);
	if ($('input#Formulari').val() != 'ExpedientSaga') {
		ResaltaFila(element, '#A9A9A9', 'black');
		ResaltaColumna(element, '#A9A9A9', 'black');
	}
}

/**
 * EnEntrarCellaNotaModul
 * @param element Cel·la que ha fet la crida.
 */
function EnEntrarCellaNotaModul(element) { 
	ObteNotaModul(element);
	if ($('input#Formulari').val() != 'ExpedientSaga') {
		ResaltaFila(element, 'dodgerblue', 'dodgerblue');
		ResaltaColumna(element, 'dodgerblue', 'dodgerblue');
	}
}

/**
 * EnSortirCellaNotaModul
 * @param element Cel·la que ha fet la crida.
 */
function EnSortirCellaNotaModul(element) { 
	if (element.value!='' && element.value>=0 && element.value<5)
		element.style.color = 'red';
	else
		element.style.color = 'black';
	ActualitzaNotaModul(element);
	if ($('input#Formulari').val() != 'ExpedientSaga') {
		ResaltaFila(element, '#A9A9A9', 'black');
		ResaltaColumna(element, '#A9A9A9', 'black');
	}
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
	var tr = $('tr[name=Baixa1]');
	(obj.checked) ? tr.show() : tr.hide();
}

/**
 * MostraTotAprovat
 * Mostra/Oculta els alumnes que ho tenen tot aprovat.
 * @param obj Objecte que ha provocat la crida.
 */
function MostraTotAprovat(obj) {
	// Seleccionem els que ho tenen tot aprovat
	var tr = $('tr.Aprovat100');
	(obj.checked) ? tr.show() : tr.hide();
}

/**
 * MostraConvocatoriesAnteriors
 * Mostra/Oculta els alumnes que ho tenen tot aprovat a les convocatòries anteriors i estan matriculats.
 * @param obj Objecte que ha provocat la crida.
 */
function MostraConvocatoriesAnteriors(obj) {
	// Seleccionem els que ho tenen tot aprovat a les convocatòries anteriors
	var tr = $('tr.ConvocatoriesAnteriors');
	(obj.checked) ? tr.show() : tr.hide();
}

/**
 * MostraAlumnesUFPendents
 * Mostra/Oculta els alumnes que ho tenen UF Pendents i estan matriculats.
 * @param obj Objecte que ha provocat la crida.
 */
function MostraAlumnesUFPendents(obj,id) {
	// Seleccionem els alumnes que ho tenen UF Pendents i estan matriculats.
	var tr = $('tr.NoAprovat100');
	(obj.checked) ? tr.hide() : tr.show();
}

/**
 * MostraGrup
 * Mostra/Oculta els alumnes d'un grup
 * @param obj Objecte que ha provocat la crida.
 * @param grup Grup classe.
 */
function MostraGrup(obj, grup) {
	var tr = $('tr.Grup'+grup);
	(obj.checked) ? tr.show() : tr.hide();
}

/**
 * MostraTutoria
 * Mostra/Oculta els alumnes d'una tutoria
 * @param obj Objecte que ha provocat la crida.
 * @param tutoria Grup tutoria.
 */
function MostraTutoria(obj, tutoria) {
	var tr = $('tr.Tutoria'+tutoria);
	(obj.checked) ? tr.show() : tr.hide();
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
 * Introdueix una nota de IntrodueixConvalidacio
 * @param obj Objecte que ha provocat la crida (INPUT).
 */
function IntrodueixConvalidacio(obj) {
console.dir(obj[0]);
//alert(obj[0].value);
	bootbox.prompt({
		title: "Introdueix la nota de convalidació",
		inputType: 'number',
		callback: function (result) {
			console.log(result);
			if (result>0 && result <=10) {
				ActualitzaConvalidacio(obj[0], result);
			}
			
		}
	});
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
		KeyCode.KEY_END,
		KeyCode.KEY_F5
	];
	const TECLES_NOTA = [
		KeyCode.KEY_0, KeyCode.KEY_1, KeyCode.KEY_2, KeyCode.KEY_3, KeyCode.KEY_4, KeyCode.KEY_5, KeyCode.KEY_6, KeyCode.KEY_7, KeyCode.KEY_8, KeyCode.KEY_9, 
		KeyCode.KEY_NUMPAD0, KeyCode.KEY_NUMPAD1, KeyCode.KEY_NUMPAD2, KeyCode.KEY_NUMPAD3, KeyCode.KEY_NUMPAD4, KeyCode.KEY_NUMPAD5, KeyCode.KEY_NUMPAD6, KeyCode.KEY_NUMPAD7, KeyCode.KEY_NUMPAD8, KeyCode.KEY_NUMPAD9,
		KeyCode.KEY_A, KeyCode.KEY_N
	];
	const TECLES_NOTA_NUMERICA = [
		KeyCode.KEY_0, KeyCode.KEY_1, KeyCode.KEY_2, KeyCode.KEY_3, KeyCode.KEY_4, KeyCode.KEY_5, KeyCode.KEY_6, KeyCode.KEY_7, KeyCode.KEY_8, KeyCode.KEY_9, 
		KeyCode.KEY_NUMPAD0, KeyCode.KEY_NUMPAD1, KeyCode.KEY_NUMPAD2, KeyCode.KEY_NUMPAD3, KeyCode.KEY_NUMPAD4, KeyCode.KEY_NUMPAD5, KeyCode.KEY_NUMPAD6, KeyCode.KEY_NUMPAD7, KeyCode.KEY_NUMPAD8, KeyCode.KEY_NUMPAD9
	];
	
	var data = (obj.id).split('_');
//console.dir(obj);
//console.log('Valor anterior: '+obj.value);
//console.log(event.keyCode);
	if ((event.keyCode === KeyCode.KEY_RETURN) || (event.keyCode === KeyCode.KEY_DOWN)) {
		// Avall
		data[1]++;
		var grd = data[0] + '_' + data[1] + '_' + data[2];
		var objGrd = document.getElementById(grd);

//console.log('grd:' + grd);
//console.log($(objGrd).is(":visible"));
//console.dir(objGrd);
//console.log('window.getComputedStyle(objGrd).display: ' + window.getComputedStyle(objGrd).display);
		var objTR = objGrd.parentNode.parentNode;
//console.dir(objTR);
//console.log('window.getComputedStyle(objTR).display: ' + window.getComputedStyle(objTR).display);
//console.log('objGrd.style.display:' + objGrd.style.display);

//		while ((document.getElementById(grd) !== null) && (document.getElementById(grd).disabled)) {
		while ((objGrd !== null) && (objGrd.disabled || objTR.style.display === 'none')) {
			data[1]++;
			grd = data[0] + '_' + data[1] + '_' + data[2];
			objGrd = document.getElementById(grd);
			objTR = objGrd.parentNode.parentNode;
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
			var objGrd = document.getElementById(grd);
			var objTR = objGrd.parentNode.parentNode;
//			while ((document.getElementById(grd) !== null) && (document.getElementById(grd).disabled)) {
			while ((objGrd !== null) && (objGrd.disabled || objTR.style.display === 'none')) {
				data[1]--;
				grd = data[0] + '_' + data[1] + '_' + data[2];
				objGrd = document.getElementById(grd);
				objTR = objGrd.parentNode.parentNode;
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
	else if (TECLES_NOTA.indexOf(event.keyCode) !== -1) {
		sValorAnterior = (obj.value).toUpperCase();
		if (obj.classList.contains('fct')) {
			// Nota FCT. A dins la casella només es permetes les combinacions: A, NA
			var bPermetreTecla = (
				((sValorAnterior == '') && (event.keyCode === KeyCode.KEY_A)) ||
				((sValorAnterior == '') && (event.keyCode === KeyCode.KEY_N)) ||
				((sValorAnterior == 'N') && (event.keyCode === KeyCode.KEY_A)) 
				);
			if (!bPermetreTecla) {
				event.preventDefault();
			}
			else if (event.keyCode === KeyCode.KEY_N) {
				// En prémer N, ja surt la combinació NA (No Apte)
				obj.value = 'NA';
				event.preventDefault();
			}
		}
		else {
			// Resta de notes. A dins la casella només es permetes les combinacions: 1, .. 9, 10
			var bPermetreTecla = (sValorAnterior == '') ||
				((sValorAnterior == '1') && ((event.keyCode === KeyCode.KEY_0) || (event.keyCode === KeyCode.KEY_NUMPAD0)));
			if ((!bPermetreTecla) || ((event.keyCode === KeyCode.KEY_0) && (sValorAnterior == '')) ||
				(event.keyCode === KeyCode.KEY_A) ||(event.keyCode === KeyCode.KEY_N) 
				)
				event.preventDefault();			
		}
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
 * ObteNotaModul
 * Obté la nota mitjana d'un input i la manté per comprovar si ha canviat en sortir de l'element.
 * @param element Input que ha fet la crida.
 */
function ObteNotaModul(element) { 
	sText = 'Executant ObteNota... ';
//	$('#debug').html(sText);
	var sNota = element.value;
	$('input[name="TempNotaModul"]').val(sNota);
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
	if (alumne !== null)
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
	if (uf !== null)
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
 * ActualitzaNotaModul
 * Actualitza la nota d'un input.
 * @param element Input que ha fet la crida.
 * @param bool bSempreActualitza Indica que s'ha de forçar l'actualització.
 */
function ActualitzaNotaModul(element, bSempreActualitza) {
console.log('ActualitzaNotaModul');
	// Valor per defecte
	if (typeof bSempreActualitza === "undefined" || bSempreActualitza === null) 
		bSempreActualitza = false; 

	sText = 'Executant ActualitzaNotaModul... ';
	$('#debug').html(sText);
	
	var sNota = $('input[name="TempNotaModul"]').val();	
console.log(element.value);
	if (sNota == element.value && !bSempreActualitza) {
		sText = sText + 'No ha calgut actualitzar';
		$('#debug').html(sText);
console.log(sText);
	}
	else {
		// <INPUT>
		// name: conté identificadors de la nota, matrícula i mòdul.
		// id: conté les coordenades x, y. Inici a (0, 0).
			
console.log(element.name);
console.dir(element.id);

		var data = (element.id).split('_');
		var x = data[1];
		var y = data[2];
		
		$('input[name="TempNotaModul"]').val(sNota);	
		$.ajax( {
			type: 'POST',
			url: 'lib/LibNotes.ajax.php',
			data:{
				'accio': 'ActualitzaNotaModul',
//				'mp': $('input#ModulId').val(),
				'nom': element.name,
				'valor': element.value
				},
			success: function(data) {
				$('#debug').html(data);
//				CalculaTotalFila(data[0], x);
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
 * Desconvalida
 * Treu la convalidació d'una UF.
 * @param NotaId Identificador de la nota.
 */
function Desconvalida(NotaId) { 
	$.ajax( {
		type: 'POST',
		url: 'lib/LibNotes.ajax.php',
		data:{
			'accio': 'Desconvalida',
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
 * ActualitzaConvalidacio
 * Actualitza la nota de convalidació d'un input.
 * @param element Input que ha fet la crida.
 * @param nota Nota de convalidació.
 */
function ActualitzaConvalidacio(element, nota) { 
	sText = 'Executant ActualitzaConvalidacio... ';
	$('#debug').html(sText);
console.dir(element);
	$.ajax( {
		type: 'POST',
		url: 'lib/LibNotes.ajax.php',
		data:{
			'accio': 'ActualitzaConvalidacio',
			'nom': element.name,
			'valor': nota
			},
		success: function(data) {
			element.value = nota;
			element.style.backgroundColor = 'blue';
			element.style.color = 'white';
			$('#debug').html(data);			
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

/**
 * CalculaQualificacionsFinalsModul
 * Calcula les qualificacions finals del mòdul (de tota la graella).
 */
function CalculaQualificacionsFinalsModul() { 
console.log('->CalculaQualificacionsFinalsModul');
	var TotalX = document.getElementById('TotalX').value;
	var TotalY = document.getElementById('TotalY').value;
	var objArrayHores = document.getElementById('grd2_ArrayHores');
	var ArrayHores = JSON.parse(objArrayHores.value);	
	var TotalHores = document.getElementById('grd2_TotalHores').value;	

	for (var y = 0; y <= TotalY; y++) {
console.log('Fila '+y);
		ArrayNotes = [];
		for (var x = 0; x < TotalX; x++) {
			CellaId = 'grd2_' + y + '_' + x;
			Cella = document.getElementById(CellaId).value;
			ArrayNotes.push(Cella);
		}
//console.dir(ArrayHores);
//console.dir(ArrayNotes);
		Qualificacio = CalculaQualificacioFinalModul(ArrayNotes, ArrayHores);
console.log('  Qualificació: ' + Qualificacio);
		CellaId = 'grd2_' + y + '_' + x;
		objCella = document.getElementById(CellaId);
		//document.getElementById(CellaId).value(Qualificacio);

		if (!objCella.disabled) {
			objCella.value = Qualificacio;
			ActualitzaNotaModul(objCella);
		}
	}
}
	
/**
 * CalculaQualificacioFinalModul
 * Calcula la qualificaciós final del mòdul.
 * @param array aNotes Array de notes de cada UF.
 * @param array aHores Array d'hores de cada UF.
 * @return Retorna la qualificaciós final del mòdul, en el cas que es pugui calcular.
 */
function CalculaQualificacioFinalModul(aNotes, aHores) { 
console.log('->CalculaQualificacioFinalModul');
	var bSenseNota = false;
	var bNotaSuspera = false;
	var TotalNotes = 0;
	var TotalHores = 0;
	var Qualificacio = '';
	
	for (var i = 0; i < aNotes.length && !bSenseNota; i++) {
		bSenseNota = (aNotes[i] == '');
		if (!bSenseNota) {
			if (aNotes[i] < 5)
				bNotaSuspera = true;
			TotalNotes += aNotes[i]*aHores[i];
			TotalHores += 10*aHores[i];
		}
	}
	if (!bSenseNota) {
		Qualificacio = Math.round(10* TotalNotes / TotalHores);
		if (bNotaSuspera)
			Qualificacio = Math.min(4, Qualificacio);
	}
	
	return Qualificacio;
}

/**
 * EsborraQualificacionsFinalsModul
 * Esborra les qualificacions finals del mòdul (de tota la graella).
 */
function EsborraQualificacionsFinalsModul() { 
console.log('->EsborraQualificacionsFinalsModul');
	var TotalX = document.getElementById('TotalX').value;
	var TotalY = document.getElementById('TotalY').value;
	for (var y = 0; y <= TotalY; y++) {
		CellaId = 'grd2_' + y + '_' + TotalX;
		objCella = document.getElementById(CellaId);
		if (!objCella.disabled) {
			objCella.value = '';
			ActualitzaNotaModul(objCella, true);
		}
	}
}
