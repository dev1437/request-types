<?php

namespace Dev1437\RequestTypes;

class CodeOutputGenerator implements CodeOutputGeneratorInterface 
{
    public function interfacesToCode($allInterfaces): string
    {
        $code = "declare interface ApiRequests {\n";

        foreach($allInterfaces as $route => $interface) {
            $code .= "\t'$route': {\n";
            foreach($interface as $field => $params) {
                $optional = $params['optional'] ? '?' : '';
                $code .= "\t\t$field{$optional}: {$params['type']};\n";
            }
            $code .= "\t}\n";
        }
        $code .= "}";

        return $code;
    }
}