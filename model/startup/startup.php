<?php
use GraphQL\Error\ClientAware;

class MySafeException extends \Exception implements ClientAware
{
    public function isClientSafe()
    {
        return true;
    }

    public function getCategory()
    {
        return 'businessLogic';
    }
}

class ModelStartupStartup extends Model
{
    public function getResolvers() {
        $rawMapping = file_get_contents(DIR_PLUGIN.'mapping.json');
        $mapping = json_decode( $rawMapping, true );
        $result = array();
        foreach ($mapping as $key => $value) {
            $that = $this;
            $result[$key] = function($root, $args, $context) use ($value, $that) {
                try {
                    return $that->load->resolver($value, $args);
                } catch (Exception $e) {
                    throw new MySafeException($e->getMessage());
                }
            };
        }

        return $result;
    }
}