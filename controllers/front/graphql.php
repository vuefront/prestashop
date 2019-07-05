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
 * vuefront
 * vuefront.php
 */
class VuefrontGraphqlModuleFrontController extends ModuleFrontController
{
    private $codename = "vuefront";
    private $route = "vuefront";

    public function initContent()
    {
        parent::initContent();
        start($this->context);
    }
}
