<?php

namespace Dev1437\RequestTypes;

interface CodeOutputGeneratorInterface 
{
    /**
     * Accepts the resolved interfaces for all of the requests and
     * returns the code to output into the file
     * 
     * @param array $allInterfaces Array of key values where the key is the route name
     *  and the value is an associative array of fields and their types.
     *  A field type is another associative array with two keys: 'type' and 'optional'
     **/
    public function interfacesToCode($allInterfaces): string;
}