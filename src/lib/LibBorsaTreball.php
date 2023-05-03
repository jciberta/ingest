<?php

/**
 * LibBorsaTreball.php
 * 
 * Classe per gestionar la borsa de treball.
 * 
 * @author  shad0wstv, Josep Ciberta
 * @since   1.13
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(ROOT.'/vendor/autoload.php');
require_once(ROOT.'/vendor/PHPMailer/PHPMailer.php');
require_once(ROOT.'/vendor/PHPMailer/Exception.php');
require_once(ROOT.'/vendor/PHPMailer/SMTP.php');
require_once(ROOT.'/lib/LibInet.php');
require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibUsuari.php');

class BorsaTreball extends Objecte
{
	private $EsGestorBorsa = false;

	function __construct($conn = null, $user = null, $system = null) {
		parent::__construct($conn, $user, $system);
		$Professor = new Professor($conn, $user, $system);
		$this->EsGestorBorsa = $Professor->EsGestorBorsa();
	}	

	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		echo $this->GeneraCapcalera();
		echo $this->GeneraCerca();
		echo $this->GeneraTaula();
		echo $this->GeneraModalDetallOferta();
		echo $this->GeneraModalNovaOferta();
		echo $this->GeneraModalEditaOferta();
		echo $this->GeneraPeu();
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
		$output = "<option selected hidden>Seleccioneu...</option>";
		while ($row = $resultSet->fetch_assoc()) {
			$output .= "<option value='$row[cicle_formatiu_id]'>$row[nom]</option>";
		}
		return $output;
	}

	/**
	 * Retorna la informació de totes les ofertes de la borsa de treball
	 * @return string HTML amb la informació de les ofertes
	 */
	public function CarregaOfertes(): string {
		$SQL = "
			SELECT bt.*, cf.nom AS nom_cicle, fp.nom AS nom_familia 
			FROM BORSA_TREBALL bt 
			INNER JOIN CICLE_FORMATIU cf ON bt.cicle_formatiu_id = cf.cicle_formatiu_id 
			INNER JOIN FAMILIA_FP fp ON cf.familia_fp_id = fp.familia_fp_id
			ORDER BY data_creacio DESC
		";
		$stmt = $this->Connexio->prepare($SQL);
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

	public function FiltrarOfertes($filtre): string	{
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
	public function ConsultaOferta(int $id): string	{
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
	public function DesaNovaOferta($empresa, $cicle, $contacte, $telefon, $poblacio, $correu, $descripcio, $web) {
		$web = preg_replace("/^https?:\/\//", "", $web);
		try {
			$IP = getUserIP();
			$stmt = $this->Connexio->prepare("INSERT INTO BORSA_TREBALL (cicle_formatiu_id, empresa, contacte, telefon, poblacio, email, web, descripcio, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");
			$stmt->bind_param("issssssss", $cicle, $empresa, $contacte, $telefon, $poblacio, $correu, $web, $descripcio, $IP);
			$stmt->execute();
			$stmt->close();
//			$this->enviarMailNovaOferta($empresa, $cicle, $contacte, $telefon, $poblacio, $correu, $descripcio, $web);
		} catch (Exception $e) {
			if (Config::Debug) {
				return json_encode(array("status" => "error", "message" => $e->getMessage(), "trace" => $e->getTrace()));
			}
			return json_encode(array("status" => "error", "message" => "Error en enviar el correu."));
		}
		return json_encode(array("status" => "ok"));
	}

	/**
	 * Publica una oferta de la borsa de treball a partir del seu id i notifica a tots els estudiants del cicle formatiu de l'oferta publicada per correu electrònic
	 * @param int $id Identificador de l'oferta
	 * @return string JSON amb el resultat de la operació
	 */
	public function PublicaOferta(int $id): string {
		Seguretat::ComprovaAccessUsuari($this->Usuari, ['SU', 'DI', 'CE'], $this->EsGestorBorsa);
//		if ($this->Usuari === null || !$this->EsGestorBorsa) {
//			return json_encode(array("status" => "error", "message" => "No tens permisos per realitzar aquesta acció"));
//		}

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
	public function EliminaOferta(int $id): string {
		Seguretat::ComprovaAccessUsuari($this->Usuari, ['SU', 'DI', 'CE'], $this->EsGestorBorsa);
//		if ($this->Usuari === null || !$this->EsGestorBorsa) {
//			return json_encode(array("status" => "error", "message" => "No tens permisos per realitzar aquesta acció"));
//		}
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
	private function GeneraCapcalera($Titol = 'Borsa de treball'): string {
		return GeneraIniciHTML($this->Usuari, 'Borsa de treball', $this->Usuari !== null);
	}

	private function GeneraPeu(): string {
		return '
			</div>
			<script src="js/BorsaTreball.js?v1.2"></script>
			</body>
			</html>
		';
	}

	private function GeneraCerca(): string {
		return '
			<div class="container-fluid">
				<div class="m-4 d-inline">
					<div class="row">
						<div class="col-sm-4">
							<div class="w-100 mw-50">
								<form action="#" method="post" class="cerca">
									<div class="input-group">
										<input class="form-control" type="search" name="itemName" placeholder="Nom, cicle, empresa..." aria-label="Search">
										<span class="input-group-text bg-transparent border-start-0">
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
												<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
											</svg>
										</span>
									</div>
								</form>
							</div>
						</div>
						<div class="col-sm-8 mt-sm-0 mt-3">
							<button type="button" class="btn btn-outline-primary float-end" data-bs-toggle="modal" data-bs-target="#modalNovaOferta" onclick="carregaCicles()">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
									<path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
									<path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
								</svg>
								Nova oferta
							</button>
						</div>
					</div>
				</div>
			</div>
		';
	}

	private function GeneraTaula(): string {
		return '
			<div class="container-fluid">
				<div class="rounded-2 table-responsive">
					<table id="taulaCicles" class="table table-striped table-sm table-hover">
						<thead class="thead-dark">
							<tr>
								<th>Empresa</th>
								<th>Cicle</th>
								<th>Població</th>
								<th>Data publicació</th>
								<th>Accions</th>
							</tr>
						</thead>
						<tbody id="llista-ofertes"></tbody>
					</table>
				</div>
			</div>
		';
	}

	private function GeneraModalDetallOferta(): string {
		return '
			<!-- Modal detall oferta -->
			<div class="modal fade" id="modalOferta" tabindex="-1" aria-labelledby="modalOfertaLabel">
				<div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modalOfertaLabel">Oferta</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
						  	</button>							
						</div>
						<div class="modal-body">
							<div class="container">
								<div class="card">
									<div class="card-body">
										<h4 class="card-title" id="modalOfertaEmpresa"></h4>
										<h6 class="card-subtitle mb-2 text-muted">
											<span id="modalOfertaPoblacio"></span>
											-
											<span id="modalOfertaCicle"></span>
										</h6>
										<div class="card-text">
											<h5>Contacte</h5>
											<p>
												<span id="modalOfertaTelefon"></span>
												-
												<span id="modalOfertaEmail"></span>
											</p>
										</div>
										<div class="mb-2 form-group">
											<h5>Descripció</h5>
											<textarea class="form-control" id="modalOfertaDescripcio" rows="10" readonly></textarea>
										</div>
										<a href="#" class="btn btn-primary" target="_blank" id="modalOfertaWeb">Visitar web</a>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Tanca</button>
						</div>
					</div>
				</div>
			</div>		
		';
	}

	private function GeneraModalNovaOferta(): string {
		return '
			<!-- Modal nova oferta -->
			<div class="modal fade" id="modalNovaOferta" tabindex="-1" role="dialog" aria-labelledby="modalNovaOfertaLabel">
				<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modalNovaOfertaLabel">Nova oferta</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
						  	</button>							
						</div>
						<div class="modal-body">
							<div class="container-fluid">
								<div class="alert alert-danger fade show visually-hidden" role="alert" id="modalError" style="display:none">
									<span id="modalErrorMessage"></span>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>									
								</div>
								<form>
									<div class="input-group" style="margin-bottom:40px">
										<table width=50%>
											<tr style="height:40px;vertical-align:middle;">
												<td>Cicle</td>
												<td>
													<select id="inputCicle" class="custom-select">
														<option selected hidden>Seleccioneu...</option>
													</select>
												</td>
											</tr>
										</table>
									</div>
									<div class="row">
										<div class="col-md-6">
											<label class="form-label" for="inputEmpresa">Empresa</label>
											<input type="text" class="form-control" id="inputEmpresa">
										</div>
										<div class="col-md-6">
											<label class="form-label" for="inputContacte">Contacte</label>
											<input type="text" class="form-control" id="inputContacte">
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<label class="form-label" for="inputTelefon">Telèfon</label>
											<input type="tel" class="form-control" id="inputTelefon" pattern="[0-9]{9}">
										</div>
										<div class="col-md-6">
											<label class="form-label" for="inputPoblacio">Població</label>
											<input type="text" class="form-control" id="inputPoblacio">
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<label class="form-label" for="inputCorreu">Correu</label>
											<input type="email" class="form-control" id="inputCorreu">
										</div>
										<div class="col-md-6">
											<label class="form-label" for="inputWeb">Web</label>
											<input type="text" class="form-control" id="inputWeb">
										</div>
									</div>
									<div class="form-group">
										<label class="form-label" for="inputDescripcio">Descripció</label>
										<textarea class="form-control" id="inputDescripcio" rows="6"></textarea>
									</div>
								</form>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary" id="DesaOferta" onclick="DesaNovaOferta()">
								<span id="DesaOfertaText">Desa</span>
							</button>
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelaNovaOferta()">
								Cancel·la
							</button>
						</div>
					</div>
				</div>
			</div>
		';
	}

	private function GeneraModalEditaOferta(): string {
		return '
			<!-- Modal edita oferta -->
			<div class="modal fade" id="modalEditaOferta" tabindex="-1" aria-labelledby="modalEditaOfertaLabel">
				<div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modalEditaOfertaLabel">Oferta</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
						  	</button>							
						</div>
						<div class="modal-body">
							<div class="container">
								<div class="alert alert-danger fade show visually-hidden" role="alert" id="modalEditaError" style="display:none">
									<span id="modalEditaErrorMessage"></span>
								</div>
								<div class="card">
									<div class="card-body">
										<h4 class="card-title" id="modalEditaOfertaEmpresa"></h4>
										<h6 class="card-subtitle mb-2 text-muted">
											<span id="modalEditaOfertaPoblacio"></span>
											-
											<span id="modalEditaOfertaCicle"></span>
										</h6>
										<div class="card-text">
											<h5>Contacte</h5>
											<p>
												<span id="modalEditaOfertaTelefon"></span>
												-
												<span id="modalEditaOfertaEmail"></span>
											</p>
										</div>
										<div class="mb-2 form-group">
											<h5>Descripció</h5>
											<textarea class="form-control" id="modalEditaOfertaDescripcio" rows="10" readonly></textarea>
										</div>
										<a href="#" class="btn btn-primary" target="_blank" id="modalEditaOfertaWeb">Visitar web</a>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<input hidden id="modalEditaOfertaId" />
							<button type="button" class="btn btn-secondary" data-dismiss="modal">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
									<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z" />
								</svg>
								Tanca
							</button>
							<button type="button" class="btn btn-danger" id="eliminaOferta" onclick="eliminaOferta()">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" id="modalEliminaIcon" class="bi bi-trash" viewBox="0 0 16 16">
									<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
									<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
								</svg>
								<span id="eliminarOfertaText">Suprimeix</span>
							</button>
							<button type="button" class="btn btn-success" id="publicaOferta" onclick="publicaOferta()">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" id="modalPublicaIcon" class="bi bi-box-arrow-up" viewBox="0 0 16 16">
									<path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1h-2z" />
									<path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708l3-3z" />
								</svg>
								<span id="publicaOfertaText">Publica</span>
							</button>
						</div>
					</div>
				</div>
			</div>
		';
	}

	/**
	 * Construeix la fila de la taula amb la informació de la oferta de treball tenint en compte si l'usuari està loguejat o no i si és un gestor de la borsa de treball, només mostrarà les ofertes dels anteriors 6 mesos
	 * @param array $row Array amb la informació de la oferta
	 * @return string HTML amb la informació de la oferta
	 * @see ConsultaOfertes()
	 * @see FiltrarOfertes()
	 */
	private function MostraFilaOferta(array $row): string {
		$output = "";
		$data = date_format(date_create($row["data_creacio"]), 'd/m/Y H:i:s');
		if (date($row["data_creacio"]) > date("Y-m-d H:i:s", strtotime("-6 month"))) {
			if ($this->Usuari !== null && ($this->Usuari->es_admin || $this->EsGestorBorsa)) {
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
								Visualitza
							</button>";
				if ($row["publicat"] === 0) {
					$output .= "
						<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#modalEditaOferta' onclick='mostraEditaOferta($row[borsa_treball_id])'>
							<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='align-self-start' viewBox='0 0 16 16'>
								<path d='M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z'></path>
								<path d='M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z'></path>
							</svg>
							Publica
						</button>";
				}
				$output .= "</td></tr>";
			}
			else if ($row["publicat"] === 1) {
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
								Visualitza
							</button>
						</td>
					</tr>";
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
	 * @see DesaNovaOferta()
	 * @throws Exception Si hi ha algun error al enviar el correu
	 */
	private function EnviarMailNovaOferta($empresa, $cicle, $contacte, $telefon, $poblacio, $correu, $descripcio, $web) {
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
	private function EnviarMailNovaOfertaAlumnes(array $oferta, int $usuari_id, string $emailAlumne) {
		Seguretat::ComprovaAccessUsuari($this->Usuari, ['SU', 'DI', 'CE'], $this->EsGestorBorsa);
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

	/**
	 * Genera el formulari de la recerca de la borsa de treball.
	 * @param integer $Modalitat Modalitat del formulari.
	 */
	public function EscriuFormulariRecerca($Modalitat = FormRecerca::mfLLISTA) {
		Seguretat::ComprovaAccessUsuari($this->Usuari, ['SU', 'DI', 'CE'], $this->EsGestorBorsa);
		$frm = new FormRecerca($this->Connexio, $this->Usuari, $this->Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Borsa de treball';
		$SQL = "
			SELECT bt.*, cf.nom AS nom_cicle, fp.nom AS nom_familia 
			FROM BORSA_TREBALL bt 
			INNER JOIN CICLE_FORMATIU cf ON bt.cicle_formatiu_id = cf.cicle_formatiu_id 
			INNER JOIN FAMILIA_FP fp ON cf.familia_fp_id = fp.familia_fp_id
			ORDER BY data_creacio DESC
		";
		$frm->SQL = $SQL;
		$frm->Taula = 'BORSA_TREBALL';
		$frm->ClauPrimaria = 'borsa_treball_id';
		$frm->Camps = 'empresa, nom_cicle, poblacio, data_creacio, bool:publicat';
		$frm->Descripcions = 'Empresa, Cicle, Població, Data, Publicat';

		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		array_unshift($aCicles[0] , '');
		array_unshift($aCicles[1] , 'Tots');
		$frm->Filtre->AfegeixLlista('CF.cicle_formatiu_id', 'Cicle', 100, $aCicles[0], $aCicles[1]);

		if ($this->Usuari->es_admin) {
			$frm->PermetEditar = true;
			$frm->URLEdicio = 'FPFitxa.php?accio=BorsaTreball';
			$frm->PermetAfegir = true;
			$frm->PermetSuprimir = true;
		}
		$frm->EscriuHTML();
	}
	
	/**
	 * Genera el formulari de la fitxa de la borsa de treball.
	 */
	public function EscriuFormulariFitxa() {
		Seguretat::ComprovaAccessUsuari($this->Usuari, ['SU', 'DI', 'CE'], $this->EsGestorBorsa);
		$frm = new FormFitxa($this->Connexio, $this->Usuari);
		$frm->Titol = 'Borsa de treball';
		$frm->Taula = 'BORSA_TREBALL';
		$frm->ClauPrimaria = 'borsa_treball_id';
		$frm->AutoIncrement = false;
		$frm->Id = $this->Id;
		$frm->AfegeixLookup('cicle_formatiu_id', 'Cicle formatiu', 200, 'FPRecerca.php?accio=CiclesFormatius', 'CICLE_FORMATIU', 'cicle_formatiu_id', 'nom', [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('empresa', 'Empresa', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('contacte', 'Contacte', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('poblacio', 'Població', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('telefon', 'Telèfon', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('email', 'Correu electrònic', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('web', 'Web', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixTextArea('descripcio', 'Descripció', 40, 5, [FormFitxa::offREQUERIT]);
		$frm->AfegeixCheckBox('publicat', 'Publicat');
		$frm->AfegeixText('data_creacio', 'Data creació', 50, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('ip', 'IP', 50, [FormFitxa::offNOMES_LECTURA]);
		$frm->EscriuHTML();
	}
}
