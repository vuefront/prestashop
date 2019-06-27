<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/../../system/startup.php';

/**
 * d_vuefront
 * d_vuefront.php
 */
class D_VuefrontGraphqlModuleFrontController extends ModuleFrontController
{
    private $codename = "d_vuefront";
    private $route = "d_vuefront";

    public function initContent()
    {
        parent::initContent();
        start($this->context);
    }
}
