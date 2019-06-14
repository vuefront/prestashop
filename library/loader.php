<?php

class VFLoader
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    public function resolver($route, $args = array())
    {
        //$this->load->controller('extension/'.$this->codename.'/blog_category/category', $args);

        $parts = explode('/', (string) $route);

        // Break apart the route
        while ($parts) {
            $file = DIR_MODULE . 'resolver/' . implode('/', $parts) . '.php';

            if (is_file($file)) {
                $route = implode('/', $parts);

                break;
            } else {
                $method = array_pop($parts);
            }
        }
        $file = DIR_MODULE . 'resolver/' . $route . '.php';
        $class = $route . 'VFResolver';

        if (is_file($file)) {
            include_once $file;

            $resolver = new $class($this->registry);
        } else {
            return new \Exception('Error: Could not call ' . $route . '/' . $method . '!');
        }

        $reflection = new ReflectionClass($class);

        if ($reflection->hasMethod($method) && $reflection->getMethod($method)->getNumberOfRequiredParameters() <= count($args)) {
            return call_user_func_array(array($resolver, $method), array($args));
        } else {
            return new \Exception('Error: Could not call ' . $route . '/' . $method . '!');
        }
    }

    public function model($route)
    {

        // Sanitize the call
        $route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string) $route);

        if (!$this->registry->has('model_' . str_replace('/', '_', $route))) {
            $file = DIR_MODULE . 'model/' . $route . '.php';
            $class = $route . 'VFModel';

            if (is_file($file)) {
                include_once $file;

                $model = new $class($this->registry);
                $this->registry->set('model_' . str_replace('/', '_', (string) $route), $model);

                return $this->registry->get('model_' . str_replace('/', '_', $route));
            } else {
                throw new \Exception('Error: Could not load model ' . $route . '!');
            }
        } else {
            return $this->registry->get('model_' . str_replace('/', '_', $route));
        }

    }

    public function type($route)
    {
        // Sanitize the call
        $route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string) $route);

        if (!$this->registry->has('type_' . str_replace('/', '_', $route))) {
            $file = DIR_MODULE . 'type/' . $route . '.php';
            $class = $route . 'VFType';

            if (is_file($file)) {
                include_once $file;

                $type = new $class($this->registry);
                $this->registry->set('type_' . str_replace('/', '_', (string) $route), $type);
                return $this->registry->get('type_' . str_replace('/', '_', $route));
            } else {
                throw new \Exception('Error: Could not load type ' . $route . '!');
            }
        } else {
            return $this->registry->get('type_' . str_replace('/', '_', $route));
        }
    }
}
