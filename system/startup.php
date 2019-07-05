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

define('DIR_PLUGIN', realpath(_PS_MODULE_DIR_.'vuefront/').'/');

require_once(DIR_PLUGIN . 'system/engine/action.php');
require_once(DIR_PLUGIN . 'system/engine/resolver.php');
require_once(DIR_PLUGIN . 'system/engine/loader.php');
require_once(DIR_PLUGIN . 'system/engine/model.php');
require_once(DIR_PLUGIN . 'system/engine/registry.php');
require_once(DIR_PLUGIN . 'system/engine/proxy.php');
require_once(DIR_PLUGIN . 'system/vendor/autoload.php');
require_once(DIR_PLUGIN . 'system/helper/MySafeException.php');

function start($context)
{
    $registry = new Registry();

    $loader = new Loader($registry);
    $registry->set('load', $loader);

    $registry->set('context', $context);

    $registry->get('load')->resolver('startup/startup');
}
