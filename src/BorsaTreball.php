<?php

/**
 * BorsaTreball.php
 * 
 * Pàgina principal de la borsa de treball
 * 
 * @author: shad0wstv
 * @version: 1.0
 * @since: 1.13
 * @license: https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */
require_once('Config.php');
require_once('lib/LibBorsaTreball.php');

$BorsaTreball = new BorsaTreball();

echo $BorsaTreball->CreaCapcalera();
?>

<div class="container-fluid mb-4">
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

<div class="container-fluid">
  <div class="rounded-2 table-responsive">
    <table id="taulaCicles" class="table table-bordered table-striped table-hover">
      <caption>
        <div class="d-flex justify-content-center" id="loading">
          <div class="spinner-grow" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </caption>
      <thead class="table-dark">
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

<!-- Modal detall oferta -->
<div class="modal fade" id="modalOferta" tabindex="-1" aria-labelledby="modalOfertaLabel">
  <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalOfertaLabel">Oferta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="spinner-border text-primary text-center" role="status" id="modalLoading">
          <span class="visually-hidden">Loading...</span>
        </div>
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
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z" />
          </svg>
          Tancar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal nova oferta -->
<div class="modal fade" id="modalNovaOferta" tabindex="-1" role="dialog" aria-labelledby="modalNovaOfertaLabel">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class=" modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNovaOfertaLabel">Nova oferta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <div class="alert alert-danger fade show visually-hidden" role="alert" id="modalError">
            <span id="modalErrorMessage"></span>
          </div>
          <form class="row g-3">
            <div class="form-group">
              <label class="form-label" for="inputCicle">Cicle</label>
              <select id="inputCicle" class="form-select">
                <option selected hidden>Escull...</option>
              </select>
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
            <div>
              <label class="form-label" for="inputDescripcio">Descripció</label>
              <textarea class="form-control" id="inputDescripcio" rows="3"></textarea>
            </div>
          </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelaNovaOferta()">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z" />
          </svg>
          Tancar
        </button>
        <button type="button" class="btn btn-primary" id="guardarOferta" onclick="guardarNovaOferta()">
          <span class="spinner-border spinner-border-sm visually-hidden" role="status" id="modalLoading" aria-hidden="true"></span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
          </svg>
          <span id="guardarOfertaText">Guardar</span>
        </button>
      </div>
    </div>
  </div>
</div>

<?php
echo $BorsaTreball->CreaFooter();
?>