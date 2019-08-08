{*
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 * @version   0.1.0
 *}
<!doctype html>
<html lang="{$language.iso_code}">
<head>
    {block name='head'}
        {include file='_partials/head.tpl'}
    {/block}
</head>
<body id="{$page.page_name}" class="{$page.body_classes|classnames}">
<div class="container module-container">
    <div class="module-content">
        <div class="text-center module-content__image">
            <img src="modules/vuefront/views/img/logo.png"/>
        </div>
        <div class="module-content__title mb-2">{l s='You have Succefully installed Vuefront on Prestashop ' mod='vuefront'}</div>

        <a href="https://vuefront.com/guide/setup.html" target="blank" class="btn btn-primary">Continue</a>
    </div>
</div>
</body>
</html>