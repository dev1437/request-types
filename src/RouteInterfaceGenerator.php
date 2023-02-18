<?php

namespace Dev1437\RequestTypes;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class RouteInterfaceGenerator {

    public function __construct(private TypesResolveInterface $typeResolver)
    {
        
    }

    private function routeToMethod($route )
    {
        if ($route->getAction()['uses'] instanceof \Closure) {
            return new ReflectionFunction($route->getAction()['uses']);
        }
        $controller = explode("@", $route->getAction()['uses']);
        $method = $controller[1];
        $controller = $controller[0];
        $reflectionController = new ReflectionClass($controller);
        $reflectionMethod = $reflectionController->getMethod($method);

        return $reflectionMethod;
    }

    public function routeToInterface($route)
    {
        $method = $this->routeToMethod($route);
        $methodParams = $method->getParameters();
        $tsInterface = [];
        
        foreach ($methodParams as $param) {
            $type = $param->getType();
            
            if ($type && (is_subclass_of($type->getName(), Request::class) || is_subclass_of($type->getName(), FormRequest::class))) {
                $requestRC = new ReflectionClass($type->getName());
                $rules = [];
                try {
                    $rules = $requestRC->newInstance()->rules();
                } catch (\Throwable $th) {
                    echo "Failed to get rules for {$type->getName()} for {$method->getName()}\n";
                }

                if ($rules) {
                    // RuleToType
                    foreach ($rules as $field => $rules) {
                        // How do we handle arrays/nested fields with dot notation...
                        // Recursively deal with array type, later
                        // if field has a dot in it, then find corresponding field and add types to that interface
                        if (str_contains($field, '.')) {
                            continue;
                        }
                        $tsInterface[$field] = [
                            'type' => $this->typeResolver->rulesToType($rules),
                            'optional' => $this->typeResolver->fieldIsOptional($rules)
                        ];
                    }
                }
            }
        }
        return $tsInterface;
    }
}