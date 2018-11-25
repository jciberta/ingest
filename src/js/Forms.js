/** 
 * Forms.js
 *
 * Accions JavaScript de suport a la llibreria de formularis.
 */


/**
 * ActualitzaTaula
 *
 * ...
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
 * @param event Event que ha provocat la crida.
 */
function RecercaKeyPress(event) {
	if (event.keyCode === 13) {
		// Cancel the default action, if needed
		event.preventDefault();
		$('#btnRecerca').click();
	}
}

