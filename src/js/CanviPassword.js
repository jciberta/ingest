/** 
 * CanviPassword.js
 *
 * Canvi de contrasenya de l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */


/**
 * ComprovaFortalesaPassword
 * Comprova la fortalesa d'un password. https://pages.nist.gov/800-63-3/sp800-63b.html
 * @param string $pwd Password a comprovar.
 * @return boolean Cert si supera la fortalesa exigida.
 */
function ComprovaFortalesaPassword(pwd) {
	return (
		(pwd.length >= 8) &&
		(pwd.match(/[a-zA-Z]/)) &&
		(pwd.match(/[0-9]/))
	);
}

/**
 * ComprovaCamps
 * Comprova els camps del canvi de contrasenya per la part client.
 */
function ComprovaCamps() { 
    if (document.CanviPassword.contrasenya1.value != document.CanviPassword.contrasenya2.value) {
        alert("Les contrasenyes són diferents.");
        return false;
    } 
	else if (!ComprovaFortalesaPassword(document.CanviPassword.contrasenya1.value)) {
        alert("La contrasenya no és prou segura. Ha de tenir una longitud mínima de 8 caràcters, i ha de contenir números i lletres.");
        return false;
	}
	else {
        return true;
    }
}

/**
 * CanviPassword
 * Canvia el password d'un usuari.
 * @param usuari_id Identificador de l'usuari.
 */
function CanviPassword(usuari_id) {
	bootbox.prompt({
		title: "Nova contrasenya",
		inputType: 'password',
		callback: function (result) {
			console.log(result);
			if (result != null) {
				if (!ComprovaFortalesaPassword(result)) {
					alert("La contrasenya no és prou segura. Ha de tenir una longitud mínima de 8 caràcters, i ha de contenir números i lletres.");
				}
				else {
					// Aquesta crida AJAX s'ha d'enviar amb HTTPS!
					$.ajax( {
						type: 'POST',
						url: 'AccionsAJAX.php',
						data:{
							'accio': 'CanviPassword',
							'usuari_id': usuari_id,
							'password': result
							},
						success: function(data) {
							$('#debug').html(data);
						}, 
						error: function (data) {
							$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
						}
					} );
				}
			}
		}
	});	
}
