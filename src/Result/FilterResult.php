<?php

namespace BetoCampoy\Champs\Filter\Result;

use BetoCampoy\Champs\Filter\Core\FilterPayload;

final class FilterResult
{
    public function __construct(
        private readonly mixed $target,
        private readonly FilterPayload $payload,
        private readonly mixed $criteria,
        private readonly array $formState,
        private readonly string $queryString,
        private readonly int $total = 0,
        private readonly int $page = 1,
        private readonly int $perPage = 20,
        private readonly array $items = [],
    ) {
    }

    public function getTarget(): mixed
    {
        return $this->target;
    }

    public function getPayload(): FilterPayload
    {
        return $this->payload;
    }

    public function getCriteria(): mixed
    {
        return $this->criteria;
    }

    public function getFormState(): array
    {
        return $this->formState;
    }

    public function getQueryString(): string
    {
        return $this->queryString;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
