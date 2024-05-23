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
                // $optional = $params['optional'] ? '?' : '';
                // $code .= "\t\t$field{$optional}: {$params['type']};\n";
                $code = $this->fieldToCode($field, $params, $code);
            }
            $code .= "\t}\n";
        }
        $code .= "}";

        return $code;
    }

    private function fieldToCode($field, $params, $code, $tabs = 1)
    {
        if (array_is_list($params)) {
            $optional = $params[1] ? '?' : '';
            $code .= str_repeat("\t\t", $tabs);
            $code .= "$field{$optional}: {$params[0]};\n";
        } else {
            $code .= str_repeat("\t\t", $tabs);
            $code .= "$field: {\n";
            foreach ($params as $childField => $childParams) {
                $code = $this->fieldToCode($childField, $childParams, $code, $tabs + 1);
            }
            $code .= str_repeat("\t\t", $tabs);
            $code .= "};\n";
        }
        return $code;
        // if associatve array, recurse
        // else get the things
    }
}
