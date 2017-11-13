/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

function ShowHide() {

    var div = document.getElementById('top3_log');
    if (div.style.display == "none") {
        div.style.display = "block";
    } else {
        div.style.display = "none";
    }
}
function remote(action) {

    var id_order = $("#id_order").attr('value');
    var top3_reference = $("#top3_reference").attr('value');
    var token = $("#top3_token").attr('value');
    var url_redirect = $("#url_redirect").attr('value');

    if (action == 'validatetransaction')
        msg = "Confirmez-vous la validation de la transaction ?";
    else if (action == 'canceltransaction')
        msg = "Confirmez-vous l'annulation de la transaction ?";
    else
        msg = "Confirmez-vous cette action ?";

    if (confirm(msg)) {
        $("#loader_img").show();
        $.ajax({
            url: '../modules/top3/order_manager.php',
            type: 'POST',
            dataType: "json",
            data: "id_order=" + id_order + "&top3_reference=" + top3_reference + "&token=" + token + "&action=" + action,
            cache: false,
            success: function (reponse) {
                $("#loader_img").hide();
                if (reponse.http_code == 200) {
                    alert(reponse.top3_libelle);
                    if (reponse.top3_code == 'OK')
                        document.location.href = url_redirect;
                } else
                    return false;
            }
        })
    } else {
        return false;
    }
}


function getTransactionState() {

    var id_order = $("#id_order").attr('value');
    var top3_reference = $("#top3_reference").attr('value');
    var token = $("#top3_token").attr('value');
    var url_redirect = $("#url_redirect").attr('value');
    $("#loader_img").show();

    $.ajax({
        url: '../modules/top3/order_manager.php',
        type: 'POST',
        data: "id_order=" + id_order + "&top3_reference=" + top3_reference + "&token=" + token + "&action=gettransaction",
        cache: false,
        success: function (reponse) {
            document.location.href = url_redirect;
        }
    })
}

