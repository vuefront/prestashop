<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/../../library/startup.php';

use Youshido\GraphQL\Execution\Processor;
use Youshido\GraphQL\Schema\Schema;
use Youshido\GraphQL\Type\Object\ObjectType;

/**
 * d_vuefront
 * d_vuefront.php
 */
class D_VuefrontGraphqlModuleFrontController extends ModuleFrontController
{
    private $codename = "d_vuefront";
    private $route = "d_vuefront";
    private $registry;

    public function __construct()
    {
        parent::__construct();
        $this->initRegistry();
    }

    public function initRegistry()
    {
        $this->registry = new VFRegistry();
        $this->registry->set('context', $this->context);
        $this->registry->set('load', new VFLoader($this->registry));
    }

    public function __get($key)
    {
        if (!$this->registry) {
            $this->init();
        }
        return $this->registry->get($key);
    }

    public function initContent()
    {
        parent::initContent();
        $result = array();

        $this->load->model($this->route);
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        $query = $input['query'];

        $queries = $this->model_d_vuefront->getQueries();
        $mutations = $this->model_d_vuefront->getMutations();

        $queryType = new ObjectType(array(
            'name' => 'RootQueryType',
            'fields' => $queries,
        ));
        $mutationType = new ObjectType(array(
            'name' => 'RootMutationType',
            'fields' => $mutations,
        ));

        $schema = new Schema(array(
            'query' => $queryType,
            'mutation' => $mutationType,
        ));

        $processor = new Processor($schema);

        if (!empty($input['variables'])) {
            $processor->processPayload($input['query'], $input['variables']);

        } else {
            $processor->processPayload($input['query']);
        }

        $result = $processor->getResponseData();
        header('Content-Type: application/json; charset=UTF-8');
        // In the template, we need the vars paymentId & paymentStatus to be defined

        die(Tools::jsonEncode($result));
    }
}
