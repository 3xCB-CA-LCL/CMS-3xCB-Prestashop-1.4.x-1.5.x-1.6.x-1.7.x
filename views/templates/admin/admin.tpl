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
<div id='top3_admin_content'>
{if $head_msg|escape:'htmlall':'UTF-8' <> ''}
    <div class="top3conf">
    <img src="{$path_confirmation|escape:'htmlall':'UTF-8'}" alt="" title="" />{$head_msg|escape:'htmlall':'UTF-8'}</div>
{/if}

{if $error_msg|escape:'htmlall':'UTF-8' <> ''}
    <div class="top3error">
	<img src="{$path_error|escape:'htmlall':'UTF-8'}" alt="" title="" />{$error_msg|escape:'htmlall':'UTF-8'}
	<ul>
	{foreach from=$errors item=error_label name=errors}
	    <li>{$error_label|escape:'htmlall':'UTF-8'}</li>
	{/foreach}
	</ul>
    </div>
{/if}
<br/>
<form action="" method="post">
    <fieldset>
        <legend><img src="{$logo_account_path|escape:'htmlall':'UTF-8'}" />{l s='Account settings' mod='top3'}</legend>
        <label>{l s='Site ID' mod='top3'}</label>
        <div class="margin-form">
            <input type="text" name="top3_siteid" value="{$top3_siteid|escape:'htmlall':'UTF-8'}"/>
        </div>
        <label>{l s='Key' mod='top3'}</label>
        <div class="margin-form">
            <input type="text" name="top3_authkey" value="{$top3_authkey|escape:'htmlall':'UTF-8'}"/>
        </div>
        <label>{l s='Mode' mod='top3'}</label>
        <div class="margin-form">
            <select name="top3_status">
                {foreach from=$top3_statuses item=top3_status_name name=top3_status}
                    <option value="{$top3_status_name|escape:'htmlall':'UTF-8'}" {if $top3_status_name eq $top3_status}Selected{/if}>{l s=$top3_status_name|escape:'htmlall':'UTF-8' mod='top3'}</option>
                {/foreach}
            </select> {l s='In test mode, you will not receive payment. In production mode, you will receive real payment.' mod='top3'}
        </div>

        <label>{l s='Email test' mod='top3'}</label>
        <div class="margin-form">
            <input type="text" size="40" name="top3_email_test" value="{$top3_email_test|escape:'htmlall':'UTF-8'}"/> {l s='You can put multiple addresses separated by a "," ' mod='top3'}
        </div>
    </fieldset>

    <br/>	

    <fieldset>
        <legend><img src="{$logo_categories_path|escape:'htmlall':'UTF-8'}" />{l s='Categories settings' mod='top3'}</legend>
        <p>{l s='For a better quality of service, 3XCB needs to know the types of products' mod='top3'} :</p>
        <label>{l s='Default Product Type' mod='top3'}</label>
        <div class="margin-form">
            <select name="top3_default_product_type">
                <option value="0">-- {l s='Choose' mod='top3'} --</option>
                {foreach from=$top3_product_types item=product_type key=id_product_type name=product_types}
                    <option value="{$id_product_type|intval}" {if $top3_default_product_type eq $id_product_type}Selected{/if}>{$product_type|strval}</option>
                {/foreach}
            </select>
        </div>

        <div class="margin-form">
            <table class="table">
                <thead>
                    <tr><th>{l s='Shop category' mod='top3'}</th><th>{l s='3XCB category' mod='top3'}</th></tr>
                </thead>
                <tbody>
                    {foreach from=$shop_categories key=id item=shop_category name=shop_categories}
                        <tr>
                            <td>{$shop_category.name|escape:'htmlall':'UTF-8'}</td>
                            <td>
                                <select name="top3_{$id|intval}_product_type">
                                    <option value="0">-- {l s='Choose' mod='top3'} --</option>
                                    {foreach from=$top3_product_types item=product_type key=id_product_type name=product_types}
                                        <option value="{$id_product_type|intval}" {if $shop_category.top3_type eq $id_product_type}Selected{/if}>{$product_type|strval}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </fieldset>

    <br/>

    <fieldset>
        <legend><img src="{$logo_carriers_path|escape:'htmlall':'UTF-8'}"/>{l s='Carrier settings' mod='top3'}</legend>
        <p>{l s='Thank you for selecting a type of carrier for each carrier of your shop' mod='top3'} :</p>		
        <label>{l s='Default Carrier Type' mod='top3'}</label>
        <div class="margin-form">
            <select name="top3_default_carrier_type">
                <option value="0">-- {l s='Choose' mod='top3'} --</option>
                {foreach from=$top3_carrier_types key=id_carrier_type item=top3_carrier_type name=top3_carrier_types}
                    <option value="{$id_carrier_type|intval}" {if $top3_default_carrier_type eq $id_carrier_type}Selected{/if}>{$top3_carrier_type|strval}</option>
                {/foreach}
            </select>
            <select name="top3_default_carrier_speed">
                {foreach from=$top3_carrier_speeds key=id_carrier_speed item=top3_carrier_speed name=top3_carrier_speeds}
                    <option value="{$id_carrier_speed|intval}" {if $top3_default_carrier_speed eq $id_carrier_speed}Selected{/if}>{$top3_carrier_speed|strval}</option>
                {/foreach}
            </select>
        </div>

        <div class="margin-form">
            <table cellspacing="0" cellpadding="0" class="table">
                <thead><tr><th>{l s='Carrier' mod='top3'}</th><th>{l s='Carrier Type' mod='top3'}</th><th>{l s='Carrier Speed' mod='top3'}</th></tr></thead>
                <tbody>
                    {foreach from=$shop_carriers key=id_shop_carrier item=shop_carrier name=shop_carriers}
                        <tr>
                            <td>{$shop_carrier.name|escape:'htmlall':'UTF-8'}</td>
                            <td>
                                <select name="top3_{$id_shop_carrier|intval}_carrier_type">
                                    <option value="0">-- {l s='Choose' mod='top3'} --</option>
                                    {foreach from=$top3_carrier_types key=id_carrier_type item=top3_carrier_type name=top3_carrier_types}
                                        <option value="{$id_carrier_type|intval}" {if $shop_carrier.top3_type eq $id_carrier_type}Selected{/if}>{$top3_carrier_type|strval}</option>
                                    {/foreach}
                                </select>
                            </td>
                            <td>
                                <select name="top3_{$id_shop_carrier|intval}_carrier_speed">
                                    {foreach from=$top3_carrier_speeds key=id_carrier_speed item=top3_carrier_speed name=top3_carrier_speeds}
                                        <option value="{$id_carrier_speed|intval}" {if $shop_carrier.top3_speed eq $id_carrier_speed}Selected{/if}>{$top3_carrier_speed|strval}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

        <br /><img src="{$logo_warning|escape:'htmlall':'UTF-8'}"/>{l s='To use the withdrawal store, you must enter the address of your store.' mod='top3'} <a href="{$link_shop_setting|strval}" target="_blank">{l s='Check the details of the shop here' mod='top3'}</a>.

    </fieldset>

    <br/>
    <center><input type="submit" name="submitSettings" value="{l s='Save' mod='top3'}" class="button" /></center>

</form>	
<br/>
<center><input type="button" name="submitLog" onclick="ShowHide();" value="{l s='Show/Hide 3XCB log file' mod='top3'}" class="button" /></center>
<br/>
<center>
    <fieldset id="top3_log" style="display:none;">
        <textarea cols="100%" rows="10">{$log_content|escape:'htmlall':'UTF-8'}</textarea>
        <br/>
    </fieldset>
</center>
<br/>

<fieldset>
    <legend><img src="{$logo_information|escape:'htmlall':'UTF-8'}"/>{l s='Manage your payments in the 3XCB administration interface' mod='top3'}</legend>
    {l s='Your administration interface' mod='top3'} : <a target='_blank' href='https://www.3foiscb-sofinco.fr/bo/login'>{l s='https://business.top3.com/merchantbo/login.htm' mod='top3'}</a>.
    <br/><br/>{l s='The administration interface allows you 3XCB manage your payments: monitoring, cancellation, refund.' mod='top3'}
</fieldset>
</div>
