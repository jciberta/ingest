/** 
 * Material.js
 *
 * Accions JavaScript de suport a la llibreria de material.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Valida el formulari de sortida de material.
 */
function ValidaFormSortidaMaterial() { 
console.log('-> ValidaFormSortidaMaterial');

    var UsuariId = document.forms["FormSortidaMaterial"]["lkh_usuari"].value;
    if (UsuariId == '') { 
        bootbox.alert('Cal seleccionar un usuari.');
        return false;
    }

    var bAlmenysUnCheckBox = false;
    var cbs = document.getElementsByTagName('input');
    for (var i=0; i < cbs.length; i++) {
        if (cbs[i].type == 'checkbox') {
            if (cbs[i].checked) {
                bAlmenysUnCheckBox = true;
            }
        }
    }
    if (!bAlmenysUnCheckBox) {
        bootbox.alert('Cal seleccionar almenys un material.');
        return false;
    }

    return true;
}

/**
 * Valida el formulari d'entrada  de material.
 */
function ValidaFormEntradaMaterial() { 
console.log('-> ValidaFormEntradaMaterial');

    var bAlmenysUnCheckBox = false;
    var cbs = document.getElementsByTagName('input');
    for (var i=0; i < cbs.length; i++) {
        if (cbs[i].type == 'checkbox') {
            if (cbs[i].checked) {
                bAlmenysUnCheckBox = true;
            }
        }
    }
    if (!bAlmenysUnCheckBox) {
        bootbox.alert('Cal seleccionar almenys un material.');
        return false;
    }

    return true;
}
    