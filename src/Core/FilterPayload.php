<?php

namespace BetoCampoy\Champs\Filter\Core;

final class FilterPayload
{
    /**
     * @param FilterRule[] $rules
     * @param FilterScope[] $scopes
     */
    public function __construct(
        public readonly array $rules = [],
        public readonly array $scopes = [],
        public readonly array $raw = [],
    ) {
    }

    public function hasRules(): bool
    {
        return $this->rules !== [];
    }

    public function hasScopes(): bool
    {
        return $this->scopes !== [];
    }
}
