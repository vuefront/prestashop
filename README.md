<p align="center">
  <br>
  <a href="https://vuefront.com">
    <img src="https://raw.githubusercontent.com/vuefront/vuefront-docs/master/.vuepress/public/img/github/vuefront-prestashop.jpg" width="400"/>
  </a>
</p>
<h1 align="center">VueFront</h1>
<h3 align="center">CMS Connect App for PrestaShop
</h3>


<p align="center">
  <a href="https://github.com/vuefront/vuefront"><img src="https://img.shields.io/badge/price-FREE-0098f7.svg" alt="Version"></a>
  <a href="https://discord.gg/C9vcTCQ"><img src="https://img.shields.io/badge/chat-on%20discord-7289da.svg" alt="Chat"></a>
</p>

<p align="center">
Show your :heart: - give us a :star: <br/> 
Help us grow this project to be the best it can be!
  </p>

__VueFront__ is a <a href="//vuejs.org">VueJS powered</a> CMS agnostic SPA & PWA frontend for your old-fashioned Blog and E-commerce site. 

__PrestaShop__ is an efficient and innovative e-commerce solution with all the features you need to create an online store and grow your business.

__CMS Connect App__ - adds the connection between the PrestaShop CMS and VueFront Web App via a GraphQL API.

## What does it do?
This is a PrestaShop module that connects the PrestaShop CMS with the VueFront Web App via a GraphQL API. When installed, you will be provided with a CMS Connect URL that you will add to your VueFront Web App during [setup](https://vuefront.com/guide/setup.html).

## DEMO

[VueFront on PrestaShop](https://prestashop.vuefront.com/)

![VueFront for PrestaShop admin panel](http://joxi.net/E2p1aYlS7JP05A.jpg)

### PrestaShop Blog (PrestaBlog) 
Since PrestaShop does not have a built-in Blog, we use [PrestaBlog](https://addons.prestashop.com/en/blog-forum-new/4731-professional-blog.html) to add blog support. If PrestaBlog is not avalible, VueFront will ignore it.

## How to install?
Php version required >= 5.5, <= 7.2 (this limitation will be removed in the future)

### Quick Install
1. The quickest way to install is via PrestaShop Module Manager or manually [Download](https://github.com/vuefront/prestashop/releases) the **compiled** module and upload it through the 'Modules > Module Manager > Upload a module' menu in PrestaShop
2. Activate the Module after installation is complete
3. Visit modules's configurations to get the CMS Connect URL

You will need the CMS Connect URL to complete the [VueFront Web App installation](https://vuefront.com/guide/setup.html)

## Deploy VueFront Web App to hosting (static website)
### via VueFront Deploy service (recommended)
1. Install the VueFront CMS Connect App from this repo.
2. Log in or register an account with VueFront.com
3. Build your first Web App
4. Activate the new Frontend Web App (only avalible for Apache servers)
 > For Nginx you need to add this code to your `nginx.config` file right after the `index` directive
 ```
location ~ ^((?!image|.php|admin|catalog|\/img\/.*\/|wp-json|wp-admin|wp-content|checkout|rest|static|order|themes\/|modules\/|js\/|\/vuefront\/).)*$ {
    try_files /vuefront/$uri /vuefront/$uri "/vuefront${uri}index.html" /vuefront$uri.html /vuefront/200.html;
}
 ```
 

### via ftp manually
1. Install the VueFront CMS Connect App from this repo.
2. Log in or register an account with VueFront.com
3. Copy the CMS Connect URL 
4. Via Ftp create a new folder `vuefront` in the root of your PrestaShop site on your hosting. 
5. Via command line build your VueFront Web App ([read more](https://vuefront.com/guide/setup.html)) 
```
yarn create vuefront-app
# When promote, provide the CMS Connect URL, which you coppied at step 3.
yarn generate
```
6. Copy all files from folder `dist` to the newly created `vuefront` folder
7. modify you `.htaccess` file by adding after `RewriteBase` rule the following rules:
```htaccess
# VueFront scripts, styles and images
RewriteCond %{REQUEST_URI} .*(_nuxt)
RewriteCond %{REQUEST_URI} !.*/vuefront/_nuxt
RewriteRule ^([^?]*) vuefront/$1
# VueFront sw.js
RewriteCond %{REQUEST_URI} .*(sw.js)
RewriteCond %{REQUEST_URI} !.*/vuefront/sw.js
RewriteRule ^([^?]*) vuefront/$1
# VueFront favicon.ico
RewriteCond %{REQUEST_URI} .*(favicon.ico)
RewriteCond %{REQUEST_URI} !.*/vuefront/favicon.ico
RewriteRule ^([^?]*) vuefront/$1
# VueFront pages
# VueFront home page
RewriteCond %{REQUEST_URI} !.*(image|.php|admin|catalog|\/img\/.*\/|wp-json|wp-admin|wp-content|checkout|rest|static|order|themes\/|modules\/|js\/|\/vuefront\/)
RewriteCond %{QUERY_STRING} !.*(rest_route)
RewriteCond %{DOCUMENT_ROOT}".$document_path."vuefront/index.html -f
RewriteRule ^$ vuefront/index.html [L]
RewriteCond %{REQUEST_URI} !.*(image|.php|admin|catalog|\/img\/.*\/|wp-json|wp-admin|wp-content|checkout|rest|static|order|themes\/|modules\/|js\/|\/vuefront\/)
RewriteCond %{QUERY_STRING} !.*(rest_route)
RewriteCond %{DOCUMENT_ROOT}".$document_path."vuefront/index.html !-f
RewriteRule ^$ vuefront/200.html [L]
# VueFront page if exists html file
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !.*(image|.php|admin|catalog|\/img\/.*\/|wp-json|wp-admin|wp-content|checkout|rest|static|order|themes\/|modules\/|js\/|\/vuefront\/)
RewriteCond %{QUERY_STRING} !.*(rest_route)
RewriteCond %{DOCUMENT_ROOT}".$document_path."vuefront/$1.html -f
RewriteRule ^([^?]*) vuefront/$1.html [L,QSA]
# VueFront page if not exists html file
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !.*(image|.php|admin|catalog|\/img\/.*\/|wp-json|wp-admin|wp-content|checkout|rest|static|order|themes\/|modules\/|js\/|\/vuefront\/)
RewriteCond %{QUERY_STRING} !.*(rest_route)
RewriteCond %{DOCUMENT_ROOT}".$document_path."vuefront/$1.html !-f
RewriteRule ^([^?]*) vuefront/200.html [L,QSA]
```

 > For Nginx you need to add this code to your nginx.config file right after the index rule
 ```
location ~ ^((?!image|.php|admin|catalog|\/img\/.*\/|wp-json|wp-admin|wp-content|checkout|rest|static|order|themes\/|modules\/|js\/|\/vuefront\/).)*$ {
    try_files /vuefront/$uri /vuefront/$uri "/vuefront${uri}index.html" /vuefront$uri.html /vuefront/200.html;
}
 ```

## Support
For support please contact us at [Discord](https://discord.gg/C9vcTCQ)

## Submit an issue
For submitting an issue, please create one in the [issues tab](https://github.com/vuefront/vuefront/issues). Remember to provide a detailed explanation of your case and a way to reproduce it. 

Enjoy!
