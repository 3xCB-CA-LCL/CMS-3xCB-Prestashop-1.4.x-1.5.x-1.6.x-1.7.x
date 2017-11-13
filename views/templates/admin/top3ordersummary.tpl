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

<br/>
<div class="panel">
    <fieldset>
        <legend>{l s='3XCB' mod='top3'}</legend>
        <div id="info_tagline">
            {l s='Transaction reference' mod='top3'} : <b>{$top3_reference|escape:'htmlall':'UTF-8'}</b><br/><br/>
            {l s='Transaction state' mod='top3'} : <b>{$top3_state_detail|escape:'htmlall':'UTF-8'}</b>

            <br/><br/><input class="button" onclick="getTransactionState();" type="button" value="{l s='Get transaction state' mod='top3'}" />

            {if $top3_state|strval eq 'CONTRACT_ACCEPTED'}
                <br/><br/>
                <input class="button" onclick="remote('validatetransaction');" type="button" value="{l s='Confirm transaction' mod='top3'}" />
                <input class="button" onclick="remote('canceltransaction');" type="button" value="{l s='Cancel transaction' mod='top3'}" />
            {/if}

            {if $top3_state|strval eq 'CANCELLATION_ASKED'}
                <input class="button" onclick="remote('cancelprecedentorder');" type="button" value="{l s='Cancel cancellation asked' mod='top3'}" />
            {/if}

            {if $top3_state|strval eq 'VALIDATION_ASKED'}
                <input class="button" onclick="remote('cancelprecedentorder');" type="button" value="{l s='Cancel confirmation asked' mod='top3'}" />
            {/if}

            <div id="loader_img" style="display: none;"><br/><img src="{$top3_loader_img|escape:'htmlall':'UTF-8'}" ></div>
        </div>

        <input name="id_order" id="id_order" type="hidden" value="{$id_order|intval}" />
        <input name="top3_reference" id="top3_reference" type="hidden" value="{$top3_reference|escape:'htmlall':'UTF-8'}" />
        <input name="token" id="top3_token" type="hidden" value="{$token|escape:'htmlall':'UTF-8'}" />
        <input name="url_redirect" id="url_redirect" type="hidden" value="{$url_redirect|escape:'htmlall':'UTF-8'}" />
    </fieldset>
</div>
