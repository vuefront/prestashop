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

<div class="panel">
    <div class="module-content">
        <div class="text-center module-content__image">
            <img src="../modules/vuefront/views/img/logo.png"/>
        </div>
        <div class="module-content__form">
            <div class="module-content__row">
                <h3 class="text-center mb-3">{l s='CMS Connect URL' mod='vuefront'}</h3>
                <div class="input-group mb-2">
                    <input id="connect_url" class="form-control" type="text" value="{$catalog|escape:'htmlall':'UTF-8'}" readonly>
                    <div class="input-group-btn">
                        <span class="btn btn-primary clipboard" data-clipboard-target="#connect_url">{l s='copy' mod='vuefront'}</span>
                    </div>
                </div>
                <p class="module-content__warning">
                  <b>{l s='Heads up!' mod='vuefront'}</b> {l s='When visiting this URL via the browser you should see an error message. This is normal behavior for a GraphQL API. Please, continue with setting up your VueFront Web App.' mod='vuefront'}</p>
                <p class="module-content__description">
                {l s='This is your CMS Connect URL link that shares your Store data via GraphQL. When installing VueFront via the command line, you will be prompted to enter this URL. Simply copy and paste it into the command line.' mod='vuefront'}
                <br/>
                <br/>
                 {l s='Read more about the' mod='vuefront' } <a href="https://vuefront.com/cms/prestashop.html" target="_blank">{l s='CMS Connect for Prestashop' mod='vuefront'}</a>
                </p>
            </div>
            <hr/>
            <div class="module-content__row">
                <h3 class="text-center mb-3"  style="border-bottom: 0;">{l s='Blog support' mod='vuefront'}</h3>
                <div class="text-center">
                {if $blog}
                    <span class="btn btn-success">{l s='Blog enabled' mod='vuefront'}</span>
                {else}
                    <a class="btn btn-danger" href="https://addons.prestashop.com/ru/blog-forum-news/4731-professional-blog.html" target="_blank">{l s='Blog disabled' mod='vuefront'}</a>
                {/if}
                 <p class="module-content__description">
                        {l s='VueFront relies on the' mod='vuefront'}
                        <a href="https://addons.prestashop.com/ru/blog-forum-news/4731-professional-blog.html" target="_blank">{l s='Blog Module' mod='vuefront'}</a>
                        {l s='to implement blog support. The blog feature is optional and VueFront will work fine without it.' mod='vuefront'}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<script lang="text/javascript">
jQuery(document).ready(function() {
    var clipboard = new ClipboardJS('.clipboard')

    clipboard.on('success', function(e) {
        jQuery(e.trigger).text("{l s='copied!' mod='vuefront'}")
        jQuery(e.trigger).addClass('btn-success').removeClass('btn-primary')

        e.clearSelection()
    })
})
</script>