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

include_once 'lib/includes/includes.inc.php';

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');

include_once 'top3.php';

$order = new Order((int) Tools::getValue('id_order'));
$payment = new Top3();

if (version_compare(_PS_VERSION_, '1.5', '<')) {
    $top3 = new Top3Payment();
} else {
    $top3 = new Top3Payment($order->id_shop);
}

/* token security */
if (Tools::getValue('token') == Tools::getAdminToken($top3->getSiteid() . $top3->getAuthkey() . $top3->getLogin())) {
    $module = new Top3();

    //put a transaction state with remote action done by merchant
    if (Tools::getValue('action') == 'validatetransaction'
            || Tools::getValue('action') == 'canceltransaction'
            || Tools::getValue('action') == 'cancelprecedentorder') {
        $res_remote = $top3->sendRemoteControl(Tools::getValue('action'), trim(Tools::getValue('top3_reference')));
    }

    //get the last transaction state after remote control or after transaction state request
    if ((isset($res_remote['result'])
            && $res_remote['result'] == true)
            || Tools::getValue('action') == 'gettransaction') {
        $res = $top3->getTransactionByRefID(trim(Tools::getValue('top3_reference')));

        if ($payment->getCurrentStateTop3Order((int) Tools::getValue('id_order')) != $res['state']) {
            $top3_state = $res['state'];
            $ps_status = (int) Configuration::get($payment->state_matches[$top3_state][1]);
            $order->setCurrentState($ps_status);
            $payment->updateTop3Order((int) Tools::getValue('id_order'), $res['state'], $res['event']);
        }
        
        Top3Logger::insertLogTop3(
            'Order manager : ' . __LINE__,
            'Gettransaction -> id_order = '.(int) Tools::getValue('id_order').','
            . ' state = '.$res['state'].','
            . ' event = '.$res['event']
        );
    } else {
        return false;
    }

    //return message from remote control
    if (Tools::getValue('action') != 'gettransaction') {
        echo Tools::jsonEncode(array('http_code' => $res_remote['http_code'],
            'top3_code' => $res_remote['top3_code'],
            'top3_libelle' => $res_remote['top3_libelle']));
        
        Top3Logger::insertLogTop3(
            'Order manager : ' . __LINE__,
            'Remote control -> id_order = '.(int) Tools::getValue('id_order').','
            . ' http code = '.$res_remote['http_code'].','
            . ' top3 code = '.$res_remote['top3_code'].','
            . ' libelle = '.$res_remote['top3_libelle']
        );
    }
} else {
    Tools::redirect('Location: ../');
}
