<?php

namespace Dev1437\RequestTypes;

interface TypesResolverInterface
{
    /**
     * Takes in the rules for a given field on the request 
     * and returns whether or not the field is optional
     **/
    public function fieldIsOptional(array|string $rules): bool;

    /**
     * Takes in the rules for a given field on the request 
     * and returns the type
     **/
    public function rulesToType(array|string $rules): string;
}