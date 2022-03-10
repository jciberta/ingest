/** 
 * Inet.js
 *
 * Accions JavaScript de suport a la lliberia d'utilitats d'Internet.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * MostraDadesIP
 * @param ip Adreça IP.
 */
function MostraDadesIP(ip) { 
	$.ajax( {
		type: 'POST',
		url: 'lib/LibInet.ajax.php',
		data: {
			'accio': 'MostraDadesIP',
			'ip': ip
		},
        success: function(data) {
            $('#ModalInformatiuTitol').html('Informació IP');
            $('#ModalInformatiuText').html(data);
			$('#ModalInformatiu').modal('show');
        }, 
		error: function(data) {
			$('#debug').html('Hi ha hagut un error. Dades rebudes: '+ JSON.stringify(data));
		}
    } );
}
