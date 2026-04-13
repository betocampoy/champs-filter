<?php

namespace BetoCampoy\Champs\Filter\Core;

final class FilterRule
{
    public function __construct(
        public readonly string $field,
        public readonly string $operator,
        public readonly mixed $value,
        public readonly mixed $value2 = null,
        public readonly ?string $alias = null,
    ) {
    }

    public function hasValue(): bool
    {
        return !($this->value === null || $this->value === '');
    }

    public function isBetween(): bool
    {
        return in_array($this->operator, ['BT', 'BETWEEN'], true);
    }
}
