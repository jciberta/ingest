<?php

/**
 * Accions:
 * - carregaOfertes: Retorna la informació de totes les ofertes de la borsa de treball
 * - mostraOferta: Retorna la informació d'una oferta de la borsa de treball a partir del seu id
 * - carregaCicles: Retorna la informació de tots els cicles formatius
 * - guardaNovaOferta: Guarda una nova oferta de la borsa de treball
 * - filtraOfertes: Retorna la informació de les ofertes de la borsa de treball que compleixen els filtres
 * 
 * @author shad0wstv
 * @version 1.0
 * @since 1.13
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */


require_once('../Config.php');
require_once(ROOT . '/lib/LibBorsaTreball.php');

session_start();

$con = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);

if ($con->connect_error) {
  die("Connection failed: " . $con->connect_error);
}

$BorsaTreball = new BorsaTreball($con);

if (isset($_POST["accio"])) {
  $accio = mysqli_real_escape_string($con, $_POST["accio"]);

  switch ($accio) {
    case "carregaOfertes":
      echo $BorsaTreball->ConsultaOfertes();
      break;
    case "mostraOferta":
      MostraOferta($BorsaTreball, $con);
      break;
    case "carregaCicles":
      echo $BorsaTreball->ConsultaCiclesFormatius();
      break;
    case "guardarNovaOferta":
      GuardarNovaOferta($BorsaTreball, $con);
      break;
    case "filtrarOfertes":
      FiltrarOfertes($BorsaTreball, $con);
      break;
  }
} else {
  echo json_encode(array("error" => "No s'ha trobat l'acció"));
}

function MostraOferta($BorsaTreball, $con)
{
  if (isset($_POST["id"])) {
    $id = mysqli_real_escape_string($con, $_POST["id"]);
    echo $BorsaTreball->ConsultaOferta($id);
  } else {
    echo json_encode(array("error" => "No s'ha trobat l'id de l'oferta"));
  }
}

function GuardarNovaOferta($BorsaTreball, $con)
{
  if (isset($_POST["empresa"]) && isset($_POST["cicle"]) && isset($_POST["contacte"]) && isset($_POST["telefon"]) && isset($_POST["poblacio"]) && isset($_POST["correu"]) && isset($_POST["descripcio"]) && isset($_POST["web"])) {
    $empresa = mysqli_real_escape_string($con, $_POST["empresa"]);
    $cicle = mysqli_real_escape_string($con, $_POST["cicle"]);
    $contacte = mysqli_real_escape_string($con, $_POST["contacte"]);
    $telefon = mysqli_real_escape_string($con, $_POST["telefon"]);
    $poblacio = mysqli_real_escape_string($con, $_POST["poblacio"]);
    $correu = mysqli_real_escape_string($con, $_POST["correu"]);
    $descripcio = mysqli_real_escape_string($con, $_POST["descripcio"]);
    $web = mysqli_real_escape_string($con, $_POST["web"]);

    if (empty($empresa) || empty($cicle) || empty($contacte) || empty($telefon) || empty($poblacio) || empty($correu) || empty($descripcio) || empty($web)) {
      die(json_encode(array("error" => 404, "missatge" => "No s'han trobat totes les dades de l'oferta")));
    }

    if(!is_numeric($cicle) || !preg_match('/^[0-9]+$/', $cicle)) {
      die(json_encode(array("error" => 404, "missatge" => "No s'ha seleccionat cap cicle formatiu")));
    }

    if(!is_numeric($telefon)) {
      die(json_encode(array("error" => 404, "missatge" => "El telèfon només pot contenir números")));
    }

    if (strlen($telefon) > 9 || strlen($telefon) < 9) {
      die(json_encode(array("error" => 404, "missatge" => "El telèfon ha de tenir 9 dígits")));
    }

    if (!filter_var($correu, FILTER_VALIDATE_EMAIL)) {
      die(json_encode(array("error" => 404, "missatge" => "El correu electrònic no és vàlid")));
    }

    if(!preg_match('/[a-zA-Z0-9-\.]+\.[a-z]{2,4}/', $web)) {
      die(json_encode(array("error" => 404, "missatge" => "La web no és vàlida")));
    }

    echo $BorsaTreball->GuardarNovaOferta($empresa, $cicle, $contacte, $telefon, $poblacio, $correu, $descripcio, $web);
  } else {
    echo json_encode(array("error" => "No s'han trobat totes les dades de l'oferta"));
  }
}

function FiltrarOfertes($BorsaTreball, $con)
{
  if (isset($_POST["cerca"])) {
    $cerca = mysqli_real_escape_string($con, $_POST["cerca"]);

    echo $BorsaTreball->FiltrarOfertes($cerca);
  } else {
    echo json_encode(array("error" => "No s'han trobat totes les dades de l'oferta"));
  }
}