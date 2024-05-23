<?php

namespace Dev1437\RequestTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionFunction;

class RouteInterfaceGenerator {

    public function __construct(private TypesResolverInterface $typeResolver, private RulesResolverInterface $ruleResolver)
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
                    $rules = $this->ruleResolver->requestToRules($requestRC);
                } catch (\Throwable $th) {
                    echo "Failed to get rules for {$type->getName()} for {$method->getName()}\n";
                }

                if ($rules) {
                    // RuleToType
                    foreach ($rules as $field => $fieldRules) {
                        
                        // How do we handle arrays/nested fields with dot notation...
                        // Recursively deal with array type, later
                        // if field has a dot in it, then find corresponding field and add types to that interface
                        if (str_contains($field, '.')) {
                            continue;
                        }

                        $tsInterface = $this->rulesToInterface($field, $fieldRules, $tsInterface);
                    }
                }
            }
        }
        return $tsInterface;
    }

    private function rulesToInterface($field, $rules, $interface) {
        if (array_is_list($rules)) {
            $interface[$field] = [
                $this->typeResolver->rulesToType($rules),
                $this->typeResolver->fieldIsOptional($rules)
            ];
        } else {
            // $tsInterface[$field] = [];
            // foreach field in fieldRules
            $interface[$field] = [];
            foreach ($rules as $childField => $childRules) {
                $interface[$field] = $this->rulesToInterface($childField, $childRules, $interface[$field]);
            }
        }
        return $interface;
    }
}
