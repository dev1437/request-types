<?php

namespace Dev1437\RequestTypes;

class TypeResolver implements TypesResolveInterface {

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
            if (in_array(['string', 'date', 'email'], $rules)) {
                $type = 'string';
            } else if (in_array('boolean', $rules)) {
                $type = 'boolean';
            } else if (in_array('array', $rules)) {
                $type = 'unknown[]';
            } else if (in_array(['integer', 'decimal'], $rules)) {
                $type = 'number';
            }
        } else {
            if (in_array($rules, ['string', 'date', 'email'])) {
                $type = 'string';
            } else if ($rules === 'boolean') {
                $type = 'boolean';
            } else if ($rules === 'array') {
                $type = 'unknown[]';
            } else if (in_array($rules, ['integer', 'decimal'])) {
                $type = 'number';
            }
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