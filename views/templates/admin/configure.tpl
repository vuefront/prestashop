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

<vf-app class="vuefront-app"></vf-app>
<script lang="text/javascript">
jQuery(document).ready(function() {
    
    d_vuefront({
        selector: '.vuefront-app',
        baseURL: "{$baseUrl|escape:'htmlall':'UTF-8'}",
        siteUrl: "{$siteUrl|escape:'htmlall':'UTF-8'}",
        tokenUrl: "token={$tokenVuefront|escape:'html':'UTF-8'}",
        apiURL: '',
        type: 'prestashop'
      })
})
</script>