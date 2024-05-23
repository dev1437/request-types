<?php

namespace Dev1437\RequestTypes\Console;

use Dev1437\RequestTypes\CodeOutputGenerator;
use Dev1437\RequestTypes\RouteFinder;
use Dev1437\RequestTypes\RouteInterfaceGenerator;
use Dev1437\RequestTypes\RulesResolver;
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
    protected $description = 'Generate a typescript interface for custom requests';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $allInterfaces = [];

        // RouteDiscovery
        $routes = (new RouteFinder($this->option('group')))->getRoutes();

        $resolver = config('request-types.resolver', TypeResolver::class);

        $ruleResolver = config('request-types.rules-resolver', RulesResolver::class);

        $routeInterfaceGenerator = new RouteInterfaceGenerator(new $resolver, new $ruleResolver);

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
}
