<?php

namespace BetoCampoy\Champs\Filter\Core;

final class PaginationState
{
    public function __construct(
        private readonly int $page,
        private readonly int $perPage,
        private readonly int $total,
        private readonly string $queryString,
    ) {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getQueryString(): string
    {
        return $this->queryString;
    }
}
