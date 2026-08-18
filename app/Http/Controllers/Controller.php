<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use ReflectionMethod;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function callAction($method, $parameters)
    {
        $ref = new ReflectionMethod($this, $method);
        $paramNames = array_map(fn($p) => $p->getName(), $ref->getParameters());

        if (!in_array('tenant', $paramNames) && isset($parameters['tenant'])) {
            unset($parameters['tenant']);
        }

        $numeric = [];
        foreach ($parameters as $key => $value) {
            if (is_int($key)) {
                $numeric[] = $value;
            }
        }

        $args = [];
        $ni = 0;
        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $parameters)) {
                $args[] = $parameters[$name];
            } elseif (isset($numeric[$ni])) {
                $args[] = $numeric[$ni++];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            }
        }

        return $this->{$method}(...$args);
    }
}
