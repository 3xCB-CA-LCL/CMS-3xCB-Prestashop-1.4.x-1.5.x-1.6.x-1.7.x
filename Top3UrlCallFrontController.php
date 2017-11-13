<?php
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

/* Load the correct class version for PS 1.4 or PS 1.5 */
if (version_compare(_PS_VERSION_, '1.5', '<')) {
    include_once 'controllers/front/MyUrlCallFrontController14.php';
    require_once(dirname(__FILE__) . '/../../config/config.inc.php');
    require_once(dirname(__FILE__) . '/../../init.php');
    require_once(dirname(__FILE__) . '/../../header.php');
} else {
    include_once 'controllers/front/MyUrlCallFrontController15.php';
}

include_once 'lib/includes/includes.inc.php';
include_once 'top3.php';

/**
 * Urlcall push management
 *
 */
class Top3URLCallFrontController extends Top3UrlcallModuleFrontController
{

    public $ssl = true;

    public static function manageUrlCall()
    {
        $payment = new Top3();
        $cart = new Cart(Tools::getValue('cart_id'));

        if ($payment->isInstalled('top3')) {
            $amount = Tools::getValue('Montant');
            $refid = Tools::getValue('RefID');
            $top3reference = Tools::getValue('Top3Reference');
            $state = Tools::getValue('State');
            $checksum = Tools::getValue('CheckSum');
            //$securekey = Tools::getValue('secure_key');
            //$amount_ps = Tools::getValue('amount');
            $params = array();

            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                $top3 = new Top3Payment();
            } else {
                $top3 = new Top3Payment($cart->id_shop);
            }

            $checksum_calcul = $top3->getChecksum($refid, $top3reference, $amount, $state, 'urlcall');

            if ($checksum == $checksum_calcul) {
                if ($state == 'PAYMENT_STORED') {
                    //paiement accepté

                    /*if (Order::getOrderByCartId($cart->id) === false) {
                        $payment->validateOrder(
                            (int) $cart->id,
                            (int) Configuration::get('TOP3_OS_ETUDE'),
                            $amount_ps,
                            $payment->displayName,
                            null,
                            '',
                            $cart->id_currency,
                            false,
                            $securekey
                        );
                    }*/

                    $params['payment_status'] = true;
                    $params['id_order'] = Order::getOrderByCartId($cart->id);
                    $params['errors'] = null;

                    if (!$payment->top3OrderExist($params['id_order'])) {
                        $payment->insertTop3Order(
                            $params['id_order'],
                            (int) $cart->id,
                            Tools::getValue('Top3Reference'),
                            Tools::getValue('State'),
                            Tools::getValue('Event'),
                            $payment->displayName
                        );
                    }
                }

                if ($state == 'PAYMENT_KO') {
                    //refus d'autorisation bancaire après 1 (Strong)
                    //ou plusieurs tentatives (Light et y compris échecs de saisie))
                    $params['payment_status'] = false;
                    $params['id_order'] = null;
                    $params['errors'] = $state;
                }

                if ($state == 'REQUEST_KO') {
                    //Si la transaction na pas pu être créée à lorigine
                    // (flux ko, éligibilité ko, paiement déjà réalisé)
                    //et que linternaute clique sur Retour vers le marchand
                    // létat renvoyé est REQUEST_KO
                    $params['payment_status'] = false;
                    $params['id_order'] = null;
                    $params['errors'] = $state;
                }

                if ($state == 'PAYMENT_ABORTED') {
                    //Retour vers le marchand sur une page de paiement
                    //avant d'avoir eu une acceptation du paiement ou un refus du paiement
                    $params['payment_status'] = false;
                    $params['id_order'] = null;
                    $params['errors'] = $state;
                }
                
                if ($state == '') {
                    $params['payment_status'] = false;
                    $params['id_order'] = null;
                    $params['errors'] = $state;
                }
                
                Top3Logger::insertLogTop3(
                    __METHOD__ . ' : ' . __LINE__,
                    'UrlCall -> cart_id = '.$cart->id.', state = '.$state
                );
                return $params;
            } else {
                Top3Logger::insertLogTop3(
                    __METHOD__ . ' : ' . __LINE__,
                    'UrlCall -> cart_id = '.$cart->id.', erreur = checksum incorrect'
                );
                return false;
            }
        } else {
            Top3Logger::insertLogTop3(
                __METHOD__ . ' : ' . __LINE__,
                'Module top3 non installé'
            );
            return false;
        }
    }
}
