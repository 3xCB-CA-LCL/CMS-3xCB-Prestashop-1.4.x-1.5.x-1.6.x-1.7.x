{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{literal}
    <script>
        function submitForm() {
            document.forms["top3form"].submit();
        }
    </script>
{/literal}

<p class="payment_module_top3">
    <a href="#" onclick="submitForm();" title="{l s='Pay by CB in 2X, 3X or 4X without fees' mod='top3'}">
        <img src="{$logo3cb|escape:'htmlall':'UTF-8'}" alt="{l s='Pay by CB in 2X, 3X or 4X without fees' mod='top3'}"/>
        {l s='Pay by CB in 2X, 3X or 4X without fees' mod='top3'}
    </a>
</p>
<form name='top3form' action='{$link_xmlfeed|escape:'htmlall':'UTF-8'}' method='post'>
    <input type='hidden' name='CheckSum' value='{$checksum|escape:'htmlall':'UTF-8'}'>
    <input type='hidden' name='URLCall' value='{$urlcall|escape:'htmlall':'UTF-8'}'>
    <input type='hidden' name='URLSys' value='{$urlsys|escape:'htmlall':'UTF-8'}'>
    <input type='hidden' name='XMLInfo' value='{$xml|escape:'htmlall':'UTF-8'}'>
    <input type='hidden' name='XMLParam' value='{$xmlparam|escape:'htmlall':'UTF-8'}'>
</form>


