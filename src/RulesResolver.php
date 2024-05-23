<?php

namespace Dev1437\RequestTypes;

use ReflectionClass;

class RulesResolver implements RulesResolverInterface {

    public function requestToRules(ReflectionClass $request): array
    {
        $rules = [];
        // $interfaces = class_implements($request->newInstance());
        // if (isset($interfaces["Illuminate\Contracts\Support\Jsonable"])) {
        //     $rules = $request->newInstance()->toJson();
        // }
        $rm = $request->getMethod('rules');

        $filename = $rm->getFileName();
        $start_line = $rm->getStartLine() + 1;
        $end_line = $rm->getEndLine() - 1;
        $length = $end_line - $start_line;

        $source = file($filename);
        $body = implode("", array_slice($source, $start_line, $length));


        $rules = $this->parseArraysInFunction("<?php " . PHP_EOL . $body);

        return $rules;

        // if (!$rules) {
        //     try {
        //         $rules = $request->newInstance()->rules();
        //     } catch (\Throwable $th) {
        //         echo "Failed to get rules for ". $request->getName();
        //     }
        // }
        return $rules;
    }

    public function parseArray($tokens) {
        $values = [];
        $ignoreNextTokens = false;
        $currentKey = null;
        $stack = [];

        for ($i=0; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            $nextToken = $tokens[$i + 1] ?? null;

            if(is_array($token) && !$ignoreNextTokens) {
                if ($token[0] === T_CONSTANT_ENCAPSED_STRING && (is_array($nextToken) && $nextToken[0] === T_DOUBLE_ARROW) && !$ignoreNextTokens) {
                    $currentKey = trim($token[1], '\'"');
                    $values[trim($token[1], '\'"')] = [];
                } else if ($token[0] === T_CONSTANT_ENCAPSED_STRING && !$ignoreNextTokens) {
                    $currentKey ? $values[$currentKey] = trim($token[1], '\'"') : $values[] = trim($token[1], '\'"');
                } else if($token[0] === T_NEW) {
                    $ignoreNextTokens = true;
                }

            } else if(!is_array($token)) {
                if ($token === '(') {
                    $stack[] = '(';
                    $ignoreNextTokens = true;
                } else if ($token === '[' && !$ignoreNextTokens) {
                    // start of array
                    [$result, $newI] = $this->parseArray(array_slice($tokens, $i + 1));
                    $currentKey ? $values[$currentKey] = $result : $values[] = $result;
                    $i += $newI;
                } else if ($token === ']' && !$ignoreNextTokens) {
                    return [$values, $i + 1];
                } else if ($token === ')') {
                    $stack = count($stack) > 1 ? array_slice($stack, -1) : [];
                    if (count($stack) === 0) {
                        $ignoreNextTokens = false;
                    }
                }
            }
        }

        return [$values, count($tokens) - 1];
    }

    public function parseArraysInFunction($function)
    {
        $tokens = token_get_all($function, TOKEN_PARSE);

        // filter out all whitespace before passing in
        $tokens = array_filter($tokens, function($token) {
            if (is_array($token) && $token[0] === T_WHITESPACE) {
                return false;
            }
            return true;
        });

        $tokens = array_values($tokens);

        return $this->parseArray($tokens)[0][0];
    }
}
