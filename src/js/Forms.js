/** 
 * Forms.js
 *
 * Accions JavaScript de suport a la llibreria de formularis.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

 
function escapeHTML(html) {
    return html.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
} 

// https://stackoverflow.com/questions/15637429/how-to-escape-double-quotes-in-json
function escapeDoubleQuotes(html) {
    return html.replace(/"/g, '\"');
} 

function EscapaDobleCometa(html) {
    return html.replace(/"/g, '~');
} 

function DesescapaDobleCometa(html) {
    return html.replace(/~/g, '"');
} 
 
/**
 * Comprova que els camps INPUT required tinguin dades
 * @param string FormId Id del formulari.
 * @return object Null si tots els camps required tenen dades, sinó el primer INPUT que no en té.
 */
function ComprovaCamps(FormId) { 
	Retorn = null;
	var frm = document.getElementById(FormId);
//console.dir(frm);	
	for (i=0; i<frm.length && Retorn==null; i++) {
//console.dir(frm.childNodes[i]);	
		var obj = frm[i];
		if (obj.tagName == 'INPUT') {
//console.dir(obj);	
//console.log('INPUT');
			if (obj.required &&(obj.value == ""))
				Retorn = obj;
		}
	}
	return Retorn;
}

/**
 * ActualitzaTaula
 * @param element Botó que ha fet la crida.
 */
