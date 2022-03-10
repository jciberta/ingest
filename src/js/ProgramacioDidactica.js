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

// https://stackoverflow.com/questions/2587345/why-does-date-parse-give-incorrect-results
function parseDate(input) {
	let parts = input.split('/');
	// new Date(year, month [, day [, hours[, minutes[, seconds[, ms]]]]])
	return new Date(parts[2], parts[1]-1, parts[0]); // Note: months are 0-based
}

// https://stackoverflow.com/questions/563406/how-to-add-days-to-date
function addDays(date, days) {
//	var result = new Date(date);
	var result = date;
	result.setDate(result.getDate() + days);
	return result;
}

// https://stackoverflow.com/questions/11591854/format-date-to-mm-dd-yyyy-in-javascript
function getFormattedDate(date) {
	var year = date.getFullYear();
	var month = (1 + date.getMonth()).toString();
	month = month.length > 1 ? month : '0' + month;
	var day = date.getDate().toString();
	day = day.length > 1 ? day : '0' + day;
	return day + '/' + month + '/' + year;
}

function ProposaDatesUF() { 
console.log('-> ProposaDatesUF');
	var data;
	var hores;

	var sDataIniciCurs = document.getElementById('data_inici').value;
	var sDataFinalCurs = document.getElementById('data_final').value;
//console.log('sDataIniciCurs: '+ sDataIniciCurs);	
//console.log('sDataFinalCurs: '+ sDataFinalCurs);	

	var dtDataIniciCurs = parseDate(sDataIniciCurs);  
	var dtDataFinalCurs = parseDate(sDataFinalCurs);  
//console.log('dtDataIniciCurs: '+ dtDataIniciCurs);	
//console.log('dtDataFinalCurs: '+ dtDataFinalCurs);	

    var Temps = dtDataFinalCurs.getTime() - dtDataIniciCurs.getTime(); // Mil·lisegons
    var Dies = Temps / (1000 * 60 * 60 * 24);        
//console.log('Dies: '+ Dies);	

	var n = 0;
	var TotalHores = 0;
    $(":input[name^='edt_hores-']").each(function () {
          TotalHores += parseInt(this.value);
		  n++;
    });	
	
	var i = 1;
	var Sufix;
    $(":input[name^='edt_hores-']").each(function () {
		Sufix = (this.name).split('-');
		if (i == 1)
			$("input[type='text'][name='edd_data_inici-"+Sufix[1]+"']").val(sDataIniciCurs);
		else
			$("input[type='text'][name='edd_data_inici-"+Sufix[1]+"']").val(getFormattedDate(data));
		hores = this.value;
		data = addDays(dtDataIniciCurs, Dies/TotalHores*hores);
		if (i == n)
			$("input[type='text'][name='edd_data_final-"+Sufix[1]+"']").val(sDataFinalCurs);
		else
			$("input[type='text'][name='edd_data_final-"+Sufix[1]+"']").val(getFormattedDate(data));
		  i++;
    });	
}

function EsborraDatesUF() { 
	$(":input[name^='edd_data_inici-']").val('');
	$(":input[name^='edd_data_final-']").val('');
}
