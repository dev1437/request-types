<?php

namespace Dev1437\RequestTypes;

class TypeResolver implements TypesResolverInterface {

    public function rulesToType(array|string $rules): string
    {
        // unknown
        // boolean -> boolean
        // email, string, date -> string
        // enum -> ?
        // array -> array<unknown> (possible to get type of element, recursively)
        // integer, decimal -> number
        $type = 'unknown';
        if (is_array($rules)) {
            foreach ($rules as $rule) {
                $type = $this->checkRule($rule);
                if ($type !== 'unknown') {
                    break;
                }
            }
        } else {
            $type = $this->checkRule($rules);
        }
        return $type;
    }

    private function checkRule(string $rule): string
    {
        $type = 'unknown';
        $rule = explode(':', $rule)[0];

        if (in_array($rule, ['string', 'date', 'email', 'date_format'])) {
            $type = 'string';
        } else if ($rule === 'boolean') {
            $type = 'boolean';
        } else if ($rule === 'array') {
            $type = 'unknown[]';
        } else if (in_array($rule, ['integer', 'decimal', 'numeric'])) {
            $type = 'number';
        }
        return $type;
    }

    public function fieldIsOptional(array|string $rules): bool
    {
        // nullable -> optional
        // Present -> allow null, disallow optional
        // sometimes -> ?
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
