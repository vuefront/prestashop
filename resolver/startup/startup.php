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

use GraphQL\GraphQL;
use GraphQL\Utils\BuildSchema;

class ResolverStartupStartup extends Resolver
{
    public function index()
    {

        if (Tools::getValue('cors')) {
            if (!empty($_SERVER['HTTP_ORIGIN'])) {
                header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            } else {
                header('Access-Control-Allow-Origin: *');
            }
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: DNT,User-Agent,X-Requested-With, ' .
                'If-Modified-Since,Cache-Control,Content-Type,Range,Token,token,Cookie,cookie,content-type');
        }

        $this->load->model('startup/startup');

        try {
            $resolvers = $this->model_startup_startup->getResolvers();
            $schema = BuildSchema::build(Tools::file_get_contents(DIR_PLUGIN . 'schema.graphql'));
            $rawInput = Tools::file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $query = $input['query'];

            if (empty($query)) {
                die('Query missing.');
            }

            $variableValues = isset($input['variables']) ? $input['variables'] : null;
            $result = GraphQL::executeQuery($schema, $query, $resolvers, null, $variableValues);
        } catch (\Exception $e) {
            $result = [
                'error' => [
                    'message' => $e->getMessage()
                ]
            ];
        }

        die(json_encode($result));
    }
}