function ActualitzaTaula(element) { 
console.log('-> ActualitzaTaula');
	var sCerca = $('input[name="edtRecerca"]').val();	
//	var sSQL = $('input[name="edtSQL"]').val();	
//	var sCamps = $('input[name="edtCamps"]').val();	
//	var sDescripcions = $('input[name="edtDescripcions"]').val();	

	var filtre = document.getElementById('filtre');
//console.log('Filtre');
//console.dir(filtre);
	var sFiltre = CreaFiltreJSON(filtre);
//console.log('sFiltre: ' + sFiltre);
	
	var frm = document.getElementById('frm');
	var sFrm = frm.value;	
	
//console.dir(frm);
//console.dir(sFrm);

	$.ajax( {
		type: 'POST',
		url: 'lib/LibForms.ajax.php',
		data:{
			'accio': 'ActualitzaTaula',
			'cerca': sCerca,
			'filtre': sFiltre,
//			'sql': sSQL,
//			'camps': sCamps,
//			'descripcions': sDescripcions,
			'frm': sFrm
		},
        success: function(data) {
            $('#taula').html(data);
			
			// Actualitzem la variable amagada que emmagatzema la SQL per a la descàrrega 
			// cada cop que es modifica el filtre.
			var urlCSV = document.getElementById('urlCSV');
			var URLDescarregaCSV = document.getElementById('DescarregaCSV');
//console.log('urlCSV: ' + urlCSV.value);
//console.dir(DescarregaCSV);
			if (URLDescarregaCSV != null)
				URLDescarregaCSV.href = urlCSV.value;
            //$('#debug').html('<textarea disabled>'+data+'</textarea>');
        }, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

/**
 * FiltraQBE
 * @param element Botó que ha fet la crida.
 */
function FiltraQBE(element) { 
console.log('-> FiltraQBE');
	var TaulaQBE = document.getElementById('TaulaQBE');
//console.log('TaulaQBE');
//console.dir(TaulaQBE);

	var inputs, index, params = '';
	inputs = TaulaQBE.getElementsByTagName('input');
	for (index = 0; index < inputs.length; ++index) {
		params += inputs[index].name + "=" + (inputs[index].value).replace(' ', '+') + "&";
	}
	if (params.length>0)
		params = params.slice(0, -1); // Treiem el darrer &
	params = params.trim();
console.log('params: ' + params);

	var frm = document.getElementById('frm');
	var sFrm = frm.value;	
//console.dir(frm);
//console.dir(sFrm);

	$.ajax( {
		type: 'POST',
		url: 'lib/LibForms.ajax.php',
		data:{
			'accio': 'FiltraQBE',
			'params': params,
			'frm': sFrm
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
 * CreaFiltreJSON
 * A partir d'un element DIV que conté el filtre obté les dades del filtre en format JSON.
 * @param filtre Element DIV que conté el filtre.
 * @return Filtre en format JSON.
 */
function CreaFiltreJSON(filtre) {
	var Retorn = '{';
//console.dir(filtre.childNodes);
	for (i=0; i<filtre.childNodes.length; i++) {
//console.dir(filtre.childNodes[i]);	
		var obj = filtre.childNodes[i];
//console.log('obj.tagName: '+obj.tagName);
		if (obj.tagName == 'SELECT') {
//console.log('SELECT');
			nom = obj.name;
			nom = nom.replace('cmb_', '');
			Retorn += '"' + nom + '": "' + obj.value + '", ';
		}
		else if (obj.tagName == 'INPUT') {
//console.log('INPUT');

			if (obj.type == 'checkbox') {
//console.log('checkbox');
				nom = obj.name;
				nom = nom.replace('chb_', '');
				valor = (obj.checked) ? 1 : 0;
				Retorn += '"' + nom + '": ' + valor + ', ';
			}
		}
		else if (obj.tagName == 'DIV') {
			// Usat en el lookup
//console.log('obj.name: '+obj.name);
//console.dir(obj);	
			for (j=0; j<obj.childNodes.length; j++) {
				var subobj = obj.childNodes[j];
//console.log('subobj.tagName: '+subobj.tagName);	
//console.dir(subobj);	
				if (subobj.tagName == 'INPUT') {
					if (subobj.type == 'hidden') {
						// Control lookup
						nom = subobj.name;
						nom = nom.replace('lkh_', '');	
						Retorn += '"' + nom + '": "' + subobj.value + '", ';
						break; // Només necessitem el primer lkh
					}
				}
				
			}
		}
	}
	Retorn = Retorn.slice(0, -2); // Treiem la darrera coma
	Retorn = Retorn.trim();
	if (Retorn.length>0)
		Retorn += '}';
console.log('Retorn: '+Retorn);
	return Retorn;
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
 * FormFitxaKeyDown
 *
 * Funció per controlar les tecles permeses.
 *
 * @param obj Objecte que ha provocat la crida.
 * @param event Event que ha provocat la crida.
 * @param tipus 0 per enter, 1 per real, 2 per lookup.
 */
function FormFitxaKeyDown(obj, event, tipus) {
	switch(tipus) {
	  case 0:
		// Enter. Tecles permeses: BS, DEL, 0..9, Esquerra, Dreta, ENTER
		if ([8, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 37, 39, 13].indexOf(event.keyCode) === -1) {
			event.preventDefault();
		}
		break;
	  case 1: 
		// Real. Tecles permeses: BS, DEL, 0..9, Esquerra, Dreta, ENTER, .
		if ([8, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 37, 39, 13, 46].indexOf(event.keyCode) === -1) {
			event.preventDefault();
		}
		break;
	  case 2: 
		// Lookup. Tecles permeses: BS, DEL
		if ([8, 46].indexOf(event.keyCode) === -1) {
			event.preventDefault();
		}
		else {
			event.preventDefault();
			obj.value = "";
			nom = obj.name;
			// Esborrem també el codi
			nom = nom.replace('lkp_', 'lkh_');
			obj = document.getElementsByName(nom)[0];
			obj.value = "";
			ActualitzaTaula(obj);
		}
		break;
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
//console.dir(controls[i]);		
		if (controls[i].name != '' && !controls[i].readOnly) {
			if (controls[i].type && controls[i].type === 'checkbox') {
				msg += '{"name":"' + controls[i].name + '","value":"' + (controls[i].checked ? 1 : 0) + '"},';
			}
			else 
				msg += '{"name":"' + controls[i].name + '","value":"' + EscapaDobleCometa(controls[i].value) + '"},';
		}
    }
	msg = msg.slice(0, -1); // Treiem la darrera coma
	msg += ']';
	return msg;
}

/**
 * SuprimeixRegistre
 * Suprimeix el registre d'una taula.
 * @param Taula Taula de la que es vol eliminar el registre.
 * @param ClauPrimaria Clau primària de la taula.
 * @param Valor valor de la clau primària del registre que es vol esborrar.
 */
function SuprimeixRegistre(Taula, ClauPrimaria, Valor) { 
	bootbox.confirm({
	//	title: "Suprimeix",
		message: "Esteu segur que voleu esborrar el registre?",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Suprimeix',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if (result) {
				var frm = document.getElementById('frm');
				var sFrm = frm.value;	
				$.ajax( {
					type: 'POST',
					url: 'lib/LibForms.ajax.php',
					data:{
						'accio': 'SuprimeixRegistre',
						'taula': Taula,
						'clau_primaria': ClauPrimaria,
						'valor': Valor,
						'frm': sFrm
					},
					success: function(data) {
						$('#taula').html(data);
						//$('#debug').html('<textarea disabled>'+data+'</textarea>');
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
 * DuplicaRegistre
 * Duplica un registre d'una taula.
 * @param Taula Taula de la que es vol eliminar el registre.
 * @param ClauPrimaria Clau primària de la taula.
 * @param Valor valor de la clau primària del registre que es vol esborrar.
 * @param CampCopia Nom del camp que es vol que aparegui el text "(còpia)" al final.
 */
function DuplicaRegistre(Taula, ClauPrimaria, Valor, CampCopia) { 
	bootbox.confirm({
		message: "Esteu segur que voleu duplicar el registre?",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Duplica',
			}
		},
		callback: function (result) {
			if (result) {
				var frm = document.getElementById('frm');
				var sFrm = frm.value;	
				$.ajax( {
					type: 'POST',
					url: 'lib/LibForms.ajax.php',
					data:{
						'accio': 'DuplicaRegistre',
						'taula': Taula,
						'clau_primaria': ClauPrimaria,
						'valor': Valor,
						'camp_copia': CampCopia,
						'frm': sFrm
					},
					success: function(data) {
						$('#taula').html(data);
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
 * DesaFitxa
 * @param element Botó que desa la fitxa.
 */
function DesaFitxa(element) { 

	// Formulari principal
//console.dir('element: ' + element);
	var frm = document.getElementById('frmFitxa');
//console.dir('frm: ' + frm);
	var jsonForm = GetFormDataJSON(frm);
console.dir('jsonForm: ' + jsonForm);

	var frm = document.getElementById('frm');
	var sFrm = frm.value;	

//	var jsonForm2 = JSON.stringify($('#frmFitxa').serializeArray());
//console.dir('jsonForm2: ' + jsonForm2);


	// Els detalls s'emmagatzemen en un array de detalls en JSON
	var jsonDetalls = '';
//	var Detalls = Array.from(document.getElementsByClassName("Detalls"));
	var Detalls = document.getElementsByClassName("Detalls");
//console.log(Detalls);		
	for(i=0;i<Detalls.length;i++) {
console.log(Detalls[i]);	
		jsonDetall = GetFormDataJSON(Detalls[i]);
console.log(jsonDetall);
		jsonDetalls += jsonDetall + ',';
	}
	if (jsonDetalls.length >0 )
		jsonDetalls = '[' + jsonDetalls.slice(0, -1) + ']';
console.log(jsonDetalls);

	// Acció corresponent a FormFitxa o FormFitxaDetall
	var accio = 'DesaFitxa';
	if (Detalls.length > 0)
		accio = 'DesaFitxaDetall';

	// Crida AJAX
	$('#MissatgeCorrecte').hide();
	$('#MissatgeError').hide();
	$.ajax( {
		type: 'POST',
		url: 'lib/LibForms.ajax.php',
		data:{
			'accio': accio,
			'form': jsonForm,
			'detalls': jsonDetalls,
			'frm': sFrm
		},
        success: function(data) {
			i = data.indexOf('ERROR DesaFitxa');
			if (i > -1) {
				bootbox.alert({
					message: 'Hi ha hagut un error en desar les dades:<p>' + data + "<p>Contacteu amb l'administrador.",
					centerVertical: true,
					locale: 'ca'
				});
			}
			else {
				bootbox.alert({
					message: "Les dades s'han desat correctament.",
					centerVertical: true,
					locale: 'ca'
				});
console.log('data:');
console.dir(data);
			}
        }, 
		error: function(data) {
			bootbox.alert({
				message: 'Hi ha hagut un error. Dades rebudes:<p> '+ JSON.stringify(data) + "<p>Contacteu amb l'administrador.",
				centerVertical: true,
				locale: 'ca'
			});			
		}
    } );
}

/**
 * OrdenaColumna
 * @param camp Camp a ordenar.
 * @param sentit Ascendent ('') o descendent ('DESC').
 */
function OrdenaColumna(camp, sentit) { 
console.log('-> OrdenaColumna');
	var sCerca = $('input[name="edtRecerca"]').val();	
	var filtre = document.getElementById('filtre');
	var sFiltre = CreaFiltreJSON(filtre);
	var frm = document.getElementById('frm');
	var sFrm = frm.value;	
	$.ajax( {
		type: 'POST',
		url: 'lib/LibForms.ajax.php',
		data:{
			'accio': 'OrdenaColumna',
			'cerca': sCerca,
			'filtre': sFiltre,
			'camp': camp,
			'sentit': sentit,
			'frm': sFrm
		},
        success: function(data) {
            $('#taula').html(data);
			
			if (sentit == '') {
				$('#FletxaAvall_'+camp).hide();
				$('#FletxaAmunt_'+camp).show();
			}
			else {
				$('#FletxaAvall_'+camp).show();
				$('#FletxaAmunt_'+camp).hide();
			}
        }, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}


/**
 * AfegeixDetall
 * Afegeix un registre a una taula que és detall d'una altra.
 * @param element Element que ha fet la crida.
 * @param Taula Taula a la que es vol afegir el registre.
 * @param ClauPrimaria Clau primària de la taula. Res si és autoincrementable.
 * @param CampMestre
 * @param ValorMestre
 * @param CampDetall
 * @param ValorDetall
 */
function AfegeixDetall(element, Taula, ClauPrimaria, CampMestre, ValorMestre, CampDetall) { 
console.log('-> AfegeixDetall');
	var frm = document.getElementById('frm');
	var sFrm = frm.value;

	var ValorDetall = element.value;
console.log('    ValorDetall: ' + ValorDetall);

	$.ajax( {
		type: 'POST',
		url: 'lib/LibForms.ajax.php',
		data:{
			'accio': 'AfegeixDetall',
			'taula': Taula,
			'clau_primaria': ClauPrimaria,
			'camp_mestre': CampMestre, 
			'valor_mestre': ValorMestre, 
			'camp_detall': CampDetall, 
			'valor_detall': ValorDetall,
			'frm': sFrm
		},
        success: function(data) {
            $('#taula').html(data);
        }, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}

// http://ecmanaut.blogspot.com/2006/07/encoding-decoding-utf8-in-javascript.html
function encode_utf8(s) {
  return unescape(encodeURIComponent(s));
}

function decode_utf8(s) {
  return decodeURIComponent(escape(s));
}

/**
 * CercaLookup
 *
 * @param codi Nom del component lookup que conté el codi.
 * @param valor Nom del component lookup que conté la descripció.
 * @param url Pàgina per fer la recerca.
 * @param camps Camps a mostrar al lookup.
 */
function CercaLookup(codi, valor, url, camps) { 
console.log('-> CercaLookup');
//console.log('    codi: ' + codi);
//console.log('    valor: ' + valor);
	targetFieldCodi = document.getElementsByName(codi)[0];
	targetFieldValor = document.getElementsByName(valor)[0];
	targetCamps = camps;
//console.log('targetFieldCodi: ' + targetFieldCodi.name);
//console.dir(targetFieldCodi);
//console.log('targetFieldValor: ' + targetFieldValor.name);

	w = screen.width - 100;
	h = screen.height - 100;
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);

//	var connector = (url.indexOf('?') == -1) ? '?' : '&';
//	var w = window.open(url + connector + 'Modalitat=mfBusca','_blank','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
	var w = window.open(url,'_blank','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
	// pass the targetField to the pop up window
	w.targetFieldCodi = targetFieldCodi;
	w.targetFieldValor = targetFieldValor;
	w.targetCamps = targetCamps;
	w.focus();
}

// this function is called by the pop up window
function setSearchResult(targetFieldCodi, targetFieldValor, returnValue) {
console.log('-> setSearchResult');
	//alert("returnValue:" + returnValue);
console.dir(targetFieldValor);

	// Obtenim els camps que s'han de mostrar al lookup.
console.log('targetFieldCodi: ' + targetFieldCodi.name + '_camps');
	var objCamps = document.getElementsByName(targetFieldCodi.name + '_camps')[0];
	var sCamps = (objCamps.value).replace(/ /g, '');;
console.log("sCamps: " + sCamps);
	var aCamps = sCamps.split(',');
console.dir("aCamps: " + aCamps);

	// El replace no substitueix totes les ocurrències de forma "normal". S'ha de fer amb una expressió regular.
	// https://stackoverflow.com/questions/1144783/how-to-replace-all-occurrences-of-a-string-in-javascript
	var jsonValorRetorn = returnValue.replace(/~/g, '"');
console.dir("jsonValorRetorn: " + jsonValorRetorn);
//alert("jsonValorRetorn: " + jsonValorRetorn);
	var obj = JSON.parse(jsonValorRetorn);

	// Recorrem les propietats (del 1r nivell) de l'objecte.
	// La primera propietat és l'identificador.
	// https://stackoverflow.com/questions/684672/how-do-i-loop-through-or-enumerate-a-javascript-object 
console.dir(obj);
	var bPrimer = true;
	var sText = '';
	for (var key in obj) {
//console.log("key: " + key);
		if (obj.hasOwnProperty(key)) {
//console.log("obj[key]: " + obj[key]);
			if (bPrimer) {
				targetFieldCodi.value = obj[key]; 
				// En el cas que estiguem en un formulari de recerca, cal realitzar alguna acció (actualitzar la taula).
				// https://stackoverflow.com/questions/1003053/does-html-hidden-control-have-any-events-like-onchange-or-something
				if (targetFieldCodi.onchange !== null)
					targetFieldCodi.onchange();
//console.log("targetFieldCodi: " + obj[key]);
				bPrimer = false;
			}
			else {
				if (aCamps.indexOf(key) > -1)
					sText += obj[key] + ' ';
			}
		}
	}
	targetFieldValor.value = sText.trim();
console.log("targetFieldValor: " + sText);

	window.focus();
}

// return the value to the parent window
function returnYourChoice(choice) {
console.log('-> returnYourChoice');
	opener.setSearchResult(targetFieldCodi, targetFieldValor, choice);
	close();
}
