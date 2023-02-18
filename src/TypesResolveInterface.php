<?php

namespace Dev1437\RequestTypes;

interface TypesResolveInterface
{
    public function fieldIsOptional(array|string $rules): bool;
    public function rulesToType(array|string $rules): string;
}