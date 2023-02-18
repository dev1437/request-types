<?php

namespace Dev1437\RequestTypes\Console;

use Dev1437\RequestTypes\CodeOutputGenerator;
use Dev1437\RequestTypes\RouteFinder;
use Dev1437\RequestTypes\RouteInterfaceGenerator;
use Dev1437\RequestTypes\TypeResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:requests {path=./resources/js/routes.d.ts : Path to the generated JavaScript file.} {--group=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {   
        // use similar config as ziggy, publish to composer + github, improve logging a bit and exception handling
        $allInterfaces = [];

        
        // RouteDiscovery
        $routes = (new RouteFinder($this->option('group')))->getRoutes();

        $resolver = config('request-types.resolver', TypeResolver::class);

        $routeInterfaceGenerator = new RouteInterfaceGenerator(new $resolver);

        foreach ($routes as $routeName => $route) {
            $interface = $routeInterfaceGenerator->routeToInterface($route);
            if ($interface) {
                $allInterfaces[$routeName] = $interface;
            } else {
                $this->warn("Generated empty interface for $routeName");
            }
        }

        $codeOutputGenerator = config('request-types.output.file', CodeOutputGenerator::class);
    
        $code = (new $codeOutputGenerator)->interfacesToCode($allInterfaces);

        $path = $this->argument('path');

        File::put(
            base_path($path),
            $code
        );


        return Command::SUCCESS;
    }

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
}
