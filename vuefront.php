<?php
/**
 * Starter Module
 *
 *  @author    PremiumPresta <office@premiumpresta.com>
 *  @copyright PremiumPresta
 *  @license   http://creativecommons.org/licenses/by/4.0/ CC BY 4.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Vuefront extends Module
{

    /** @var array Use to store the configuration from database */
    public $config_values;

    /** @var array submit values of the configuration page */
    protected static $config_post_submit_values = array('saveConfig');

    public function __construct()
    {
        $this->name = 'vuefront'; // internal identifier, unique and lowercase
        $this->tab = 'front_office_features'; // backend module coresponding category
        $this->version = '2.1.2'; // version number for the module
        $this->author = 'VueFront'; // module author
        $this->need_instance = 0; // load the module when displaying the "Modules" page in backend
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('VueFront'); // public name
        $this->description = $this->l('CMS Connect App for PrestaShop'); // public description

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?'); // confirmation message at uninstall

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->module_key = '1d77752fd71e98268cd50f200cb5f5ce';
    }

    /**
     * Install this module
     * @return boolean
     */
    public function install()
    {
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vuefront_url` (
            `id_url` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id` INT( 11 ) UNSIGNED NOT NULL,
            `type` varchar(64) NOT NULL,
            `url` varchar(255) NOT NULL,
            PRIMARY KEY (`id_url`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8');

        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vuefront_customer` (
          `id_customer` int(11) unsigned NOT NULL,
          `phone` varchar(255) NOT NULL,
          PRIMARY KEY (`id_customer`)
          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8');

        return parent::install() &&
        $this->registerAdminTab() && $this->registerAdminAjaxTab();
    }

    /**
     * Uninstall this module
     * @return boolean
     */
    public function uninstall()
    {
        return Configuration::deleteByName($this->name) &&
        parent::uninstall() &&
        $this->deleteAdminTab();
    }

    /**
     * Configuration page
     */
    public function getContent()
    {
        $this->config_values = $this->getConfigValues();

        $this->context->smarty->assign(array(
            'module' => array(
                'class' => get_class($this),
                'name' => $this->name,
                'displayName' => $this->displayName,
                'dir' => $this->_path
            )
        ));

        $app = json_decode(
            Tools::file_get_contents(
                realpath(_PS_MODULE_DIR_.'vuefront/').'/'
                . '/views/js/d_vuefront/manifest.json'
            ),
            true
        );
        $current_chunk = $app['files'];
        while (!empty($current_chunk)) {
            foreach ($current_chunk['js'] as $value) {
                $this->context->controller->addJS(
                    $this->_path . 'views/js/d_vuefront/' . basename($value),
                    false
                );
            }
            foreach ($current_chunk['css'] as $value) {
                $this->context->controller->addCSS(
                    $this->_path . 'views/css/admin/' . basename($value)
                );
            }
            $current_chunk = $current_chunk['next'];
        }

        $this->context->smarty->assign(array(
            'catalog' => Tools::getHttpHost(true).
            __PS_BASE_URI__.'index.php?controller=graphql&module=vuefront&fc=module',
            'blog' => Module::isInstalled('prestablog'),
            'baseUrl' => '',
            'siteUrl' => Tools::getHttpHost(true).
            __PS_BASE_URI__,
            'tokenVuefront' => Tools::getAdminTokenLite('AdminVuefrontAjax')
        ));

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * Get configuration array from database
     * @return array
     */
    public function getConfigValues()
    {
        return json_decode(Configuration::get($this->name), true);
    }

    public function registerAdminAjaxTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminVuefrontAjax';
        $tab->module = 'vuefront';

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Vuefront';
        }

        $tab->id_parent = -1;

        return $tab->save();
    }
    public function registerAdminTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminVuefront';
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Vuefront';
        }

        $tab->id_parent = (int)Tab::getIdFromClassName('AdminTools');
        $tab->module = 'vuefront';
        $tab->icon = 'library_books';

        return $tab->save();
    }

    public function deleteAdminTab()
    {
        foreach (array('AdminVuefront') as $tab_name) {
            $id_tab = (int)Tab::getIdFromClassName($tab_name);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }

        return true;
    }
}
