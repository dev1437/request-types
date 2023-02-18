<?php

namespace Dev1437\RequestTypes;

class RequestDiscovery {

    public function rulesToType($rules)
    {
        // unknown
        // boolean -> boolean
        // email, string, date -> string
        // enum -> ?
        // array -> array<unknown> (possible to get type of element, recursively)
        // integer, decimal -> number
        $type = 'unknown';
        if (is_array($rules)) {
            if (in_array(['string', 'date', 'email'], $rules)) {
                $type = 'string';
            } else if (in_array('boolean', $rules)) {
                $type = 'boolean';
            } else if (in_array('array', $rules)) {
                $type = 'unknown[]';
            } else if (in_array(['integer', 'decimal'], $rules)) {
                $type = 'number';
            }
        } else {
            if (in_array($rules, ['string', 'date', 'email'])) {
                $type = 'string';
            } else if ($rules === 'boolean') {
                $type = 'boolean';
            } else if ($rules === 'array') {
                $type = 'unknown[]';
            } else if (in_array($rules, ['integer', 'decimal'])) {
                $type = 'number';
            }
        }
        return $type;
    }

    public function fieldIsOptional($rules)
    {
        // nullable -> optional
        // Present -> allow null, disallow optional
        // sometimes...
        if (is_array($rules)) {
            if (in_array('required', $rules)) {
                return false;
            } else if (in_array('nullable', $rules)) {
                return true;
            }
        } else {
            if ('required' === $rules) {
                return false;
            } else if ('nullable' === $rules) {
                return true;
            }
        }
        return false;
    }

    public function requests()
    {
        // Get controllers
        collect(app('router')->getRoutes()->getRoutesByName());

        $controllers = collect(File::allFiles(app_path('Http/Controllers')))
        ->map(function ($item) {
            $model = substr($item->getFilename(), 0, -4);

            return "App\\Http\\Controllers\\{$model}";
        })->filter(function ($class) {
            $valid = false;
            if (class_exists($class)) {
                $reflection = new ReflectionClass($class);
                $valid = $reflection->isSubclassOf(Controller::class) && !$reflection->isAbstract();
            }

            return $valid;
        });

        $controllers = $controllers->values();
        $allInterfaces = [];
        foreach ($controllers as $controller) {
            $reflectionController = new ReflectionClass($controller);

            $methods = $reflectionController->getMethods();

            [$fallbacks, $routes] = collect(app('router')->getRoutes()->getRoutesByName())
            ->reject(function ($route) {
                return Str::startsWith($route->getName(), 'generated::');
            })
            ->partition(function ($route) {
                return $route->isFallback;
            });

            foreach ($methods as $method) {
                $methodParams = $method->getParameters();

                foreach ($methodParams as $param) {
                    $type = $param->getType();

                    // dd(is_subclass_of($type, Request::class));
                    if ($type && (is_subclass_of($type->getName(), Request::class) || is_subclass_of($type->getName(), FormRequest::class))) {
                        $requestRC = new ReflectionClass($type->getName());
                        $rules = [];
                        try {
                            $rules = $requestRC->newInstance()->rules();
                        } catch (\Throwable $th) {
                            $this->info("Failed to get rules for {$type->getName()} in {$controller}");
                            $this->error($th->getMessage());
                        }

                        if ($rules) {
                            // Rules to types
                            $tsInterface = [];
                            foreach ($rules as $field => $rules) {
                                // How do we handle arrays/nested fields with dot notation...
                                // Recursively deal with array type, later
                                // if field has a dot in it, then find corresponding field and add types to that interface
                                if (str_contains($field, '.')) {
                                    continue;
                                }
                                $field = $this->fieldIsOptional($rules) ? "$field?" : $field;
                                $tsInterface[$field] = $this->rulesToType($rules);
                            }

                            foreach ($routes as $routeName => $route) {
                                if (is_string($route->getAction()['uses']) && str_contains($route->getAction()['uses'], $controller)) {
                                    $allInterfaces[$routeName] = $tsInterface;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}