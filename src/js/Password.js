/** 
 * Password.js
 *
 * Accions JavaScript de suport a la llibreria de contrasenyes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Recupera la contrasenya d'un pare, mare o tutor
 */
function RecuperaPasswordTutor() { 
console.log('RecuperaPasswordTutor');
	var dni = document.getElementById('dni').value;
	var dni_tutor = document.getElementById('dni_tutor').value;
	var data_naixement = document.getElementById('data_naixement').value;

	var camps = ComprovaCamps('RecuperaPassword');
	if (camps != null) {
		$('#MissatgeCorrecte').hide();
		$('#MissatgeError').hide();
		$('#MissatgeFaltenDades').show();
	}
	else
		$.ajax( {
			type: 'POST',
			url: 'lib/LibPassword.ajax.php',
			data:{
				'accio': 'RecuperaPasswordTutor',
				'dni': dni,
				'dni_tutor': dni_tutor,
				'data_naixement': data_naixement
			},
			success: function(data) {
				//$('#debug').html(data);
				if (data != '') {
					$('#MissatgeCorrecte').html("<p>La contrasenya per a l'usuari <b>"+dni_tutor+"</b> és <b>"+data+"</b></p>"+
						"<hr><p>Retorna a la <a href='index.php' class='alert-link'>pàgina principal</a>.</p>");
					$('#MissatgeCorrecte').show();
					$('#MissatgeError').hide();
					$('#MissatgeFaltenDades').hide();
				}
				else {
					$('#MissatgeCorrecte').hide();
					$('#MissatgeError').show();
					$('#MissatgeFaltenDades').hide();
				}
			}, 
			error: function(data) {
				$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
			}
		} );
}

/**
 * Recupera la contrasenya d'un alume
 */
function RecuperaPasswordAlumne() { 
console.log('RecuperaPasswordAlumne');
	var dni = document.getElementById('dni').value;
	var data_naixement = document.getElementById('data_naixement').value;
	var telefon = document.getElementById('telefon').value;
	var municipi_naixement = document.getElementById('municipi_naixement').value;

//	$('#MissatgeCorrecte').hide();
//	$('#MissatgeError').hide();
//	$('#MissatgeFaltenDades').hide();

	var camps = ComprovaCamps('RecuperaPassword');
	if (camps != null) {
		$('#MissatgeCorrecte').hide();
		$('#MissatgeError').hide();
		$('#MissatgeFaltenDades').show();
	}
	else if (telefon.length != 9) {
		$('#MissatgeCorrecte').hide();
		$('#MissatgeError').hide();
		$('#MissatgeFaltenDades').html('El telèfon ha de tenir 9 dígits.');
		$('#MissatgeFaltenDades').show();
	}
	else
		$.ajax( {
			type: 'POST',
			url: 'lib/LibPassword.ajax.php',
			data:{
				'accio': 'RecuperaPasswordAlumne',
				'dni': dni,
				'data_naixement': data_naixement,
				'telefon': telefon,
				'municipi_naixement': municipi_naixement
			},
			success: function(data) {
				//$('#debug').html(data);
				if (data != '') {
					$('#MissatgeCorrecte').html("<p>La contrasenya per a l'usuari <b>"+dni+"</b> és <b>"+data+"</b></p>"+
						"<hr><p>Retorna a la <a href='index.php' class='alert-link'>pàgina principal</a>.</p>");
					$('#MissatgeCorrecte').show();
					$('#MissatgeError').hide();
					$('#MissatgeFaltenDades').hide();
				}
				else {
					$('#MissatgeCorrecte').hide();
					$('#MissatgeError').show();
					$('#MissatgeFaltenDades').hide();
				}
			}, 
			error: function(data) {
				$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
			}
		} );
}
