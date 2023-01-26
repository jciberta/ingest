<?php

/** 
 * AutenticacioOath2Google.php
 *
 * Pàgina d'autenticació amb el correu de inspalamos.cat
 * https://github.com/googleapis/google-api-php-client
 * https://googleapis.github.io/google-api-php-client/main/
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibInet.php');
require_once(ROOT.'/lib/LibRegistre.php');
require_once(ROOT.'/vendor/autoload.php');

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

// Carreguem la configuració del sistema
$SQL = "SELECT * FROM SISTEMA;";
$ResultSet = $conn->query($SQL);
if ($ResultSet->num_rows == 0) 
	die("El sistema no ha estat configurat.");
$sistema = $ResultSet->fetch_object();

// Objecte per usar l'API client de Google
$google_client = new Google_Client();

// Afegim les credencials
$google_client->setClientId($sistema->google_client_id);
$google_client->setClientSecret($sistema->google_client_secret);
//$google_client->setRedirectUri($sistema->google_redirect_uri);
//$google_client->setClientId(GOOGLE_CLIENT_ID);
//$google_client->setClientSecret(GOOGLE_CLIENT_SECRET);
$google_client->setRedirectUri(GOOGLE_REDIRECT_URI);

// Àmbit del que volem obtenir informació
$google_client->addScope('email');
$google_client->addScope('profile');

session_start();

// This $_GET["code"] variable value received after user has login into their Google Account redirect 
// to PHP script then this variable value has been received
if(isset($_GET["code"])) {
	// It will Attempt to exchange a code for an valid authentication token.
	$token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);

	// This condition will check there is any error occur during geting authentication token. 
	// If there is no any error occur then it will execute if block of code
	if(!isset($token['error'])) {
		// Set the access token used for requests
		$google_client->setAccessToken($token['access_token']);

		// Store "access_token" value in $_SESSION variable for future use.
		$_SESSION['access_token'] = $token['access_token'];

		// Create Object of Google Service OAuth 2 class
		$google_service = new Google_Service_Oauth2($google_client);

		// Get user profile data from google
		$data = $google_service->userinfo->get();

		// Below you can find Get profile data and store into $_SESSION variable
		if(!empty($data['given_name']))
			$_SESSION['user_first_name'] = $data['given_name'];
		if(!empty($data['family_name']))
			$_SESSION['user_last_name'] = $data['family_name'];
		if(!empty($data['email']))
			$_SESSION['user_email_address'] = $data['email'];
		if(!empty($data['gender']))
			$_SESSION['user_gender'] = $data['gender'];
		if(!empty($data['picture']))
			$_SESSION['user_image'] = $data['picture'];
	}
}

// Si encara no està identificat a un compte de Google, apareixerà el login
if (!isset($_SESSION['access_token'])) 
	header('Location: '.$google_client->createAuthUrl());
	   
// Comprovem que l'usuari existeixi a la base de dades	
// Disponible per al professorat i alumnat que s'ha identificat un primer cop amb usuari i contrasenya
$SQL = "SELECT * FROM USUARI WHERE email_ins = ? AND (es_professor=1 OR (es_alumne=1 AND imposa_canvi_password=0)) AND NOT usuari_bloquejat=1";
$stmt = $conn->prepare($SQL);
$stmt->bind_param("s", $_SESSION['user_email_address']);
$stmt->execute();
//echo $SQL;
$ResultSet = $stmt->get_result();
if ($ResultSet->num_rows > 0) {
	$user = $ResultSet->fetch_object();
	$log = new Registre($conn, $user);
	
	// Carreguem la configuració del sistema
/*	$SQL = "SELECT * FROM SISTEMA";
	$ResultSet = $conn->query($SQL);
	if ($ResultSet->num_rows == 0) 
		die("El sistema no ha estat configurat.");
	$sistema = $ResultSet->fetch_object();*/

	// Carreguem els dies festius
	$SQL = "SELECT * FROM FESTIU ORDER BY data;";
	$ResultSet = $conn->query($SQL);
	$festiu = [];
	if ($ResultSet->num_rows > 0) {
		while($row = $ResultSet->fetch_object()) {
			array_push($festiu, MySQLAData($row->data));
		}
	}

	$_SESSION['usuari_id'] = $user->usuari_id;
	// ToDo: Seguretat a la sessió
	// https://stackoverflow.com/questions/1442177/storing-objects-in-php-session
	// https://stackoverflow.com/questions/12233406/preventing-session-hijacking
	// https://codebutler.com/2010/10/24/firesheep/
	$_SESSION['USUARI'] = serialize($user);
	$_SESSION['SISTEMA'] = serialize($sistema);
	$_SESSION['FESTIU'] = serialize($festiu);
	$_SESSION['GOOGLE_CLIENT'] = true;

//print_h($google_client);
//print_h(serialize($google_client));
//exit;
	
	$SQL = "UPDATE USUARI SET data_ultim_login = ?, ip_ultim_login = ? WHERE usuari_id = ?;";
	$stmt = $conn->prepare($SQL);
	$stmt->bind_param("ssi", date('Y-m-d H:i:s'), getUserIP(), $user->usuari_id);
	$stmt->execute();

	$log->Escriu(Registre::AUTH, 'Entrada al sistema');

	header('Location: Escriptori.php');
}
else 
{
	PaginaHTMLMissatge("Error", "L'usuari no existeix o no es pot identificar amb aquest mitjà.<br>Disponible per al professorat i alumnat que s'ha identificat un primer cop amb usuari i contrasenya.");
}

?>


