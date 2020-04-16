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
class VuefrontCallbackModuleFrontController extends ModuleFrontController
{
    private $codename = "vuefront";
    private $route = "vuefront";

    public function initContent()
    {
        parent::initContent();

        callback($this->context);
    }
}
