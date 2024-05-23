<?php

namespace Dev1437\RequestTypes;

use ReflectionClass;

interface RulesResolverInterface
{
    /**
     * Takes in the request class and returns the rules in
     * the form of <field, rules>
     **/
    public function requestToRules(ReflectionClass $rules): array;
}
