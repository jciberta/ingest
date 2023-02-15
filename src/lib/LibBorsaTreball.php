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

  /**
   * Constructor de la classe
   * @param mysqli $conn Connexió a la base de dades
   * @param Usuari $usuari Usuari que ha fet login
   * @param Sistema $sistema Objecte amb la informació del sistema
   * @param Professor $professorGestor Objecte amb la informació del professor gestor
   */
  function __construct($conn = null, $usuari = null, $sistema = null)
  {
    parent::__construct($conn, $usuari, $sistema);
  }

  /**
   * Retorna la informació de totes les famílies de formació professional
   * @return string HTML amb la informació de les famílies
   */
  public function consultaCiclesFormatius(): string
  {
    $stmt = $this->Connexio->prepare("SELECT cf.nom, cf.cicle_formatiu_id FROM cicle_formatiu cf INNER JOIN familia_fp fp ON cf.familia_fp_id = fp.familia_fp_id;");

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
  public function consultaOfertes(): string
  {
    $stmt = $this->Connexio->prepare("SELECT bt.*, cf.nom AS nom_cicle, fp.nom AS nom_familia FROM borsa_treball bt INNER JOIN cicle_formatiu cf ON bt.cicle_formatiu_id = cf.cicle_formatiu_id INNER JOIN familia_fp fp ON cf.familia_fp_id = fp.familia_fp_id;");

    $stmt->execute();

    $resultSet = $stmt->get_result();

    $stmt->close();
    $output = "";

    while ($row = $resultSet->fetch_assoc()) {
      if ($row["publicat"] === 1) {
        $data = date_format(date_create($row["data_creacio"]), 'd/m/Y H:i:s');
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
      }
    }

    return $output;
  }

  /**
   * Retorna la informació en base a la cerca
   * @param string $filtre Text a cercar
   * @return string HTML amb la informació de les ofertes
   */

  public function filtrarOfertes($filtre): string
  {
    $stmt = $this->Connexio->prepare("SELECT bt.*, cf.nom AS nom_cicle, fp.nom AS nom_familia FROM borsa_treball bt INNER JOIN cicle_formatiu cf ON bt.cicle_formatiu_id = cf.cicle_formatiu_id INNER JOIN familia_fp fp ON cf.familia_fp_id = fp.familia_fp_id WHERE fp.nom LIKE ? OR cf.nom LIKE ? OR bt.empresa LIKE ? OR bt.contacte LIKE ? OR bt.poblacio LIKE ?;");

    $filtre = "%" . $filtre . "%";

    $stmt->bind_param("sssss", $filtre, $filtre, $filtre, $filtre, $filtre);

    $stmt->execute();

    $resultSet = $stmt->get_result();

    $stmt->close();
    $output = "";

    while ($row = $resultSet->fetch_assoc()) {
      if ($row["publicat"] === 1) {
        $data = date_format(date_create($row["data_creacio"]), 'd/m/Y H:i:s');
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
      }
    }

    return $output;
  }

  /**
   * Retorna la informació d'una oferta de la borsa de treball a partir del seu id
   * @param int $id
   * @return string JSON amb la informació de l'oferta
   */
  public function consultaOferta(int $id): string
  {
    $stmt = $this->Connexio->prepare("SELECT bt.*, cf.nom AS nom_cicle, fp.nom AS nom_familia FROM borsa_treball bt INNER JOIN cicle_formatiu cf ON bt.cicle_formatiu_id = cf.cicle_formatiu_id INNER JOIN familia_fp fp ON cf.familia_fp_id = fp.familia_fp_id WHERE borsa_treball_id = ?;");

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
  public function guardarNovaOferta($empresa, $cicle, $contacte, $telefon, $poblacio, $correu, $descripcio, $web)
  {
    try {

      $stmt = $this->Connexio->prepare("INSERT INTO borsa_treball (cicle_formatiu_id, empresa, contacte, telefon, poblacio, email, web, descripcio) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");

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
   * Crea la capçalera de la pàgina
   * @param string $Titol
   * @return string HTML amb la capçalera
   */
  public function creaCapcalera($Titol = 'Borsa de treball'): string
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
/*    $Retorn .= '<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                  <div class="container-fluid">
                    <a class="navbar-brand" href="#">InGest</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                      <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                          <a class="nav-link active" aria-current="page" href="/">Inici</a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" href="/BorsaTreball.php">Borsa Treball</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </nav>';*/
    $Retorn .= '<div class="starter-template" style="padding:20px">';
    $Retorn .= '<h1>' . $Titol . '</h1>';
    return $Retorn;
  }

  public function creaFooter()
  {
    echo "</div>" . PHP_EOL;
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>' . PHP_EOL;
    echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>' . PHP_EOL;
    echo '<script src="js/BorsaTreball.js"></script>' . PHP_EOL;
    echo '</body>' . PHP_EOL;
    echo '</html>' . PHP_EOL;
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
   * @throws Exception Si hi ha algun error al enviar el correu
   */
  private function enviarMailNovaOferta($empresa, $cicle, $contacte, $telefon, $poblacio, $correu, $descripcio, $web)
  {

    $stmt = $this->Connexio->prepare("SELECT u.email FROM usuari u INNER JOIN sistema s ON u.usuari_id = s.gestor_borsa_treball_id;");

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
}
