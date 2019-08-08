<?php
/**
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 * @version   0.1.0
 */

require_once dirname(__FILE__) . '/../../system/startup.php';

/**
 * vuefront
 * vuefront.php
 */
class VuefrontGraphqlModuleFrontController extends ModuleFrontController
{
    private $codename = "vuefront";
    private $route = "vuefront";

    public function setMedia()
    {
        parent::setMedia();
        $this->context->controller->registerStylesheet(
            'modules-vuefront-front-css',
            'modules/vuefront/views/css/front.css',
            array('media' => 'all', 'priority' => 200)
        );
    }

    public function initContent()
    {
        parent::initContent();
        if ($_SERVER['HTTP_ACCEPT']) {
            $accepts = explode(',', $_SERVER['HTTP_ACCEPT']);
            if (in_array('text/html', $accepts)) {
                $this->context->smarty->assign(array(
                    'hello' => 'Hello World!!!'
                ));

                $this->setTemplate('module:vuefront/views/templates/front/d_vuefront.tpl');
                return;
            }
        }

        start($this->context);
    }
}
