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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'top3/lib/includes/includes.inc.php';

class Top3 extends PaymentModule
{
    /* category defined by Top3 */

    public static $product_types = array(
        1 => 'Alimentation et gastronomie',
        2 => 'Auto et moto',
        3 => 'Culture et divertissements',
        4 => 'Maison et jardin',
        5 => 'Electroménager',
        6 => 'Enchères et achats groupés',
        7 => 'Fleurs et cadeaux',
        8 => 'Informatique et logiciels',
        9 => 'Santé et beauté',
        10 => 'Services aux particuliers',
        11 => 'Services aux professionnels',
        12 => 'Sport',
        13 => 'Vêtements et accessoires',
        14 => 'Voyage et tourisme',
        15 => 'Hifi, photo et vidéos',
        16 => 'Téléphonie et communication',
        17 => 'Bijoux et métaux précieux',
        18 => 'Articles et accessoires pour bébé',
        19 => 'Sonorisation et lumière'
    );
    private $carrier_types = array(
        1 => 'Retrait de la marchandise chez le marchand',
        2 => 'Utilisation d\'un réseau de points-retrait tiers (type kiala, alveol, etc.)',
        3 => 'Retrait dans un aéroport, une gare ou une agence de voyage',
        4 => 'Transporteur (La Poste, Colissimo, UPS, DHL... ou tout transporteur privé)',
        5 => 'Emission d\'un billet électronique, téléchargements',
        6 => 'Module SoColissimo',
        7 => 'Module SoColissimo Liberté',
        8 => 'Module Mondial Relay',
        9 => 'Module Ici Relais',
        10 => 'Module So Colissimo Flexibilité',
    );
    private $carrier_speeds = array(
        2 => 'Standard',
        1 => 'Express (-24h)'
    );
    private $top3_statuses = array(
        'test',
        'prod',
    );
    private $top3_waiting_statuses = array(
        'TOP3_OS_ETUDE' => 'Dossier 3XCB - Etude en cours',
    );
    private $top3_ok_statuses = array(
        'TOP3_OS_OK' => 'Dossier 3XCB accepté',
    );
    private $top3_ko_statuses = array(
        'TOP3_OS_KO' => 'Dossier 3XCB refusé',
    );
    private $top3_validated_statuses = array(
        'TOP3_OS_VALIDATION_ASKED' => 'Transaction en cours de confirmation totale',
        'TOP3_OS_PARTIAL_VALIDATION_ASKED' => 'Transaction en cours de confirmation partielle',
        'TOP3_OS_PAYMENT_VALIDATED' => 'Confirmation effectuée',
        'TOP3_OS_DEBIT_SENT' => 'Ordre débit internaute',
        'TOP3_OS_CONTRACT_SENT' => 'Contrat émis',
    );
    private $top3_canceled_statuses = array(
        'TOP3_OS_CANCELLATION_ASKED' => 'Demande d\'annulation',
        'TOP3_OS_PAYMENT_CANCELLED' => 'Annulation totale effectuée',
    );
    public $state_matches = array(
        'PAYMENT_ABORTED' => array("Paiement abandonné", 'PS_OS_CANCELED'),
        'PAYMENT_KO' => array("Paiement refusé", 'PS_OS_ERROR'),
        'PAYMENT_STORED' => array("Paiement autorisé par la banque", 'TOP3_OS_ETUDE'),
        'CONTRACT_REVIEW_IN_PROGRESS' => array("Transaction à l'étude", 'TOP3_OS_ETUDE'),
        'CONTRACT_REFUSED' => array("Dossier 3XCB refusé", 'TOP3_OS_KO'),
        'CONTRACT_ACCEPTED' => array("Dossier 3XCB accepté", 'TOP3_OS_OK'),
        'VALIDATION_ASKED' => array("Transaction en cours de confirmation totale", 'TOP3_OS_VALIDATION_ASKED'),
        'PARTIAL_VALIDATION_ASKED' => array("Transaction en cours de confirmation partielle",
            'TOP3_OS_PARTIAL_VALIDATION_ASKED'),
        'CANCELLATION_ASKED' => array("Transaction en cours d'annulation totale", 'TOP3_OS_CANCELLATION_ASKED'),
        'PAYMENT_CANCELLED' => array("Annulation totale effectuée", 'PS_OS_CANCELED'),
        'PAYMENT_VALIDATED' => array("Confirmation effectuée", 'TOP3_OS_PAYMENT_VALIDATED'),
        'DEBIT_SENT' => array("Ordre débit internaute", 'TOP3_OS_DEBIT_SENT'),
        'CONTRACT_SENT' => array("Contrat émis", 'TOP3_OS_CONTRACT_SENT'),
    );

    const TOP3_ORDER_TABLE_NAME = 'top3_order';

