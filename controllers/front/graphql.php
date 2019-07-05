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

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/../../system/startup.php';

/**
 * d_vuefront
 * d_vuefront.php
 */
class d_VuefrontGraphqlModuleFrontController extends ModuleFrontController
{
    private $codename = "d_vuefront";
    private $route = "d_vuefront";

    public function initContent()
    {
        parent::initContent();
        start($this->context);
    }
}
