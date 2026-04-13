<?php

namespace BetoCampoy\Champs\Filter\Core;

final class FilterScope
{
    public function __construct(
        public readonly string $name,
        public readonly mixed $value = true,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->value === null || $this->value === '';
    }
}
