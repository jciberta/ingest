/** 
 * DateUtils.js
 *
 * Utilitats per al maneig de dates.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */


// https://stackoverflow.com/questions/2587345/why-does-date-parse-give-incorrect-results
function TextAData(input) {
	let parts = input.split('/');
	// new Date(year, month [, day [, hours[, minutes[, seconds[, ms]]]]])
	return new Date(parts[2], parts[1]-1, parts[0]); // Note: months are 0-based
}

// https://stackoverflow.com/questions/11591854/format-date-to-mm-dd-yyyy-in-javascript
function DataAText(date) {
	var year = date.getFullYear();
	var month = (1 + date.getMonth()).toString();
	month = month.length > 1 ? month : '0' + month;
	var day = date.getDate().toString();
	day = day.length > 1 ? day : '0' + day;
	return day + '/' + month + '/' + year;
}

// https://stackoverflow.com/questions/563406/how-to-add-days-to-date
function AfegeixDies(date, days) {
//	var result = new Date(date);
	var result = date;
	result.setDate(result.getDate() + days);
	return result;
}

function DiesEntreDates(dtData1, dtData2) {
    // Take the difference between the dates and divide by milliseconds per day.
    // Round to nearest whole number to deal with DST.
    return Math.round((dtData2-dtData1)/(1000*60*60*24));
}

/**
 * Calcula la data final (lectiva) donada una data i els dies festius
 * @param dtDataInicial Data inicial.
 * @param iDies Número de dies lectius.
 * @param asFestius Array de festius.
 * @return string Data final.
 */
function CalculaDataFinal(dtDataInicial, iDies, asFestius) { 
	var dtData = dtDataInicial; 
	while (iDies > 0) {
		dtData = AfegeixDies(dtData, 1);
		if (asFestius.indexOf(DataAText(dtData)) == -1) 
			iDies--;
	} 
	return dtData;
}

/**
 * Arrodoneix el dia de la setmana al diumenge més pròxim de la següent manera:
 * 	- Dilluns, dimarts, dimecres: diumenge anterior.
 * 	- Dijous, divendres, dissabte: diumenge posterior.
 * @param dtData Data.
 * @return date Data arrodonida.
 */
 function ArrodoneixADiumenge(dtData) { 
	var Retorn = dtData;
	var iDia = dtData.getDay(); // 0 diumenge, 1 diluns, ...
	switch (iDia) {
		case 1: AfegeixDies(Retorn, -1); break;
		case 2: AfegeixDies(Retorn, -2); break;
		case 3: AfegeixDies(Retorn, -3); break;
		case 4: AfegeixDies(Retorn, 3); break;
		case 5: AfegeixDies(Retorn, 2); break;
		case 6: AfegeixDies(Retorn, 1); break;
	}
	return Retorn;
}
