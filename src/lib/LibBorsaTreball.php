<?php

/**
 * Classe per gestionar la borsa de treball
 * 
 * 
 * @author  shad0wstv
 * @version 1.0
 * @since   1.13
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(ROOT . '/vendor/autoload.php');
require_once(ROOT . '/vendor/PHPMailer/PHPMailer.php');
require_once(ROOT . '/vendor/PHPMailer/Exception.php');
require_once(ROOT . '/vendor/PHPMailer/SMTP.php');

require_once(ROOT . '/lib/LibForms.php');


class BorsaTreball extends Objecte
{
	private $Professor;

	/**
	 * Constructor de la classe
	 * @param mysqli $conn Connexió a la base de dades
	 * @param Usuari $usuari Usuari que ha fet login
	 * @param Sistema $sistema Objecte amb la informació del sistema
	 * @param Professor $Professor Objecte amb la informació del professor loguejat
	 */
	function __construct($conn = null, $usuari = null, $sistema = null, $Professor = null)
	{
		parent::__construct($conn, $usuari, $sistema);
		$this->Professor = $Professor;
	}

	/**
	 * Retorna la informació de totes les famílies de formació professional
	 * @return string HTML amb la informació de les famílies
	 */
	public function ConsultaCiclesFormatius(): string
	{
		$stmt = $this->Connexio->prepare("SELECT cf.nom, cf.cicle_formatiu_id FROM CICLE_FORMATIU cf INNER JOIN FAMILIA_FP fp ON cf.familia_fp_id = fp.familia_fp_id;");

		$stmt->execute();

		$resultSet = $stmt->get_result();

		$stmt->close();

		$output = "<option selected hidden>Escull...</option>";

		while ($row = $resultSet->fetch_assoc()) {
			$output .= "<option value='$row[cicle_formatiu_id]'>$row[nom]</option>";
		}

		return $output;
	}

	/**
	 * Retorna la informació de totes les ofertes de la borsa de treball
	 * @return string HTML amb la informació de les ofertes
	 */
	public function ConsultaOfertes(): string
	{
		$stmt = $this->Connexio->prepare("SELECT bt.*, cf.nom AS nom_cicle, fp.nom AS nom_familia FROM BORSA_TREBALL bt INNER JOIN CICLE_FORMATIU cf ON bt.cicle_formatiu_id = cf.cicle_formatiu_id INNER JOIN FAMILIA_FP fp ON cf.familia_fp_id = fp.familia_fp_id;");

		$stmt->execute();

		$resultSet = $stmt->get_result();

		$stmt->close();
		$output = "";

		while ($row = $resultSet->fetch_assoc()) {
			$output .= $this->MostraFilaOferta($row);
		}

		return $output;
	}

	/**
	 * Retorna la informació en base a la cerca
	 * @param string $filtre Text a cercar
	 * @return string HTML amb la informació de les ofertes
	 */

	public function FiltrarOfertes($filtre): string
	{
		$stmt = $this->Connexio->prepare("SELECT bt.*, cf.nom AS nom_cicle, fp.nom AS nom_familia FROM BORSA_TREBALL bt INNER JOIN CICLE_FORMATIU cf ON bt.cicle_formatiu_id = cf.cicle_formatiu_id INNER JOIN familia_fp fp ON cf.familia_fp_id = fp.familia_fp_id WHERE fp.nom LIKE ? OR cf.nom LIKE ? OR bt.empresa LIKE ? OR bt.contacte LIKE ? OR bt.poblacio LIKE ?;");

		$filtre = "%" . $filtre . "%";

		$stmt->bind_param("sssss", $filtre, $filtre, $filtre, $filtre, $filtre);

		$stmt->execute();

		$resultSet = $stmt->get_result();

		$stmt->close();
		$output = "";

		while ($row = $resultSet->fetch_assoc()) {
			$output .= $this->MostraFilaOferta($row);
		}

		return $output;
	}

	/**
	 * Retorna la informació d'una oferta de la borsa de treball a partir del seu id
	 * @param int $id
	 * @return string JSON amb la informació de l'oferta
	 */
	public function ConsultaOferta(int $id): string
	{
		$stmt = $this->Connexio->prepare("SELECT bt.*, cf.nom AS nom_cicle, fp.nom AS nom_familia FROM borsa_treball bt INNER JOIN CICLE_FORMATIU cf ON bt.cicle_formatiu_id = cf.cicle_formatiu_id INNER JOIN FAMILIA_FP fp ON cf.familia_fp_id = fp.familia_fp_id WHERE borsa_treball_id = ?;");

		$stmt->bind_param("i", $id);

		$stmt->execute();

		$resultSet = $stmt->get_result();
		$resultSet = $resultSet->fetch_all(MYSQLI_ASSOC);

		$stmt->close();

		return json_encode($resultSet);
	}

	/**
	 * Guarda una nova oferta de la borsa de treball
	 * @param string $empresa
	 * @param int $cicle
	 * @param string $contacte
	 * @param string $telefon
	 * @param string $poblacio
	 * @param string $correu
	 * @param string $descripcio
	 * @param string $web
	 * @return string JSON amb el resultat de la operació
	 */
	public function GuardarNovaOferta($empresa, $cicle, $contacte, $telefon, $poblacio, $correu, $descripcio, $web)
	{
		$web = preg_replace("/^https?:\/\//", "", $web);
		try {

			$stmt = $this->Connexio->prepare("INSERT INTO BORSA_TREBALL (cicle_formatiu_id, empresa, contacte, telefon, poblacio, email, web, descripcio) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");

			$stmt->bind_param("isssssss", $cicle, $empresa, $contacte, $telefon, $poblacio, $correu, $web, $descripcio);

			$stmt->execute();

			$stmt->close();

			$this->enviarMailNovaOferta($empresa, $cicle, $contacte, $telefon, $poblacio, $correu, $descripcio, $web);
		} catch (Exception $e) {
			if (Config::Debug) {
				return json_encode(array("status" => "error", "message" => $e->getMessage(), "trace" => $e->getTrace()));
			}

			return json_encode(array("status" => "error", "message" => "Error al enviar el correu"));
		}

		return json_encode(array("status" => "ok"));
	}

	/**
	 * Publica una oferta de la borsa de treball a partir del seu id i notifica a tots els estudiants del cicle formatiu de l'oferta publicada per correu electrònic
	 * @param int $id Identificador de l'oferta
	 * @return string JSON amb el resultat de la operació
	 */
	public function PublicaOferta(int $id): string
	{
		if ($this->Professor === null || !$this->Professor->EsGestorBorsa()) {
			return json_encode(array("status" => "error", "message" => "No tens permisos per realitzar aquesta acció"));
		}

		try {
			$stmt = $this->Connexio->prepare("UPDATE BORSA_TREBALL SET publicat = 1 WHERE borsa_treball_id = ?;");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->close();

			$stmtOferta = $this->Connexio->prepare("SELECT bt.*, cf.nom AS nom_cicle, fp.nom AS nom_familia FROM BORSA_TREBALL bt INNER JOIN CICLE_FORMATIU cf ON bt.cicle_formatiu_id = cf.cicle_formatiu_id INNER JOIN FAMILIA_FP fp ON cf.familia_fp_id = fp.familia_fp_id WHERE bt.borsa_treball_id = ?;");
			$stmtOferta->bind_param("i", $id);
			$stmtOferta->execute();
			$resultSetOferta = $stmtOferta->get_result();
			$stmtOferta->close();
			$resultSetOferta = $resultSetOferta->fetch_assoc();

			$stmt = $this->Connexio->prepare("SELECT u.usuari_id, u.email, u.inscripcio_borsa_treball FROM USUARI u 
			INNER JOIN MATRICULA m ON u.usuari_id = m.alumne_id 
			INNER JOIN CURS c ON m.curs_id = c.curs_id 
			INNER JOIN CICLE_PLA_ESTUDI cpe ON c.cicle_formatiu_id = cpe.cicle_pla_estudi_id 
			INNER JOIN CICLE_FORMATIU cf ON cpe.cicle_formatiu_id = cf.cicle_formatiu_id 
			WHERE cf.cicle_formatiu_id = ?;");

			$stmt->bind_param("i", $resultSetOferta["cicle_formatiu_id"]);

			$stmt->execute();

			$resultSet = $stmt->get_result();

			$stmt->close();

			while ($row = $resultSet->fetch_assoc()) {
				if ($row['inscripcio_borsa_treball'] === 1) {
					$this->EnviarMailNovaOfertaAlumnes($resultSetOferta, $row['usuari_id'], $row['email']);
				}
			}
		} catch (Exception $e) {
			if (Config::Debug) {
				return json_encode(array("status" => "error", "message" => $e->getMessage(), "trace" => $e->getTrace()));
			}

			return json_encode(array("status" => "error", "message" => "Error al publicar l'oferta"));
		}

		return json_encode(array("status" => "ok"));
	}

	/**
	 * Elimina una oferta de la borsa de treball
	 * @param int $id Identificador de l'oferta
	 * @return string JSON amb el resultat de la operació
	 */
	public function EliminaOferta(int $id): string
	{
		if ($this->Professor === null || !$this->Professor->EsGestorBorsa()) {
			return json_encode(array("status" => "error", "message" => "No tens permisos per realitzar aquesta acció"));
		}

		try {
			$stmt = $this->Connexio->prepare("DELETE FROM BORSA_TREBALL WHERE borsa_treball_id = ?;");

			$stmt->bind_param("i", $id);

			$stmt->execute();

			$stmt->close();
		} catch (Exception $e) {
			if (Config::Debug) {
				return json_encode(array("status" => "error", "message" => $e->getMessage(), "trace" => $e->getTrace()));
			}

			return json_encode(array("status" => "error", "message" => "Error al eliminar l'oferta"));
		}

		return json_encode(array("status" => "ok"));
	}

	/**
	 * Crea la capçalera de la pàgina
	 * @param string $Titol
	 * @return string HTML amb la capçalera
	 */
	public function CreaCapcalera($Titol = 'Borsa de treball'): string
	{
		$Retorn = '<!doctype html>' . PHP_EOL;
		$Retorn .= '<html>' . PHP_EOL;
		$Retorn .= '<head>' . PHP_EOL;
		$Retorn .= '  <meta name="viewport" content="width=device-width, initial-scale=1">' . PHP_EOL;
		$Retorn .= '	<meta charset=UTF8>' . PHP_EOL;
		$Retorn .= '	<title>InGest</title>' . PHP_EOL;
		$Retorn .= '	    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">' . PHP_EOL;
		$Retorn .= '</head>' . PHP_EOL;
		$Retorn .= '<body>' . PHP_EOL;
		$Retorn .= '<div class="starter-template" style="padding:20px">';
		$Retorn .= '<h1>' . $Titol . '</h1>';
		return $Retorn;
	}

	public function CreaFooter()
	{
		echo "</div>" . PHP_EOL;
		echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>' . PHP_EOL;
		echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>' . PHP_EOL;
		echo '<script src="js/BorsaTreball.js"></script>' . PHP_EOL;
		echo '</body>' . PHP_EOL;
		echo '</html>' . PHP_EOL;
	}


	/**
	 * Construeix la fila de la taula amb la informació de la oferta de treball tenint en compte si l'usuari està loguejat o no i si és un gestor de la borsa de treball, només mostrarà les ofertes dels anteriors 6 mesos
	 * @param array $row Array amb la informació de la oferta
	 * @return string HTML amb la informació de la oferta
	 * @see ConsultaOfertes()
	 * @see FiltrarOfertes()
	 */
	private function MostraFilaOferta(array $row): string
	{
		$output = "";
		$data = date_format(date_create($row["data_creacio"]), 'd/m/Y H:i:s');
		if (date($row["data_creacio"]) > date("Y-m-d H:i:s", strtotime("-6 month"))) {
			if ($row["publicat"] === 1 && ($this->Professor === null || !$this->Professor->EsGestorBorsa())) {
				$output .= "
					<tr>
						<td>$row[empresa]</td>
						<td>$row[nom_cicle]</td>
						<td>$row[poblacio]</td>
						<td>$data</td>
						<td>
							<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#modalOferta' onclick='mostraOferta($row[borsa_treball_id])'>
								<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='align-self-start' viewBox='0 0 16 16'>
									<path d='M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z'></path>
									<path d='M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z'></path>
								</svg>
								Visualitzar
							</button>
						</td>
					</tr>";
			} else if ($this->Professor !== null && $this->Professor->EsGestorBorsa()) {
				$output .= "
					<tr>
						<td>$row[empresa]</td>
						<td>$row[nom_cicle]</td>
						<td>$row[poblacio]</td>
						<td>$data</td>
						<td>
							<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#modalOferta' onclick='mostraOferta($row[borsa_treball_id])'>
								<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='align-self-start' viewBox='0 0 16 16'>
									<path d='M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z'></path>
									<path d='M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z'></path>
								</svg>
								Visualitzar
							</button>";
				if ($row["publicat"] === 0) {
					$output .= "
						<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#modalEditaOferta' onclick='mostraEditaOferta($row[borsa_treball_id])'>
							<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='align-self-start' viewBox='0 0 16 16'>
								<path d='M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z'></path>
								<path d='M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z'></path>
							</svg>
							Edita
						</button>";
				}

				$output .= "</td></tr>";
			}
		}
		return $output;
	}

	/**
	 * Envia un correu amb la informació de la nova oferta de treball
	 * @param string $empresa
	 * @param int $cicle
	 * @param string $contacte
	 * @param string $telefon
	 * @param string $poblacio
	 * @param string $correu
	 * @param string $descripcio
	 * @param string $web
	 * @see GuardarNovaOferta()
	 * @throws Exception Si hi ha algun error al enviar el correu
	 */
	private function EnviarMailNovaOferta($empresa, $cicle, $contacte, $telefon, $poblacio, $correu, $descripcio, $web)
	{

		$stmt = $this->Connexio->prepare("SELECT u.email FROM USUARI u INNER JOIN SISTEMA s ON u.usuari_id = s.gestor_borsa_treball_id;");

		$stmt->execute();

		$rs = $stmt->get_result();

		$stmt->close();

		$body = "
			<h1>Nova oferta de treball</h1>
			<p>Empresa: $empresa</p>
			<p>Cicle: $cicle</p>
			<p>Contacte: $contacte</p>
			<p>Telefon: $telefon</p>
			<p>Població: $poblacio</p>
			<p>Correu: $correu</p>
			<p>Descripció: $descripcio</p>
			<p>Web: $web</p>
		";

		$mail = new PHPMailer(true);
		$mail->SMTPDebug = 0;

		$mail->Host = 'smtp.gmail.com';
		$mail->Port = 465;

		$mail->IsSMTP();
		$mail->SMTPSecure = 'ssl';
		$mail->SMTPAuth = true;

		$mail->Username = Config::Correu;
		$mail->Password = Config::PasswordCorreu;

		$mail->IsHTML(true);
		$mail->setFrom(Config::Correu, "No contesteu");

		$mail->Sender = Config::Correu;
		$mail->Subject = "Nova oferta de treball";

		$mail->isHTML(true);
		$mail->Body = $body;
		$mail->AddAddress($rs->fetch_assoc()['email']);

		if (!$mail->Send()) {
			throw new Exception("Error al enviar el correu");
		}
	}

	/**
	 * Envia un correu a l'alumne amb la informació de la nova oferta de treball
	 * @param array $oferta Oferta de treball a enviar
	 * @param int $usuari_id Id de l'usuari
	 * @param string $emailAlumne Correu de l'alumne
	 * @see PublicaOferta()
	 * @throws Exception Si hi ha algun error al enviar el correu
	 */
	private function EnviarMailNovaOfertaAlumnes(array $oferta, int $usuari_id, string $emailAlumne)
	{
		$Protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
		$URLHost = "{$Protocol}://{$_SERVER['HTTP_HOST']}";
		$token = base64_encode($this->GenerarToken($emailAlumne));
		$URL = GeneraURL("{$URLHost}/lib/LibBorsaTreball.ajax.php?accio=desubscriuBorsaTreball&usuari_id={$usuari_id}&email={$emailAlumne}&token={$token}");
		$body = "
		<html>
			<body>
				<h1>Nova oferta de treball</h1>
				<p>Empresa: {$oferta['empresa']}</p>
				<p>Cicle: {$oferta['nom_cicle']}</p>
				<p>Contacte:{$oferta['contacte']}</p>
				<p>Telefon: {$oferta['telefon']}</p>
				<p>Població: {$oferta['poblacio']}</p>
				<p>Correu: {$oferta['email']}</p>
				<p>Descripció: {$oferta['descripcio']}</p>
				<p>Web: {$oferta['web']}</p>
				<footer>
					<div class='container'>
						<div class='row'>
							<div class='col-12'>
								<p>Si no vols rebre més correus de la Borsa de Treball, <a href='$URL'>desactiva el correu</a></p>
							</div>
						</div>
					</div>
				</footer>
			</body>
		</html>
		";

		$alBody = "
			Nova oferta de treball
			Empresa: {$oferta['empresa']}
			Cicle: {$oferta['nom_cicle']}
			Contacte:{$oferta['contacte']}
			Telefon: {$oferta['telefon']}
			Població: {$oferta['poblacio']}
			Correu: {$oferta['email']}
			Descripció: {$oferta['descripcio']}
			Web: {$oferta['web']}
			";

		$mail = new PHPMailer(true);
		$mail->SMTPDebug = 0;
		$mail->IsSMTP();

		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = Config::Correu;
		$mail->Password = Config::PasswordCorreu;
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;



		$mail->setFrom(Config::Correu, "No contesteu");
		$mail->AddAddress($emailAlumne);

		$mail->isHTML(true);
		$mail->Sender = Config::Correu;
		$mail->Subject = "Nova oferta de treball";
		$mail->Body = $body;
		$mail->AltBody = $alBody;

		if (!$mail->Send()) {
			throw new Exception("Error al enviar el correu");
		}
	}

	/**
	 * Genera un token per desactivar el correu
	 * @param string $emailAlumne Correu de l'alumne
	 * @see EnviarMailNovaOfertaAlumnes()
	 * @return string Token
	 */
	private function GenerarToken($emailAlumne)
	{
		return hash_hmac('sha256', $emailAlumne, Config::Secret);
	}

	/**
	 * Dona de baixa l'alumne de la borsa de treball
	 * @param string $email Correu de l'alumne
	 * @param string $token Token de desactivació
	 * @return bool True si s'ha desactivat correctament, false si no
	 */

	public function DesubscriuBorsaTreball(string $email, string $token)
	{
		if ($this->GenerarToken($email) === base64_decode($token)) {
			try {
				$stmt = $this->Connexio->prepare("UPDATE USUARI SET inscripcio_borsa_treball = 0 WHERE email = ?;");
				$stmt->bind_param("s", $email);
				$stmt->execute();
				$stmt->close();
			} catch (Exception $e) {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}
}