    public function __construct()
    {
        $this->name = 'top3';
        $this->version = '1.0.3';
        $this->tab = 'payments_gateways';
        $this->author = 'Fia-Net';
        $this->displayName = $this->l('3XCB');
        $this->description = $this->l('Pay your orders in installments by credit card');
        $this->module_key = 'aeae3687f5da29b09e51fade2945ab20';
                
        parent::__construct();

        /* Backward compatibility */
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            if (file_exists(_PS_MODULE_DIR_ . 'backwardcompatibility/backward_compatibility/backward.php')) {
                include(_PS_MODULE_DIR_ . 'backwardcompatibility/backward_compatibility/backward.php');
            } else {
                $this->warning = $this->l('In order to work properly in PrestaShop v1.4,
					the Fia-Net - 3XCB module requiers the backward compatibility module at least v0.4.') . '<br />';
                $this->warning .= $this->l('You can download this module for free here
					: http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html');
            }
        }
    }

    public function install()
    {
        //create log file
        Top3Logger::insertLogTop3(__METHOD__ . ' : ' . __LINE__, 'Installation du module');

        /** database tables creation * */
        $sqlfile = dirname(__FILE__) . '/install.sql';

        if (!file_exists($sqlfile) || !($sql = Tools::file_get_contents($sqlfile))) {
            return false;
        }

        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('TOP3_ORDER_TABLE_NAME', self::TOP3_ORDER_TABLE_NAME, $sql);

        $queries = preg_split("/;\s*[\r\n]+/", $sql);

        foreach ($queries as $query) {
            if (!Db::getInstance()->Execute(trim($query))) {
                Top3Logger::insertLogTop3(
                    __METHOD__ . ' : ' . __LINE__,
                    'Installation échouée, création base échouée : ' . Db::getInstance()->getMsgError()
                );
                return false;
            }
        }

        //waiting payment status creation
        $this->createTop3PaymentStatus($this->top3_waiting_statuses, '#4169E1', '', false, false, '', false);

        //others statuses
        $this->createTop3PaymentStatus($this->top3_canceled_statuses, '#DC143C', '', false, false, '', false);
        $this->createTop3PaymentStatus($this->top3_validated_statuses, '#4169E1', '', false, false, '', false);

        //validate green payment status creation
        $this->createTop3PaymentStatus($this->top3_ok_statuses, '#32CD32', 'payment', true, true, true, true);

        //validate red payment status creation
        $this->createTop3PaymentStatus($this->top3_ko_statuses, '#DC143C', 'order_canceled', false, true, false, true);

        //hook register
        
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return (
                parent::install()
                && $this->registerHook('newOrder')
                && $this->registerHook('paymentConfirm')
                && $this->registerHook('adminOrder')
                && $this->registerHook('header')
                && $this->registerHook('payment')
                && $this->registerHook('top')
                && $this->registerHook('backOfficeHeader')
                && $this->registerHook('paymentOptions')
            );
        } else {
            return (
                parent::install()
                    && $this->registerHook('newOrder')
                    && $this->registerHook('paymentConfirm')
                    && $this->registerHook('adminOrder')
                    && $this->registerHook('header')
                    && $this->registerHook('payment')
                    && $this->registerHook('top')
                    && $this->registerHook('backOfficeHeader')
                );
        }
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Load css and javascript files
     *
     * @param type $params
     */
    public function hookHeader()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            Tools::addCSS($this->_path . '/views/css/top3.css', 'all');
        } else {
            $this->context->controller->addCSS($this->_path . '/views/css/top3.css', 'all');
        }
    }

    public function hookbackOfficeHeader()
    {
        $html = '<link rel="stylesheet" type="text/css" href="'
        . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/top3.css" />'
                . '<script type="text/javascript" src="' . $this->_path . '/views/js/javascript.js"></script>';

        return $html;
    }

    public function getContent()
    {
        $head_msg = '';
        $error_msg = '';
        $base_url = __PS_BASE_URI__;

        //lists all categories
        $shop_categories = $this->loadProductCategories();

        //lists all carriers
        $shop_carriers = $this->loadCarriers();

        //admin shop address link and log file url
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $link_shop_setting = 'index.php?tab=AdminContact&token=' . Tools::getAdminTokenLite('AdminContact');
        } else {
            $link_shop_setting = $this->context->link->getAdminLink('AdminStores') .
                    '&token=' . Tools::getAdminTokenLite('AdminStores');
        }
        
        $path_error = $base_url . 'modules/' . $this->name . '/views/img/warning.gif';
        $path_confirmation = $base_url . 'modules/' . $this->name . '/views/img/ok.gif';

        //Get log file
        $log_content = Top3Logger::getLogContent();

        //check if form is submit
        if (Tools::isSubmit('submitSettings')) {
            //if the form is correctly saved
            if ($this->processForm()) {
            //adds a confirmation message
                $head_msg = $this->l('Configuration updated.');
            } else {
                //if errors have been encountered while validating the form
                //adds an error message informing about errors that have been encountered

                $error_msg = $this->l('Some errors have been encoutered while updating configuration.');
            }
        }


        //load submitted or default values to administration form
        $adminform_values = $this->loadAdminFormValues();


        $this->smarty->assign($adminform_values);
        $this->smarty->assign(array(
            'head_msg' => $head_msg,
            'error_msg' => $error_msg,
            'errors' => $this->_errors,
            'top3_statuses' => $this->top3_statuses,
            'shop_categories' => $shop_categories,
            'top3_product_types' => self::$product_types,
            'shop_carriers' => $shop_carriers,
            'top3_carrier_types' => $this->carrier_types,
            'top3_carrier_speeds' => $this->carrier_speeds,
            'logo_account_path' => $base_url . 'modules/' . $this->name . '/views/img/account.gif',
            'logo_categories_path' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/categories.gif',
            'logo_carriers_path' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/carriers.gif',
            'logo_display_path' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/photo.gif',
            'logo_information' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/information.png',
            'logo_warning' => $base_url . 'modules/' . $this->name . '/views/img/no.gif',
            'link_shop_setting' => $link_shop_setting,
            'log_content' => $log_content,
            'path_error' => $path_error,
            'path_confirmation' => $path_confirmation,
        ));


        return $this->display(__FILE__, '/views/templates/admin/admin.tpl');
    }

    /**
     * Load shop product categories
     *
     * @return type
     */
    private function loadProductCategories()
    {
        $categories = Category::getHomeCategories($this->context->language->id);
        $shop_categories = array();

        foreach ($categories as $category) {
            $top3_type = Tools::isSubmit('top3_' . $category['id_category'] . '_product_type') ?
                    Tools::getValue('top3_' . $category['id_category'] . '_product_type') :
                Configuration::get('TOP3_CATEGORY_' . $category['id_category'] . '');

            $shop_categories[$category['id_category']] = array(
                'name' => $category['name'],
                'top3_type' => $top3_type
            );
        }

        return $shop_categories;
    }

    /**
     * Load shop carriers
     *
     * @return type
     */
    private function loadCarriers()
    {
        $carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, ALL_CARRIERS);
        $shop_carriers = array();

        foreach ($carriers as $carrier) {
            $top3_type = Tools::isSubmit('top3_' . $carrier['id_carrier'] . '_carrier_type') ?
                    Tools::getValue('top3_' . $carrier['id_carrier'] . '_carrier_type') :
                Configuration::get('TOP3_CARRIER_TYPE_' . $carrier['id_carrier'] . '');
            $top3_speed = Tools::isSubmit('top3_' . $carrier['id_carrier'] . '_carrier_speed') ?
                    Tools::getValue('top3_' . $carrier['id_carrier'] . '_carrier_speed') :
                Configuration::get('TOP3_CARRIER_SPEED_' . $carrier['id_carrier'] . '');

            $shop_carriers[$carrier['id_carrier']] = array(
                'name' => $carrier['name'],
                'top3_type' => $top3_type,
                'top3_speed' => $top3_speed
            );
        }

        return $shop_carriers;
    }

    /**
     * Load administration form values
     *
     */
    public function loadAdminFormValues()
    {
        if (Tools::isSubmit('submitSettings')) {
            //saving all data posted
            $top3_siteid = Tools::getValue('top3_siteid');
            $top3_authkey = Tools::getValue('top3_authkey');
            $top3_status = Tools::getValue('top3_status');
            $top3_email_test = Tools::getValue('top3_email_test');
            $top3_default_product_type = Tools::getValue('top3_default_product_type');
            $top3_default_carrier_type = Tools::getValue('top3_default_carrier_type');
            $top3_default_carrier_speed = Tools::getValue('top3_default_carrier_speed');
        } else {
            //take database values or fix defaut values
            $top3_siteid = (Configuration::get('TOP3_SITEID') === false ?
                    '' : Configuration::get('TOP3_SITEID'));
            $top3_authkey = (Configuration::get('TOP3_AUTHKEY') === false ?
                    '' : Configuration::get('TOP3_AUTHKEY'));
            $top3_status = (Configuration::get('TOP3_STATUS') === false ?
                    '' : Configuration::get('TOP3_STATUS'));
            $top3_email_test = (Configuration::get('TOP3_EMAILS_TEST') === false ?
                    '' : Configuration::get('TOP3_EMAILS_TEST'));
            $top3_default_product_type = (Configuration::get('TOP3_DEFAULT_PRODUCT_TYPE') === false ?
                    '1' : Configuration::get('TOP3_DEFAULT_PRODUCT_TYPE'));
            $top3_default_carrier_type = (Configuration::get('TOP3_DEFAULT_CARRIER_TYPE') === false ?
                    '4' : Configuration::get('TOP3_DEFAULT_CARRIER_TYPE'));
            $top3_default_carrier_speed = (Configuration::get('TOP3_DEFAULT_CARRIER_SPEED') === false ?
                    '2' : Configuration::get('TOP3_DEFAULT_CARRIER_SPEED'));
        }

        $adminform_values = array(
            'top3_siteid' => Tools::safeOutput($top3_siteid),
            'top3_authkey' => Tools::safeOutput($top3_authkey),
            'top3_status' => Tools::safeOutput($top3_status),
            'top3_email_test' => Tools::safeOutput($top3_email_test),
            'top3_default_product_type' => Tools::safeOutput($top3_default_product_type),
            'top3_default_carrier_type' => Tools::safeOutput($top3_default_carrier_type),
            'top3_default_carrier_speed' => Tools::safeOutput($top3_default_carrier_speed),
        );

        //return array values for admin.tpl
        return $adminform_values;
    }

    /**
     * Save all admin settings on database
     *
     * @return boolean
     */
    private function processForm()
    {
        //if the form is valid
        if ($this->formIsValid()) {
            //global parameters update
            /** TOP3 paramaters * */
            Configuration::updateValue('TOP3_AUTHKEY', urlencode(Tools::getValue('top3_authkey')));
            Configuration::updateValue('TOP3_SITEID', Tools::getValue('top3_siteid'));
            Configuration::updateValue('TOP3_STATUS', Tools::getValue('top3_status'));
            Configuration::updateValue('TOP3_EMAILS_TEST', htmlentities(Tools::getValue('top3_email_test')));

            /** categories configuration * */
            //lists all product categories

            Configuration::updateValue(
                'TOP3_DEFAULT_PRODUCT_TYPE',
                Tools::getValue('top3_default_product_type')
            );
            $shop_categories = $this->loadProductCategories();

            foreach (array_keys($shop_categories) as $id) {
                Configuration::updateValue(
                    'TOP3_CATEGORY_' . $id . '',
                    Tools::getValue('top3_' . $id . '_product_type')
                );
            }

            /** carriers update * */
            //lists all carriers

            Configuration::updateValue(
                'TOP3_DEFAULT_CARRIER_TYPE',
                Tools::getValue('top3_default_carrier_type')
            );
            Configuration::updateValue(
                'TOP3_DEFAULT_CARRIER_SPEED',
                Tools::getValue('top3_default_carrier_speed')
            );

            $shop_carriers = $this->loadCarriers();

            foreach (array_keys($shop_carriers) as $id) {
                Configuration::updateValue(
                    'TOP3_CARRIER_TYPE_' . $id . '',
                    Tools::getValue('top3_' . $id . '_carrier_type')
                );
                Configuration::updateValue(
                    'TOP3_CARRIER_SPEED_' . $id . '',
                    Tools::getValue('top3_' . $id . '_carrier_speed')
                );
            }

            Top3Logger::insertLogTop3(
                __METHOD__ . ' : ' . __LINE__,
                'Configuration mise à jour'
            );

            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if the form is valid, false otherwise
     *
     * @return type
     */
    private function formIsValid()
    {
        //check fields form
        if (Tools::strlen(Tools::getValue('top3_siteid')) < 1) {
            $this->_errors[] = $this->l('Siteid cannot be empty');
        }

        if (Tools::strlen(Tools::getValue('top3_authkey')) < 1) {
            $this->_errors[] = $this->l('Authkey cannot be empty');
        }

        if (!preg_match('#^[0-9]+$#', Tools::getValue('top3_siteid'))) {
            $this->_errors[] = $this->l('Siteid has to be integer');
        }

        if (!in_array(Tools::getValue('top3_status'), $this->top3_statuses)) {
            $this->_errors[] = $this->l('You must give a correct status');
        }

        //check defaut product type
        if (!in_array(Tools::getValue('top3_default_product_type'), array_keys(self::$product_types))) {
            $this->_errors[] = $this->l('You must configure a valid default product type');
        }

        //check products type
        $shop_categories = $this->loadProductCategories();
        $product_type_error = false;

        foreach (array_keys($shop_categories) as $id) {
            if (!in_array(
                Tools::getValue('top3_' . $id . '_product_type'),
                array_keys(self::$product_types)
            ) && Tools::getValue('top3_' . $id . '_product_type') != 0) {
                $product_type_error = true;
            }
        }

        if ($product_type_error) {
            $this->_errors[] = $this->l('You must configure a valid product type');
        }

        //check defaut carrier type
        if (!in_array(Tools::getValue('top3_default_carrier_type'), array_keys($this->carrier_types))) {
            $this->_errors[] = $this->l('You must configure a valid default carrier type');
        }

        //check defaut carrier speed
        if (!in_array(Tools::getValue('top3_default_carrier_speed'), array_keys($this->carrier_speeds))) {
            $this->_errors[] = $this->l('You must give a correct carrier speed');
        }

        //check carrier type and carrier speed
        $shop_carriers = $this->loadCarriers();
        $carrier_type_error = false;
        $carrier_speed_error = false;
        $delivery_shop = false;
        foreach (array_keys($shop_carriers) as $id) {
            if (!in_array(
                Tools::getValue('top3_' . $id . '_carrier_type'),
                array_keys($this->carrier_types)
            ) && Tools::getValue('top3_' . $id . '_carrier_type') != 0) {
                $carrier_type_error = true;
            }

            if (!in_array(
                Tools::getValue('top3_' . $id . '_carrier_speed'),
                array_keys($this->carrier_speeds)
            )) {
                $carrier_speed_error = true;
            }


            if (Tools::getValue('top3_' . $id . '_carrier_type') == 6) {
                if (!$this->checkModuleisEnabledOrInstalled('socolissimo')) {
                    $this->_errors[] = $this->l('Invalid carrier type for carrier:') .
                            $this->l('SoColissimo module is not installed or not enabled');
                }
            }

            if (Tools::getValue('top3_' . $id . '_carrier_type') == 7) {
                if (!$this->checkModuleisEnabledOrInstalled('soliberte')) {
                    $this->_errors[] = $this->l('Invalid carrier type for carrier:') .
                            $this->l('SoColissimo Liberté module is not installed or not enabled');
                }
            }

            if (Tools::getValue('top3_' . $id . '_carrier_type') == 8) {
                if (!$this->checkModuleisEnabledOrInstalled('mondialrelay')) {
                    $this->_errors[] = $this->l('Invalid carrier type for carrier:') .
                            $this->l('Mondial Relay module is not installed or not enabled');
                }
            }
               

            if (Tools::getValue('top3_' . $id . '_carrier_type') == 9) {
                if (!$this->checkModuleisEnabledOrInstalled('icirelais')) {
                    $this->_errors[] = $this->l('Invalid carrier type for carrier:') .
                            $this->l('Ici Relais module is not installed or not enabled');
                }
            }

            if (Tools::getValue('top3_' . $id . '_carrier_type') == 10) {
                if (!$this->checkModuleisEnabledOrInstalled('soflexibilite')) {
                    $this->_errors[] = $this->l('Invalid carrier type for carrier:') .
                            $this->l('SoColissimo Flexibilité module is not installed or not enabled');
                }
            }

            if (Tools::getValue('top3_' . $id . '_carrier_type') == 1) {
                $delivery_shop = true;
            }
        }

        if ($carrier_type_error) {
            $this->_errors[] = $this->l('You must configure a valid carrier type');
        }

        if ($carrier_speed_error) {
            $this->_errors[] = $this->l('You must configure a valid carrier speed');
        }

        //check if shop address entered if selected carrier or default carrier selected is 1
        if (Tools::getValue('top3_default_carrier_type') == 1 || $delivery_shop) {
            $this->checkShopAddress();
        }

        return empty($this->_errors);
    }

    /**
     * Check if address shop is not empty
     *
     * @return boolean
     */
    public function checkShopAddress()
    {
        $check = true;
        if (Configuration::get('PS_SHOP_ADDR1') == false ||
                Configuration::get('PS_SHOP_ADDR1') == null ||
                Configuration::get('PS_SHOP_ADDR1') == '') {
            $this->_errors[] = $this->l('Shop address cannot be empty');
            $check = false;
        }

        if (Configuration::get('PS_SHOP_CITY') == false ||
                Configuration::get('PS_SHOP_CITY') == null ||
                Configuration::get('PS_SHOP_CITY') == '') {
            $this->_errors[] = $this->l('Shop city cannot be empty');
            $check = false;
        }

        if (Configuration::get('PS_SHOP_CODE') == false ||
                Configuration::get('PS_SHOP_CODE') == null ||
                Configuration::get('PS_SHOP_CODE') == '') {
            $this->_errors[] = $this->l('Shop zipcode cannot be empty');
            $check = false;
        }

        if (Configuration::get('PS_SHOP_COUNTRY') == false ||
                Configuration::get('PS_SHOP_COUNTRY') == null ||
                Configuration::get('PS_SHOP_COUNTRY') == '') {
            $this->_errors[] = $this->l('Shop country cannot be empty');
            $check = false;
        }

        if ($check == false) {
            $this->_errors[] = $this->l('You must check the address of your store');
        }

        return $check;
    }

    /**
     * return if module is enabled or installed
     *
     * @param string $module_name
     *
     */
    public function checkModuleisEnabledOrInstalled($module_name)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
        //check if module is enabled on PS 1.5
            $module_is_enabled = Module::isEnabled($module_name);
        } else {
        //check if module is enabled on PS 1.4
            $module_is_enabled = $this->checkModuleisEnabled($module_name);
        }

        return (bool) (Module::isInstalled($module_name) || $module_is_enabled);
    }

    /**
     * For Prestashop 1.4, check if module is enabled, from Module::isEnabled($module_name)
     *
     * @param string $module_name
     *
     */
    public function checkModuleisEnabled($module_name)
    {
        return (bool) Db::getInstance()->getValue(
            'SELECT `active` FROM `' . _DB_PREFIX_ .
            'module` WHERE `name` = \'' . pSQL($module_name) . '\''
        );
    }

    /**
     * Create Top3 payments status
     *
     * @param array $array
     * @param string $color
     * @param string $template
     */
    public function createTop3PaymentStatus($array, $color, $template, $invoice, $send_email, $paid, $logable)
    {
        foreach ($array as $key => $value) {
            $top3_ow_status = Configuration::get($key);
            if ($top3_ow_status === false) {
                $order_state = new OrderState();
                $order_state->id_order_state = (int) $key;
            } else {
                $order_state = new OrderState((int) $top3_ow_status);
            }

            $langs = Language::getLanguages();

            foreach ($langs as $lang) {
                $order_state->name[$lang['id_lang']] = html_entity_decode($value);
            }

            $order_state->invoice = $invoice;
            $order_state->send_email = $send_email;

            if ($template != '') {
                $order_state->template = array_fill(0, 10, $template);
            }

            if ($paid != '') {
                $order_state->paid = $paid;
            }
            $order_state->logable = $logable;
            $order_state->color = $color;
            $order_state->save();

            Configuration::updateValue($key, (int) $order_state->id);

            copy(
                dirname(__FILE__) . '/views/img/' . $key . '.gif',
                dirname(__FILE__) . '/../../img/os/' . (int) $order_state->id . '.gif'
            );
        }
    }

    /**
     * Show top3's payment on payment page
     *
     * @param type $params
     * @return boolean
     */
    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }

        $total_cart = $params['cart']->getOrderTotal(true);

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $top3 = new Top3Payment();
        } else {
            $top3 = new Top3Payment($params['cart']->id_shop);
        }
        
        
        $customer = new Customer((int) $params['cart']->id_customer);
        
        if ($top3->getStatus() == 'test') {
            $customer_mail = $customer->email;

            if (Configuration::get('TOP3_EMAILS_TEST') != '') {
                $mails_test = explode(',', str_replace(' ', '', Configuration::get('TOP3_EMAILS_TEST')));
                if (!in_array($customer_mail, $mails_test)) {
                    Top3Logger::insertLogTop3(
                        __METHOD__ . ' : ' . __LINE__,
                        'Adresse $customer_mail non autorisée à utiliser Top3 en test.'
                    );
                    Top3Logger::insertLogTop3(
                        __METHOD__ . ' : ' . __LINE__,
                        'Liste des adresses autorisées : ' . implode(', ', $mails_test)
                    );

                    return false;
                }
            }
        }

        $array_result = $top3->getEligibility($total_cart * 100, 'FRA');

        if ($array_result['result']) {
            /*$control = $this->buildXMLOrder($params['cart']->id);

            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                $urlcall = Tools::getShopDomainSsl(true, true) .
                        __PS_BASE_URI__ . 'modules/top3/payment_return.php';
                $urlsys = Tools::getShopDomainSsl(true, true) .
                        __PS_BASE_URI__ . 'modules/top3/push.php';
            } else {
                $urlcall = Context::getContext()->link->getModuleLink('top3', 'urlcall');
                $urlsys = Context::getContext()->link->getModuleLink('top3', 'urlsys');
            }

            $xml_params = new Top3XMLParams();
            $xml_params->addParam('cart_id', $params['cart']->id);
            $xml_params->addParam('amount', $total_cart);
            $xml_params->addParam('secure_key', $customer->secure_key);
            $xml_params->addParam('id_module', $this->name);
            $xml_params->addParam('shop_version', _PS_VERSION_);
            
            $link_xmlfeed = $top3->getUrlfrontline();
            $checksum = $top3->getChecksumXMLFeed($urlcall, $urlsys);*/
            
            $formValues = array();
            $formValues = $this->getXMLFeedValues($params['cart']->id);
            

            $this->smarty->assign(array(
                'xml' => $formValues['xml'],
                'xmlparam' => $formValues['xmlparam'],
                'urlsys' => $formValues['urlsys'],
                'urlcall' => $formValues['urlcall'],
                'link_xmlfeed' => $formValues['link_xmlfeed'],
                'checksum' => $formValues['checksum'],
                'logo3cb' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/3xcblogo.png',
            ));

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return $this->display(__FILE__, '/views/templates/hook/payment_short_description.tpl');
            } else {
                return $this->display(__FILE__, '/views/templates/hook/payment_short_description_1.6.tpl');
            }
        } else {
            Top3Logger::insertLogTop3(
                __METHOD__ . ' : ' . __LINE__,
                'Echec Eligibility -> cart_id = '.$params['cart']->id.', libelle erreur = '.$array_result['top3_code']
            );
            return;
        }
    }

    private function buildXMLOrder($id_cart)
    {
        $cart = new Cart($id_cart);

        $invoice_address = new Address((int) $cart->id_address_invoice);
        $delivery_address = new Address((int) $cart->id_address_delivery);
        $invoice_country = new Country((int) $invoice_address->id_country);
        $delivery_country = new Country((int) $delivery_address->id_country);
        $invoice_company = ($invoice_address->company == '' ? null : $invoice_address->company);
        $delivery_company = ($delivery_address->company == '' ? null : $delivery_address->company);

        $customer = new Customer((int) $cart->id_customer);
        $currency = new Currency((int) $cart->id_currency);

        $products = $cart->getProducts();

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $carrier_id = $cart->id_carrier;
            $carrier = new Carrier((int) $carrier_id);
            $carrier_type = Configuration::get('TOP3_CARRIER_TYPE_' . (string) $carrier->id);
            $carrier_speed = Configuration::get('TOP3_CARRIER_SPEED_' . (string) $carrier->id);
            $gender = ($customer->id_gender == 2 ? $this->l('Ms.') : $this->l('Mr.'));
            $top3 = new Top3Payment();
        } else {
            //retrieve carrier_id in delivery string option, fix for PS 1.5 with onepagecheckout
            foreach ($cart->getDeliveryOption() as $delivery_string) {
                $carrier_id = Tools::substr($delivery_string, 0, -1);
            }

            $carrier = new Carrier((int) $carrier_id);

            if (Shop::isFeatureActive()) {
                $carrier_type = Configuration::get(
                    'TOP3_CARRIER_TYPE_' . (string) $carrier->id,
                    null,
                    null,
                    $cart->id_shop
                );
                $carrier_speed = Configuration::get(
                    'TOP3_CARRIER_SPEED_' . (string) $carrier->id,
                    null,
                    null,
                    $cart->id_shop
                );
            } else {
                $carrier_type = Configuration::get('TOP3_CARRIER_TYPE_' . (string) $carrier->id);
                $carrier_speed = Configuration::get('TOP3_CARRIER_SPEED_' . (string) $carrier->id);
            }

            $customer_gender = new Gender($customer->id_gender);
            $lang_id = Language::getIdByIso('en');
            if (empty($lang_id)) {
                $lang_id = Language::getIdByIso('fr');
            }

            $gender = $this->l($customer_gender->name[$lang_id]);

            $top3 = new Top3Payment($cart->id_shop);
        }

        //if carrier type is empty, we take defaut carrier type
        if ($carrier_type == '0' || $carrier_type == '' || $carrier_type == false) {
            $carrier_type = Configuration::get('TOP3_DEFAULT_CARRIER_TYPE');
            $carrier_speed = Configuration::get('TOP3_DEFAULT_CARRIER_SPEED');
            $carrier_name = 'Transporteur';
        } else {
            $carrier_name = $carrier->name;
        }

        $control = new FianetTop3Control();

        //Address and customer invoice
        $control->createInvoiceCustomer(
            $gender,
            $invoice_address->lastname,
            $invoice_address->firstname,
            $customer->email,
            $invoice_company,
            $invoice_address->phone_mobile,
            $invoice_address->phone
        );
        
        $control->createInvoiceAddress(
            $invoice_address->address1,
            $invoice_address->postcode,
            $invoice_address->city,
            $invoice_country->iso_code,
            $invoice_address->address2
        );
        
        $order_details = $control->createOrderDetails(
            $cart->id.'-'.generateRandomRefId(5),
            $top3->getSiteid(),
            (string) $cart->getOrderTotal(true) * 100,
            $currency->iso_code,
            null,
            null,
            null,
            true
        );


        switch ($carrier_type) {
            case '2':
            case '3':
            case '5':
                //initialization of the element <utilisateur type="livraison" ...>
                $control->createDeliveryCustomer(
                    $gender,
                    $delivery_address->lastname,
                    $delivery_address->firstname,
                    null,
                    $delivery_company,
                    $delivery_address->phone_mobile,
                    $delivery_address->phone
                );
                $top3_carrier = $order_details->createCarrier($carrier_name, $carrier_type, $carrier_speed);
                break;

            //if the order is to be delivered at home: element <utilisateur type="livraison"...> has to be added
            case '4':
                $control->createDeliveryCustomer(
                    $gender,
                    $delivery_address->lastname,
                    $delivery_address->firstname,
                    null,
                    $delivery_company,
                    $delivery_address->phone_mobile,
                    $delivery_address->phone
                );
                
                $control->createDeliveryAddress(
                    $delivery_address->address1,
                    $delivery_address->postcode,
                    $delivery_address->city,
                    $delivery_country->iso_code,
                    $delivery_address->address2
                );
                $top3_carrier = $order_details->createCarrier($carrier_name, $carrier_type, $carrier_speed);
                break;

            case '5':
                $top3_carrier = $order_details->createCarrier($carrier_name, $carrier_type, $carrier_speed);
                break;

            case '6':
            case '10':
                $socolissimoinfo = $this->getSoColissimoInfo($cart->id, $carrier->external_module_name);
                //socolissimo or soflexibilite module
                $socolissimo_installed_module = Module::getInstanceByName($carrier->external_module_name);

                if ($socolissimoinfo != false) {
                    foreach ($socolissimoinfo as $info) {
                        //get socolissimo informations
                        $delivery_mode = $info['delivery_mode'];
                        $firstname = $info['prfirstname'];
                        $name = $info['prname'];
                        $mobile_phone = $info['cephonenumber'];
                        $address1 = $info['pradress1'];
                        $address2 = $info['pradress2'];
                        $address3 = $info['pradress3'];
                        $address4 = $info['pradress4'];
                        $zipcode = $info['przipcode'];
                        $city = $info['prtown'];
                        $country = $info['cecountry'];
                    }

                    //if delivery mode is DOM or RDV,
                    // <adresse type="livraison" ...> and <utilisateur type="livraison" ...> added
                    if ($delivery_mode == 'DOM' || $delivery_mode == 'RDV') {
                        if ($delivery_mode == 'DOM') {
                            $control->createDeliveryCustomer(
                                $gender,
                                $delivery_address->lastname,
                                $delivery_address->firstname,
                                null,
                                $delivery_company,
                                $delivery_address->phone_mobile,
                                $delivery_address->phone
                            );
                        } else {
                            if ($invoice_address->firstname != $firstname) {
                                $control->createDeliveryCustomer(
                                    $gender,
                                    $delivery_address->lastname,
                                    $delivery_address->firstname,
                                    null,
                                    null,
                                    $delivery_address->phone_mobile,
                                    null
                                );
                            }
                        }

                        $control->createDeliveryAddress($address3, $zipcode, $city, $country, $address4);
                        
                        $top3_carrier = $order_details->createCarrier($carrier_name, '4', $carrier_speed);
                    } else {
                        //<pointrelais> added if delivery mode is not BPR, A2P or CIT
                        
                        $top3_carrier = $order_details->createCarrier($carrier_name, '2', $carrier_speed);
                        $drop_off_point_address = $control->createAddress(
                            null,
                            $address1,
                            $zipcode,
                            $city,
                            $country,
                            null
                        );
                        $top3_carrier->createDropOffPoint($address1, null, $drop_off_point_address);
                    }
                } else {
                    $control->createDeliveryCustomer(
                        $gender,
                        $delivery_address->lastname,
                        $delivery_address->firstname,
                        null,
                        $delivery_company,
                        $delivery_address->phone_mobile,
                        $delivery_address->phone
                    );
                    
                    $control->createDeliveryAddress(
                        $delivery_address->address1,
                        $delivery_address->postcode,
                        $delivery_address->city,
                        $delivery_country->iso_code,
                        $delivery_address->address2
                    );

                    //xml <infocommande>
                    $top3_carrier = $order_details->createCarrier($carrier_name, 4, $carrier_speed);
                }

                break;

            case '7':
                $socolissimoinfo = $this->getSoColissimoLiberteInfo($cart->id);
                $socolissimo_installed_module = Module::getInstanceByName('soliberte');

                if ($socolissimoinfo != false) {
                    foreach ($socolissimoinfo as $info) {
                        if (version_compare($socolissimo_installed_module->version, '4.2.03', '<')) {
                            $delivery_mode = $info['type'];
                            $firstname = $info['firstname'];
                            $name = $info['lastname'];
                            if ($info['telephone'] != null &&
                                    $info['telephone'] != '' &&
                                    $info['telephone'] != '0000000000') {
                                $mobile_phone = $info['telephone'];
                            }
                            $address1 = $info['adresse1'];
                            $address2 = $info['adresse2'];
                            $enseigne = $info['libelle'];
                            $zipcode = $info['code_postal'];
                            $city = $info['commune'];
                            $country = 'FR';
                        } else {
                            $delivery_mode = $info['delivery_mode'];
                            $firstname = $info['prfirstname'];
                            $name = $info['prname'];
                            $mobile_phone = $info['cephonenumber'];
                            $address1 = $info['pradress1'];
                            $address2 = $info['pradress2'];
                            $address3 = $info['pradress3'];
                            $address4 = $info['pradress4'];
                            $zipcode = $info['przipcode'];
                            $city = $info['prtown'];
                            $country = 'FR';
                            $enseigne = $name;
                        }
                    }

                    //if delivery mode is DOM or RDV,
                    //<adresse type="livraison" ...> and <utilisateur type="livraison" ...> added
                    if ($delivery_mode == 'DOM' || $delivery_mode == 'RDV') {
                        if ($delivery_mode == 'DOM') {
                            $control->createDeliveryCustomer(
                                $gender,
                                $delivery_address->lastname,
                                $delivery_address->firstname,
                                null,
                                $delivery_company,
                                $delivery_address->phone_mobile,
                                $delivery_address->phone
                            );
                        } else {
                            if ($invoice_address->firstname != $firstname) {
                                $control->createDeliveryCustomer(
                                    $gender,
                                    $name,
                                    $firstname,
                                    null,
                                    null,
                                    $mobile_phone,
                                    null
                                );
                            }
                        }

                        $control->createDeliveryAddress($address1, $zipcode, $city, $country, $address2);
                        $top3_carrier = $order_details->createCarrier($carrier_name, '4', $carrier_speed);
                    } else {
                        if ($invoice_address->firstname != $firstname) {
                            $control->createDeliveryCustomer(
                                $gender,
                                $name,
                                $firstname,
                                null,
                                null,
                                $mobile_phone,
                                null
                            );
                        }

                        //<pointrelais> added if delivery mode is not BPR, A2P or CIT
                        
                        $top3_carrier = $order_details->createCarrier($carrier_name, '2', $carrier_speed);
                        $drop_off_point_address = $control->createAddress(
                            null,
                            $address1,
                            $zipcode,
                            $city,
                            $country,
                            $address2
                        );
                        $top3_carrier->createDropOffPoint($enseigne, null, $drop_off_point_address);
                    }
                } else {
                    $control->createDeliveryCustomer(
                        $gender,
                        $delivery_address->lastname,
                        $delivery_address->firstname,
                        null,
                        $delivery_company,
                        $delivery_address->phone_mobile,
                        $delivery_address->phone
                    );
                    $control->createDeliveryAddress(
                        $delivery_address->address1,
                        $delivery_address->postcode,
                        $delivery_address->city,
                        $delivery_country->iso_code,
                        $delivery_address->address2
                    );

                    //xml <infocommande>
                    $top3_carrier = $order_details->createCarrier($carrier_name, '4', $carrier_speed);
                }

                break;

            case '8':
                $control->createDeliveryCustomer(
                    $gender,
                    $delivery_address->lastname,
                    $delivery_address->firstname,
                    '',
                    $delivery_company,
                    $delivery_address->phone_mobile,
                    $delivery_address->phone
                );
                $mondialrelayinfo = $this->getMondialRelayInfo($cart->id);

                if ($mondialrelayinfo != false) {
                    foreach ($mondialrelayinfo as $info) {
                        //get mondialrelay information
                        $address1 = trim($info['MR_Selected_LgAdr1']);
                        $address2 = trim($info['MR_Selected_LgAdr2']);
                        $address3 = trim($info['MR_Selected_LgAdr3']);
                        $address4 = trim($info['MR_Selected_LgAdr4']);
                        $zipcode = trim($info['MR_Selected_CP']);
                        $city = trim($info['MR_Selected_Ville']);
                        $country = trim($info['MR_Selected_Pays']);
                        $delivery_mode = trim($info['dlv_mode']);
                    }
                    //<pointrelais>
                    if ($delivery_mode == '24R' || $delivery_mode == 'DRI') {
                        $top3_carrier = $order_details->createCarrier($carrier_name, '2', $carrier_speed);
                        $drop_off_point_address = $control->createAddress(
                            '',
                            $address3,
                            $zipcode,
                            $city,
                            $country,
                            null
                        );
                        $top3_carrier->createDropOffPoint($address1, null, $drop_off_point_address);
                    } else {
                        $control->createDeliveryAddress(
                            $delivery_address->address1,
                            $delivery_address->postcode,
                            $delivery_address->city,
                            $delivery_country->iso_code,
                            $delivery_address->address2
                        );
                        $top3_carrier = $order_details->createCarrier($carrier_name, '4', $carrier_speed);
                    }
                }

                break;

            case '9':
                $control->createDeliveryCustomer(
                    $gender,
                    $delivery_address->lastname,
                    $delivery_address->firstname,
                    null,
                    $delivery_company,
                    $delivery_address->phone_mobile,
                    $delivery_address->phone
                );
                
                $icirelaisinfo = $this->getIciRelaisInfo($cart->id);
                if ($icirelaisinfo != false) {
                    foreach ($icirelaisinfo as $info) {
                        //get mondialrelay information
                        $address1 = $info['address1'];
                        $address2 = $info['address2'];
                        $enseigne = $info['company'];
                        $zipcode = $info['postcode'];
                        $city = $info['city'];
                        $country = $info['iso_code'];
                    }
                    //<pointrelais>
                    $top3_carrier = $order_details->createCarrier($carrier_name, '2', $carrier_speed);
                    $drop_off_point_address = $control->createAddress('', $address1, $zipcode, $city, $country, null);
                    $top3_carrier->createDropOffPoint($enseigne, null, $drop_off_point_address);
                }

                break;

            default:
                $control->createDeliveryCustomer(
                    $gender,
                    $delivery_address->lastname,
                    $delivery_address->firstname,
                    null,
                    $delivery_company,
                    $delivery_address->phone_mobile,
                    $delivery_address->phone
                );
                
                $top3_carrier = $order_details->createCarrier($carrier_name, $carrier_type, $carrier_speed);

                if ($carrier_type == 1) {
                    if ($this->checkShopAddress() == true) {
                        //xml <pointrelais>
                        $drop_off_point_address = $control->createAddress(
                            null,
                            Configuration::get('PS_SHOP_ADDR1'),
                            Configuration::get('PS_SHOP_CODE'),
                            Configuration::get('PS_SHOP_CITY'),
                            Configuration::get('PS_SHOP_COUNTRY'),
                            Configuration::get('PS_SHOP_ADDR2')
                        );
                        
                        $top3_carrier->createDropOffPoint(
                            Configuration::get('PS_SHOP_NAME'),
                            Configuration::get('PS_SHOP_NAME'),
                            $drop_off_point_address
                        );
                    } else {
                        //xml <pointrelais>
                        $drop_off_point_address = $control->createAddress(
                            null,
                            $delivery_address->address1,
                            $delivery_address->postcode,
                            $delivery_address->city,
                            $invoice_country->iso_code,
                            $delivery_address->address2
                        );
                        
                        $top3_carrier->createDropOffPoint($carrier_name, $carrier_name, $drop_off_point_address);
                    }
                } else {
                    //xml <pointrelais>
                    $drop_off_point_address = $control->createAddress(
                        null,
                        $delivery_address->address1,
                        $delivery_address->postcode,
                        $delivery_address->city,
                        $invoice_country->iso_code,
                        $delivery_address->address2
                    );
                    $top3_carrier->createDropOffPoint($carrier_name, $carrier_name, $drop_off_point_address);
                }

                break;
        }

        //xml <list>
        $product_list = $order_details->createProductList();

        foreach ($products as $product) {
            $top3_categorie_id = (Configuration::get('TOP3_CATEGORY_' . (int) $product['id_category_default']) == 0 ?
                            Configuration::get('TOP3_DEFAULT_PRODUCT_TYPE') :
                            Configuration::get('TOP3_CATEGORY_' . (int) $product['id_category_default']));
            $product_reference = ((isset($product['reference']) && !empty($product['reference'])) ?
                    $product['reference'] : ((isset($product['ean13']) && !empty($product['ean13'])) ?
                    $product['ean13'] : $product['name']));

            $product_name = str_replace(
                array('&', "'", 'Ø', '|', '®', '™', '©'),
                array('', '', '', '', '', '', ''),
                $product['name']
            );

            $product_reference = str_replace(
                array('&', "'", 'Ø', '|', '®', '™', '©'),
                array('', '', '', '', '', '', ''),
                $product_reference
            );

            $product_list->createProduct(
                $product_name,
                $product_reference,
                $top3_categorie_id,
                $product['price'],
                $product['cart_quantity']
            );
        }

        $top3_tag = $control->createTop3();
        $top3_tag->addDatelivr($top3->generateDatelivr(date('Y-m-d H:i:s'), 0));
        $top3_tag->addCrypt($top3->generateCrypt($control));

        return $control;
    }

    public function top3OrderExist($id_order)
    {
        $sql = 'SELECT `id_order` '
                . 'FROM `' . _DB_PREFIX_ . self::TOP3_ORDER_TABLE_NAME . '` '
                . 'WHERE `id_order`= ' . (int) $id_order;
        return (Db::getInstance()->getRow($sql));
    }

    public function insertTop3Order($id_order, $id_cart, $top3reference, $status, $event, $payment_type)
    {

        $sql = 'INSERT INTO `' .
                _DB_PREFIX_ . self::TOP3_ORDER_TABLE_NAME . '`
				(`id_order`,`id_cart`, `top3_reference`, `state`, `event`, `date`, `payment_type`) 
			VALUES (' . (int) $id_order . ', ' . (int) $id_cart . ", '" .
                pSQL($top3reference) . "', '" . pSQL($status) . "','" . pSQL($event) . "', "
                . " '" . pSQL(date('d-m-Y H:i:s')) . "', '" . pSQL($payment_type) . "')";
        return(Db::getInstance()->execute($sql));
    }

    public function updateTop3Order($id_order, $status, $event)
    {

        $sql = 'UPDATE `' . _DB_PREFIX_ . self::TOP3_ORDER_TABLE_NAME . '` '
                . 'SET `state` = "' . pSQL($status) . '", `event` = "' . pSQL($event) . '"'
                . 'WHERE `id_order` = ' . (int) $id_order;

        return(Db::getInstance()->execute($sql));
    }

    /**
     * Show Top3 evaluation status on detail order if top order table contains orders
     *
     * @param type $params
     * @return boolean
     */
    public function hookAdminOrder($params)
    {
        $id_order = $params['id_order'];
        $info_order = $this->getInfoTop3Order($id_order);
        $order = new Order((int) $id_order);

        if (!$info_order === false) {
            foreach ($info_order as $info) {
                $top3_reference = $info['top3_reference'];
                $top3_state = $info['state'];
                $top3_event = $info['event'];
                $top3_date = $info['date'];
            }

            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                $top3 = new Top3Payment();
                $admin_dir = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ .
                        Tools::substr(_PS_ADMIN_DIR_, strrpos(_PS_ADMIN_DIR_, '/') + 1);
                $url_redirect = $admin_dir . '/index.php?tab=AdminOrders&viewordertoken=' .
                        Tools::getAdminTokenLite('AdminOrders');

                $url_redirect = 'index.php?tab=AdminOrders&id_order=' .
                        $id_order . '&vieworder&token=' .
                        Tools::getAdminTokenLite('AdminOrders');
            } else {
                $top3 = new Top3Payment($order->id_shop);
                $link = new Link();
                $url_redirect = $link->getAdminLink('AdminOrders') .
                        '&id_order=' . Tools::getValue('id_order') . '&vieworder';
            }

            $top3_state_detail = $this->state_matches[$top3_state][0];
            $token = Tools::getAdminToken($top3->getSiteid() . $top3->getAuthkey());

            $this->smarty->assign(array(
                'top3_reference' => $top3_reference,
                'top3_state' => $top3_state,
                'top3_state_detail' => $top3_state_detail,
                'top3_event' => $top3_event,
                'top3_date' => $top3_date,
                'id_order' => $id_order,
                'token' => $token,
                'url_redirect' => $url_redirect,
                'top3_loader_img' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/top3loader.gif',
            ));

            return $this->display(__FILE__, '/views/templates/admin/top3ordersummary.tpl');
        } else {
            return false;
        }
    }

    /**
     * Retrieve all Top3 information order and return it
     *
     * @param int $id_order
     * @return boolean
     */
    public function getInfoTop3Order($id_order)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::TOP3_ORDER_TABLE_NAME . '` WHERE `id_order`= ' . (int) $id_order;
        $query_result = Db::getInstance()->executeS($sql);
        if (!$query_result === false) {
            return $query_result;
        } else {
            return false;
        }
    }

    /**
     * Retrieve state Top3 information order and return it
     *
     * @param int $id_order
     * @return boolean
     */
    public function getCurrentStateTop3Order($id_order)
    {
        $sql = 'SELECT state '
                . 'FROM `' . _DB_PREFIX_ . self::TOP3_ORDER_TABLE_NAME . '` '
                . 'WHERE `id_order`= ' . (int) $id_order;
        $query_result = Db::getInstance()->getRow($sql);
        if (!$query_result === false) {
            return $query_result['state'];
        } else {
            return false;
        }
    }

    /**
     * Get all Mondial relay delivery information
     *
     * @param type $id_order
     * @return array
     */
    public function getMondialRelayInfo($id_cart)
    {
        if ($this->checkModuleisEnabledOrInstalled('mondialrelay')) {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'mr_selected` mr 
				JOIN `' . _DB_PREFIX_ . 'mr_method` m 
					ON mr.id_method = m.id_mr_method 
					WHERE `id_cart`= ' . (int) $id_cart;
            $query_result = Db::getInstance()->executeS($sql);
            return $query_result;
        } else {
            Top3Logger::insertLogTop3(
                __METHOD__ . ' : ' . __LINE__,
                'Module Mondial Relay non installé ou non activé'
            );
            return false;
        }
    }

    /**
     * Get all Ici Relais delivery information
     *
     * @param type $id_order
     * @return array
     */
    public function getIciRelaisInfo($id_cart)
    {
        if ($this->checkModuleisEnabledOrInstalled('icirelais')) {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'icirelais_selected` ir 
				JOIN `' . _DB_PREFIX_ . 'country` c 
					ON ir.id_country = c.id_country 
					WHERE `id_cart`= ' . (int) $id_cart;
            $query_result = Db::getInstance()->executeS($sql);
            return $query_result;
        } else {
            Top3Logger::insertLogTop3(__METHOD__ . ' : ' . __LINE__, 'Module Ici Relais non installé ou non activé');
            return false;
        }
    }

    /**
     * Get all SoColissimo delivery information for socolissimo officiel and socolissimo flexibilité
     *
     * @param type $id_order
     * @return array
     */
    public function getSoColissimoInfo($id_order, $module_name)
    {
        if ($this->checkModuleisEnabledOrInstalled($module_name)) {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'socolissimo_delivery_info` WHERE `id_cart`= ' . (int) $id_order;
            $query_result = Db::getInstance()->executeS($sql);
            return $query_result;
        } else {
            Top3Logger::insertLogTop3(
                __METHOD__ . ' : ' . __LINE__,
                'Module So Colissimo non installé ou non activé'
            );
            return false;
        }
    }

    /**
     * Get all SoColissimo Liberte delivery informations
     *
     * @param type $id_order
     * @return array
     */
    public function getSoColissimoLiberteInfo($id_order)
    {
        $socolissimo_installed_module = Module::getInstanceByName('soliberte');

        if ($this->checkModuleisEnabledOrInstalled('soliberte')) {
            if (version_compare($socolissimo_installed_module->version, '4.2.03', '<')) {
                $sql = 'SELECT * '
                        . 'FROM `' . _DB_PREFIX_ . 'so_delivery` '
                        . 'WHERE `cart_id`= ' . (int) $id_order;
            } else {
                $sql = 'SELECT * '
                        . 'FROM `' . _DB_PREFIX_ . 'socolissimo_delivery_info` '
                        . 'WHERE `id_cart`= ' . (int) $id_order;
            }

            $query_result = Db::getInstance()->executeS($sql);
            return $query_result;
        } else {
            Top3Logger::insertLogTop3(
                __METHOD__ . ' : ' . __LINE__,
                'Module SoColissimo Liberté non installé ou non activé'
            );
            return false;
        }
    }


    public function hookPaymentOptions($params)
    {
        
        
       /* if (!$this->active) {
            return;
        }*/
 
       /* $total_cart = $params['cart']->getOrderTotal(true);
        
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $top3 = new Top3Payment();
        } else {
            $top3 = new Top3Payment($params['cart']->id_shop);
        }
        
        
        $customer = new Customer((int) $params['cart']->id_customer);*/
        
        /*if ($top3->getStatus() == 'test') {
            
            $customer_mail = $customer->email;

            if (Configuration::get('TOP3_EMAILS_TEST') != '') {
                $mails_test = explode(',', str_replace(' ', '', Configuration::get('TOP3_EMAILS_TEST')));
                if (!in_array($customer_mail, $mails_test)) {
                    Top3Logger::insertLogTop3(
                        __METHOD__ . ' : ' . __LINE__,
                        'Adresse $customer_mail non autorisée à utiliser Top3 en test.'
                    );
                    Top3Logger::insertLogTop3(
                        __METHOD__ . ' : ' . __LINE__,
                        'Liste des adresses autorisées : ' . implode(', ', $mails_test)
                    );

                    return false;
                }
            }
        }*/
        
        
        
        /*$array_result = $top3->getEligibility($total_cart * 100, 'FRA');

        if ($array_result['result']) {*/
        
           /* $newOption = new PaymentOption();
            $newOption->setCallToActionText($this->l('Pay with 3XCB'))
                          ->setForm($this->generateForm($params['cart']->id))
                          ->setAdditionalInformation($this->context->smarty->fetch('module:top3/views/templates/front/payment_infos.tpl'))
                          ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/logo.png'));
            
            
            return [$newOption];*/
        /*} else {
            Top3Logger::insertLogTop3(
                __METHOD__ . ' : ' . __LINE__,
                'Echec Eligibility -> cart_id = '.$params['cart']->id.', libelle erreur = '.$array_result['top3_code']
            );
            return;
        }*/
        require_once('top3new.php');
        
        $top317 = new Top3New();
        return $top317->top3PaymentOptions($params);
    }
    
    protected function generateForm($cart_id)
    {
        
        $formValues = array();
        $formValues = $this->getXMLFeedValues($cart_id);
        
        $this->context->smarty->assign('action', $formValues['link_xmlfeed']);
        $this->context->smarty->assign('checksum', $formValues['checksum']);
        $this->context->smarty->assign('urlcall', $formValues['urlcall']);
        $this->context->smarty->assign('urlsys', $formValues['urlsys']);
        $this->context->smarty->assign('xml', $formValues['xml']);
        $this->context->smarty->assign('xmlparam', $formValues['xmlparam']);
        
        return $this->context->smarty->fetch('module:top3/views/templates/hook/payment_short_description_1.7.tpl');
    }
    
    
    public function getXMLFeedValues($cart_id)
    {
        $formvalues = array();
        $cart = new Cart($cart_id);
        $customer = new Customer((int) $cart->id_customer);
        $total_cart = $cart->getOrderTotal(true);
        
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $top3 = new Top3Payment();
        } else {
            $top3 = new Top3Payment($cart->id_shop);
        }
        
        
        $control = $this->buildXMLOrder($cart->id);

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $urlcall = Tools::getShopDomainSsl(true, true) .
                    __PS_BASE_URI__ . 'modules/top3/payment_return.php';
            $urlsys = Tools::getShopDomainSsl(true, true) .
                    __PS_BASE_URI__ . 'modules/top3/push.php';
        } else {
            $urlcall = Context::getContext()->link->getModuleLink('top3', 'urlcall');
            $urlsys = Context::getContext()->link->getModuleLink('top3', 'urlsys');
        }

        $xml_params = new Top3XMLParams();
        $xml_params->addParam('cart_id', $cart->id);
        $xml_params->addParam('amount', $total_cart);
        $xml_params->addParam('secure_key', $customer->secure_key);
        $xml_params->addParam('id_module', $this->name);
        $xml_params->addParam('shop_version', _PS_VERSION_);

        $link_xmlfeed = $top3->getUrlfrontline();
        $checksum = $top3->getChecksumXMLFeed($urlcall, $urlsys);
        
        
        $formvalues['link_xmlfeed'] = $link_xmlfeed;
        $formvalues['xml'] = $control;
        $formvalues['checksum'] = $checksum;
        $formvalues['urlcall'] = $urlcall;
        $formvalues['urlsys'] = $urlsys;
        $formvalues['xmlparam'] = $xml_params->saveXML();

        return $formvalues;
    }
}
