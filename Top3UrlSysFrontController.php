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
    include_once 'controllers/front/MyUrlSysFrontController14.php';
    require_once(dirname(__FILE__) . '/../../config/config.inc.php');
    require_once(dirname(__FILE__) . '/../../init.php');
    require_once(dirname(__FILE__) . '/../../header.php');
} else {
    include_once 'controllers/front/MyUrlSysFrontController15.php';
}

include_once 'lib/includes/includes.inc.php';
include_once 'top3.php';

/**
 * Urlsys push management
 *
 */
class Top3URLSysFrontController extends Top3UrlSysModuleFrontController
{

    public static function manageUrlSys()
    {
        $payment = new Top3();

        if ($payment->isInstalled('top3')) {
            $fp = fopen('php://input', 'r');

            $rawData = stream_get_contents($fp);

            $post_data = Tools::jsonDecode($rawData, true);
            if (!is_null($post_data)) {
                $refid = $post_data['refid'];
                $top3reference = $post_data['top3reference'];
                $currentamount = $post_data['currentamount'];
                $state = $post_data['state'];
                $event = $post_data['event'];
                $checksum = $post_data['checksum'];
                $xmlparam = $post_data['xmlparam'];

                $id_cart = $xmlparam['cart_id'];
                $amount = $xmlparam['amount'];
                $secure_key = $xmlparam['secure_key'];

                $id_order = Order::getOrderByCartId($id_cart);
                $cart = new Cart($id_cart);

                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $top3 = new Top3Payment();
                } else {
                    $top3 = new Top3Payment($cart->id_shop);
                }

                $checksum_calcul = $top3->getChecksum($refid, $top3reference, $currentamount, $state, 'urlsys', $event);
                if ($checksum == $checksum_calcul) {
                    $ps_status = (int) Configuration::get($payment->state_matches[$state][1]);

                    if ($id_order === false) {
                        if ($state != 'PAYMENT_ABORTED') {
                            $payment->validateOrder(
                                (int) $cart->id,
                                $ps_status,
                                $amount,
                                $payment->displayName,
                                null,
                                '',
                                $cart->id_currency,
                                false,
                                $secure_key
                            );
                        }
                    } else {
                        $order = new Order($id_order);
                        if ($state != 'PAYMENT_ABORTED') {
                            if ($order->getCurrentState() != $ps_status) {
                                $order->setCurrentState($ps_status);
                            }
                        }
                    }
                    $id_order = Order::getOrderByCartId($id_cart);

                    if (!$payment->top3OrderExist($id_order)) {
                        $payment->insertTop3Order(
                            $id_order,
                            (int) $cart->id,
                            $top3reference,
                            $state,
                            $event,
                            $payment->displayName
                        );
                    } else {
                        $payment->updateTop3Order($id_order, $state, $event);
                    }
                    
                    Top3Logger::insertLogTop3(
                        __METHOD__ . ' : ' . __LINE__,
                        'UrlSys -> id_cart = '.$id_cart.', id_order = '.$id_order.','
                            . ' state = '.$state.', event = '.$event
                    );
                } else {
                    Top3Logger::insertLogTop3(
                        __METHOD__ . ' : ' . __LINE__,
                        'UrlSys -> id_cart = '.$id_cart.', id_order = '.$id_order.','
                            . ' erreur = checksum incorrect'
                    );
                    return false;
                }
            }
        } else {
            return;
        }
    }
}
