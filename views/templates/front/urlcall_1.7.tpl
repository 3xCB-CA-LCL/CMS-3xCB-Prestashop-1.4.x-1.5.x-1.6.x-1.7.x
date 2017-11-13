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

{extends "$layout"}

{block name="content"}
{if $payment_status|escape:'htmlall':'UTF-8'}
    <p>{l s='Your order has been registered.' mod='top3'}</p>
    <p>{l s='For any question please contact our ' mod='top3'} <a href="{$path_contact|escape:'htmlall':'UTF-8'}">{l s='customer service' mod='top3'}</a></p>
    {else}

    {if $string_errors == 'PAYMENT_ABORTED'}
        <p>{l s='You have not verified your payment, but your shopping cart is still available !' mod='top3'}</p>
    {/if}

    {if $string_errors == 'PAYMENT_KO'}
        <p>{l s='Error : your payment has been refused' mod='top3'}</p>
    {/if}

    {if $string_errors == 'REQUEST_KO'}
        <p>{l s='Error : One or more error occured during the validation' mod='top3'}</p>
    {/if}

    <p>{l s='You can complete your order with payment method of your choice by clicking on the cart.' mod='top3'}</p>
    <p><a href="{$path_order|escape:'htmlall':'UTF-8'}"><img src="{$modules_dir|escape:'htmlall':'UTF-8'}top3/views/img/cart.gif"> {l s='Back to cart' mod='top3'}</img></a></p>
        {/if}
<p><a href="{$path_history|escape:'htmlall':'UTF-8'}"><img src="{$modules_dir|escape:'htmlall':'UTF-8'}top3/views/img/order.gif"> {l s='Back to my orders' mod='top3'}</img></a></p>


<br/>
{if $products != false}
    <p>{l s='Your purchased products' mod='top3'}</p>
    <table class="gridtable" width="100%">
        <tr>
            <th>{l s='Products' mod='top3'}</th>
            <th>{l s='Quantity' mod='top3'}</th>
            <th>{l s='Price' mod='top3'}</th>
        </tr>
        {foreach from=$products  item=product name=products_ht}
            <tr>
                <td>{$product.name|escape:'htmlall':'UTF-8'}</td>
                <td>{$product.cart_quantity|escape:'htmlall':'UTF-8'}</td>
                <td id="total">{$product.total_wt|escape:'htmlall':'UTF-8'} &euro;</td>
            </tr>
        {/foreach}
        <tr>
            <td colspan="2"></td>
            <td id="total_payed">{l s='Total shipping' mod='top3'} : {$total_shipping|escape:'htmlall':'UTF-8'} &euro;</td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td id="total_payed">{l s='Total payed' mod='top3'} : {$amount|escape:'htmlall':'UTF-8'} &euro;</td>
        </tr>
    </table><br/>
{/if}

{/block}