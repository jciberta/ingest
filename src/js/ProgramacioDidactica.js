/** 
 * ProgramacioDidactica.js
 *
 * Accions AJAX per a la programació didàctica.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * ActualitzaTaulaResultatsAprenentatge
 * @param element Element que ha fet la crida.
 */
function ActualitzaTaulaResultatsAprenentatge(element) { 
console.log('-> ActualitzaTaulaResultatsAprenentatge');
	var sCicleFormatiuId = document.getElementById('cmb_cicle_formatiu_id').value;	
console.log('sCicleFormatiuId: ' + sCicleFormatiuId);	

	$.ajax( {
		type: 'POST',
		url: 'lib/LibProgramacioDidactica.ajax.php',
		data:{
			'accio': 'ActualitzaTaulaResultatsAprenentatge',
			'cicle_formatiu_id': sCicleFormatiuId
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
 * ActualitzaTaulaContingutsUF
 * @param element Element que ha fet la crida.
 */
function ActualitzaTaulaContingutsUF(element) { 
	console.log('-> ActualitzaTaulaContingutsUF');
		var sCicleFormatiuId = document.getElementById('cmb_cicle_formatiu_id').value;	
	console.log('sCicleFormatiuId: ' + sCicleFormatiuId);	
	
		$.ajax( {
			type: 'POST',
			url: 'lib/LibProgramacioDidactica.ajax.php',
			data:{
				'accio': 'ActualitzaTaulaContingutsUF',
				'cicle_formatiu_id': sCicleFormatiuId
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

function ProposaDatesUF() { 
console.log('-> ProposaDatesUF');
	
	var hores;

	var sDataIniciCurs = document.getElementById('data_inici').value;
	var sDataFinalCurs = document.getElementById('data_final').value;
	var sDiesFestius = document.getElementById('festius').value;
//console.log('sDataIniciCurs: '+ sDataIniciCurs);	
//console.log('sDataFinalCurs: '+ sDataFinalCurs);	

	var dtDataIniciCurs = TextAData(sDataIniciCurs);  
	var dtDataFinalCurs = TextAData(sDataFinalCurs);  
//console.log('dtDataIniciCurs: '+ dtDataIniciCurs);	
//console.log('dtDataFinalCurs: '+ dtDataFinalCurs);

	var aDiesFestius = jQuery.parseJSON(sDiesFestius);	
//console.dir(aDiesFestius);

    var Temps = dtDataFinalCurs.getTime() - dtDataIniciCurs.getTime(); // Mil·lisegons
    var Dies = Temps / (1000 * 60 * 60 * 24);        
//console.log('Dies: '+ Dies);	

	var n = 0;
	var TotalHores = 0;
    $(":input[name^='edt_hores-']").each(function () {
          TotalHores += parseInt(this.value);
		  n++;
    });	
	TotalDies = DiesEntreDates(dtDataIniciCurs, dtDataFinalCurs) - aDiesFestius.length;
		
	var i = 1;
	var Sufix;
	var dtData = dtDataIniciCurs;
    $(":input[name^='edt_hores-']").each(function () {
		Sufix = (this.name).split('-');
		if (i == 1)
			$("input[type='text'][name='edd_data_inici-"+Sufix[1]+"']").val(sDataIniciCurs);
		else
			$("input[type='text'][name='edd_data_inici-"+Sufix[1]+"']").val(DataAText(dtData));
		hores = this.value;
		dtData = CalculaDataFinal(dtData, TotalDies/TotalHores*hores, aDiesFestius);
		dtData = ArrodoneixADiumenge(dtData);
		if (i == n)
			$("input[type='text'][name='edd_data_final-"+Sufix[1]+"']").val(sDataFinalCurs);
		else
			$("input[type='text'][name='edd_data_final-"+Sufix[1]+"']").val(DataAText(dtData));
		dtData = AfegeixDies(dtData, 1);
		i++;
    });	
}

function EsborraDatesUF() { 
	$(":input[name^='edd_data_inici-']").val('');
	$(":input[name^='edd_data_final-']").val('');
}

/**
 * Envia
 * Envia la programació a l'estat corresponent.
 * @param modul_pla_estudi_id Identificador del MP.
 * @param estat Estat de la programació.
 */
function Envia(modul_pla_estudi_id, estat) { 
console.log('Envia '+modul_pla_estudi_id);
	var frm = document.getElementById('frm');
	var sFrm = frm.value;	

	$('#MissatgeCorrecte').hide();
	$('#MissatgeError').hide();
	bootbox.confirm({
		message: "Esteu segur que voleu enviar la programació?",
		buttons: {
			cancel: {
				label: 'Cancel·la'
			},
			confirm: {
				label: 'Envia',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
			if (result) {
				$.ajax( {
					type: 'POST',
					url: 'lib/LibProgramacioDidactica.ajax.php',
					data:{
						'accio': 'Envia',
						'modul_pla_estudi_id': modul_pla_estudi_id, 
						'estat': estat,
						'frm': sFrm
						},
					success: function(data) {
						i = data.indexOf('ERROR');
						if (i > -1) {
							$('#MissatgeError').html("Hi ha hagut un error en realitzar l''acció." + data);
							$('#MissatgeError').show();
						}
						else {
							$('#taula').html(data);
							//$('#MissatgeCorrecte').show();
							//$('#debug').html('Dades rebudes: '+ JSON.stringify(data));
						}
					}, 
					error: function (data) {
						$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
					}
				} );
			}
		}
	});	
}

function EnviaElaboracio(modul_pla_estudi_id, estat) { 
	Envia(modul_pla_estudi_id, 'E');
}

function EnviaDepartament(modul_pla_estudi_id, estat) { 
	Envia(modul_pla_estudi_id, 'D');
}

function EnviaCapEstudis(modul_pla_estudi_id, estat) { 
	Envia(modul_pla_estudi_id, 'T');
}

function EnviaAcceptada(modul_pla_estudi_id, estat) { 
	Envia(modul_pla_estudi_id, 'A');
}