/**
 * @file BorsaTreball.js
 * 
 * @brief Fitxer amb les funcions JS per a la gestió de la borsa de treball
 * 
 * @version 1.0
 * 
 * @author shad0wstv
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

const cerca = document.querySelector(".cerca input"),
	formCerca = document.querySelector(".cerca")

var prevent = function preventDefault(e) {
	e.preventDefault()
}

//Prevent default submit event in order to prevent the page reload on submit
formCerca.addEventListener("submit", prevent, true)

let timeout = null

cerca.addEventListener("keyup", () => {
	clearTimeout(timeout)
	timeout = setTimeout(() => {
		if (cerca.value.length > 0) {
			$.ajax({
				url: "lib/LibBorsaTreball.ajax.php",
				type: "POST",
				dataType: "html",
				data: {
					accio: "filtrarOfertes",
					cerca: cerca.value
				},
				success: (data) => {
					if (data === "") {
						$("#llista-ofertes").html(`<tr><td colspan="5" class="text-center">No s'han trobat dades</td></tr>`)
					} else {
						$("#llista-ofertes").html(data)
					}
				},
				error: () => {
					$("#llista-ofertes").html(`<tr><td colspan="5" class="text-center">No s'han trobat dades</td></tr>`)
				},
			})
		} else {
			CarregaOfertes()
		}
	}, 500)
})

document.addEventListener("DOMContentLoaded", () => {
	CarregaOfertes()
})

function CarregaOfertes() {
console.log('-> CarregaOfertes');	
	$.ajax({
		url: "lib/LibBorsaTreball.ajax.php",
		type: "POST",
		dataType: "html",
		data: {
			accio: "CarregaOfertes"
		},
		success: (data) => {
			if (data === "") {
				$("#llista-ofertes").html(`<tr><td colspan="5" class="text-center">No s'han trobat dades</td></tr>`)
			} else {
				$("#llista-ofertes").html(data)
			}
		},
		error: (data) => {
			$("#llista-ofertes").html(`<tr><td colspan="5" class="text-center">No s'han trobat dades</td></tr>`)
		},
	})
}

function mostraOferta(id) {
	$("#modalOferta").modal("toggle");
	$.ajax({
		url: "lib/LibBorsaTreball.ajax.php",
		type: "POST",
		dataType: "json",
		data: {
			accio: "mostraOferta",
			id: id
		},
		success: (data) => {
			if (data !== null && data !== undefined && data.length > 0) {
				$("#modalOfertaEmpresa").html(data[0].empresa)
				$("#modalOfertaCicle").html(data[0].nom_cicle)
				$("#modalOfertaDescripcio").html(data[0].descripcio)
				$("#modalOfertaPoblacio").html(data[0].poblacio)
				$("#modalOfertaWeb").attr("href", `https://${data[0].web}`)
				$("#modalOfertaEmail").html(data[0].email)
				$("#modalOfertaTelefon").html(data[0].telefon)
			} else {
				$("#modalOfertaEmpresa").html("No s'ha trobat l'empresa")
				$("#modalOfertaCicle").html("No s'ha trobat el cicle")
				$("#modalOfertaDescripcio").html("No s'ha trobat la descripció")
				$("#modalOfertaPoblacio").html("No s'ha trobat la població")
				$("#modalOfertaWeb").attr("href", "#")
				$("#modalOfertaEmail").html("No s'ha trobat el correu")
				$("#modalOfertaTelefon").html("No s'ha trobat el telèfon")
			}
		},
		error: (error) => {
			console.log(JSON.parse(JSON.stringify(error)))
		},
	})
}

function carregaCicles() {
console.log('-> carregaCicles');	
	$("#modalNovaOferta").modal("toggle");
	$.ajax({
		url: "lib/LibBorsaTreball.ajax.php",
		type: "POST",
		dataType: "html",
		data: {
			accio: "carregaCicles"
		},
		success: (data) => {
			$("#inputCicle").html(data)
		},
		error: () => {
			$("#inputCicle").html(`<option selected>Seleccioni...</option>`)
		}
	})
}

function DesaNovaOferta() {
	$.ajax({
		url: "lib/LibBorsaTreball.ajax.php",
		type: "POST",
		dataType: "json",
		data: {
			accio: "DesaNovaOferta",
			empresa: $("#inputEmpresa").val(),
			cicle: $("#inputCicle").val(),
			contacte: $("#inputContacte").val(),
			telefon: $("#inputTelefon").val(),
			poblacio: $("#inputPoblacio").val(),
			correu: $("#inputCorreu").val(),
			descripcio: $("#inputDescripcio").val(),
			web: $("#inputWeb").val()
		},
		success: (data) => {
//console.dir(data);
			if (data.status == "ok") {
				$("#modalNovaOferta").modal("toggle")
				$("#modalError").hide()
				$("#inputEmpresa").val("")
				$("#inputCicle").val("")
				$("#inputContacte").val("")
				$("#inputTelefon").val("")
				$("#inputPoblacio").val("")
				$("#inputCorreu").val("")
				$("#inputDescripcio").val("")
				$("#inputWeb").val("")
				bootbox.alert({
					message: "L'oferta s'ha desat correctament i un cop revisada pel gestor de la borsa es publicarà.",
					centerVertical: true,
					locale: 'ca'
				});
				CarregaOfertes();
			} else {
				$("#modalErrorMessage").html(data.missatge)
				$("#modalError").show()
			}
		},
		error: (error) => {
			console.log(`Ajax error -> ${error}`);
			console.dir(error);
		},
	})
}

function mostraEditaOferta(id) {
console.log('-> mostraEditaOferta');	
	$("#modalEditaOferta").modal("toggle");
	$.ajax({
		url: "lib/LibBorsaTreball.ajax.php",
		type: "POST",
		dataType: "json",
		data: {
			accio: "mostraOferta",
			id: id
		},
		beforeSend: () => {
			$("#modalEditaLoading").removeClass('visually-hidden')
		},
		success: (data) => {
			if (data !== null && data.length > 0) {
				$("#modalEditaOfertaEmpresa").html(data[0].empresa)
				$("#modalEditaOfertaCicle").html(data[0].nom_cicle)
				$("#modalEditaOfertaDescripcio").html(data[0].descripcio)
				$("#modalEditaOfertaPoblacio").html(data[0].poblacio)
				$("#modalEditaOfertaWeb").attr("href", `https://${data[0].web}`)
				$("#modalEditaOfertaEmail").html(data[0].email)
				$("#modalEditaOfertaTelefon").html(data[0].telefon)
				$("#modalEditaOfertaId").val(data[0].borsa_treball_id)
			} else {
				$("#modalEditaErrorMessage").html("No s'ha trobat l'oferta")
				$("#modalEditaError").removeClass("visually-hidden")
			}
		},
		error: (error) => {
			console.log(JSON.parse(JSON.stringify(error)))
		},
		complete: () => {
			$("#modalEditaLoading").addClass('visually-hidden')
		}
	})
}

function publicaOferta() {
console.log('-> publicaOferta');	

	if ($("#modalEditaOfertaId").val() == "" || $("#modalEditaOfertaId").val() == null) {
		$("#modalEditaErrorMessage").html("No s'ha trobat l'oferta")
		$("#modalEditaError").removeClass("visually-hidden")
		return
	}

	$.ajax({
		url: "lib/LibBorsaTreball.ajax.php",
		type: "POST",
		dataType: "json",
		data: {
			accio: "publicaOferta",
			id: $("#modalEditaOfertaId").val()
		},
		success: (data) => {
			if (data.status == "ok") {
//console.dir(data);
				$("#modalEditaOferta").modal("toggle")
				CarregaOfertes()
			} else {
				$("#modalEditaErrorMessage").html(data.missatge)
				$("#modalEditaError").removeClass("visually-hidden")
			}
		},
		error: (error) => {
			console.log(JSON.parse(JSON.stringify(error)))
		}
	})
}

function eliminaOferta() {

	if ($("#modalEditaOfertaId").val() == "" || $("#modalEditaOfertaId").val() == null) {
		$("#modalEditaErrorMessage").html("No s'ha trobat l'oferta")
		$("#modalEditaError").removeClass("visually-hidden")
		return
	}

	$.ajax({
		url: "lib/LibBorsaTreball.ajax.php",
		type: "POST",
		dataType: "json",
		data: {
			accio: "eliminaOferta",
			id: $("#modalEditaOfertaId").val()
		},
		beforeSend: () => {
			$("#eliminaOferta").prop("disabled", true)
			$("#modalEliminaLoading").removeClass("visually-hidden")
			$("#modalEliminaIcon").addClass("visually-hidden")
			$("#eliminaOfertaText").html("Eliminant...")
		},
		success: (data) => {
			if (data.status == "ok") {
				$("#modalEditaOferta").modal("toggle")
				CarregaOfertes()
			} else {
				$("#modalEditaErrorMessage").html(data.missatge)
				$("#modalEditaError").removeClass("visually-hidden")
			}
		},
		error: (error) => {
			console.log(JSON.parse(JSON.stringify(error)))
		},
		complete: () => {
			$("#eliminaOferta").prop('disabled', false)
			$("#modalEliminaLoading").addClass('visually-hidden')
			$("#modalEliminaIcon").removeClass('visually-hidden')
			$("#eliminaOfertaText").html("Eliminar")
		}
	})
}

function cancelaNovaOferta() {
	$("#modalNovaOferta").modal("toggle")
	$("#modalError").addClass("visually-hidden")
	$("#inputEmpresa").val("")
	$("#inputCicle").val("")
	$("#inputContacte").val("")
	$("#inputTelefon").val("")
	$("#inputPoblacio").val("")
	$("#inputCorreu").val("")
	$("#inputDescripcio").val("")
	$("#inputWeb").val("")
}