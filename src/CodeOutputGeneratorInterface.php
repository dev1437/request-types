<?php

namespace Dev1437\RequestTypes;

interface CodeOutputGeneratorInterface 
{
    public function interfacesToCode($allInterfaces): string;
}