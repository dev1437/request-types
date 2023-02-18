<?php

namespace Dev1437\RequestTypes;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class RouteFinder {

    protected $routes;

    public function __construct(protected $group = null)
    {
        $this->routes = collect(app('router')->getRoutes()->getRoutesByName());
    }

    public function applyFilters($group)
    {
        if ($group) {
            return $this->group($group);
        }

        // return unfiltered routes if user set both config options.
        if (config()->has('request-types.except') && config()->has('request-types.only')) {
            return $this->routes;
        }

        if (config()->has('request-types.except')) {
            return $this->filter(config('request-types.except'), false)->routes;
        }

        if (config()->has('request-types.only')) {
            return $this->filter(config('request-types.only'))->routes;
        }

        return $this->routes;
    }

    /**
     * Filter routes by group.
     */
    private function group($group)
    {
        if (is_array($group)) {
            $filters = [];

            foreach ($group as $groupName) {
                $filters = array_merge($filters, Arr::wrap(config("request-types.groups.{$groupName}")));
            }

            return $this->filter($filters)->routes;
        }

        if (config()->has("request-types.groups.{$group}")) {
            return $this->filter(config("request-types.groups.{$group}"))->routes;
        }

        return $this->routes;
    }

    /**
     * Filter routes by name using the given patterns.
     */
    public function filter($filters = [], $include = true): self
    {
        $filters = Arr::wrap($filters);

        $reject = collect($filters)->every(function (string $pattern) {
            return Str::startsWith($pattern, '!');
        });

        $this->routes = $reject
            ? $this->routes->reject(function ($route, $name) use ($filters) {
                foreach ($filters as $pattern) {
                    if (Str::is(substr($pattern, 1), $name)) {
                        return true;
                    }
                }
            })
            : $this->routes->filter(function ($route, $name) use ($filters, $include) {
                if ($include === false) {
                    return ! Str::is($filters, $name);
                }

                foreach ($filters as $pattern) {
                    if (Str::startsWith($pattern, '!') && Str::is(substr($pattern, 1), $name)) {
                        return false;
                    }
                }

                return Str::is($filters, $name);
            });

        return $this;
    }

    public function getRoutes()
    {
        return $this->applyFilters($this->group);
    }
}