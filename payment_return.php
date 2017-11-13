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

require_once '../../config/settings.inc.php';
require_once '../../config/defines.inc.php';
require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
include_once 'top3.php';

if (version_compare(_PS_VERSION_, '1.5', '<')) {
    require_once 'Top3UrlCallFrontController.php';

    $top3 = new Top3();

    /* Manage urlcall push, for PS 1.4 */
    $params = Top3URLCallFrontController::manageUrlCall();

    $payment_status = $params['payment_status'];
    $errors = $params['errors'];

    if (!is_null($params['id_order'])) {
        $order = new Order($params['id_order']);
        $cart = new Cart($order->id_cart);
        $products = $cart->getProducts();
        $amount = round($order->total_paid_real, 2);
        $total_shipping = round($order->total_shipping, 2);
    } else {
        $products = false;
        $amount = false;
        $total_shipping = false;
    }

    $top3->smarty->assign('payment_status', $payment_status);
    $top3->smarty->assign('string_errors', $errors);
    $top3->smarty->assign('amount', $amount);
    $top3->smarty->assign('products', $products);
    $top3->smarty->assign('total_shipping', $total_shipping);
    $top3->smarty->assign('path_order', __PS_BASE_URI__ . 'order.php');
    $top3->smarty->assign('path_history', __PS_BASE_URI__ . 'history.php');
    $top3->smarty->assign('path_contact', __PS_BASE_URI__ . 'contact-form.php');
    $top3->smarty->assign('modules_dir', __PS_BASE_URI__ . 'modules/');

    echo $top3->smarty->display(dirname(__FILE__) . '/views/templates/front/urlcall.tpl');
    require_once(dirname(__FILE__) . '/../../footer.php');
} else {
    Tools::redirect('Location: ../');
}
