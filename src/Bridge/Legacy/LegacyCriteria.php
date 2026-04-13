<?php

namespace BetoCampoy\Champs\Filter\Bridge\Legacy;

final class LegacyCriteria
{
    public array $where = [];
    public array $params = [];
    public array $whereIn = [];
    public array $joins = [];
    public array $orders = [];
    public array $scopes = [];

    public function addWhere(
        string $terms,
        array|string|null $params = null,
        string $operator = 'AND'
    ): void {
        $this->where[] = [
            'terms' => $terms,
            'params' => $params,
            'operator' => strtoupper($operator),
        ];
    }

    public function addParam(string $name, mixed $value): void
    {
        $this->params[$name] = $value;
    }

    public function addWhereIn(string $field, array $values, string $operator = 'AND'): void
    {
        $this->whereIn[] = [
            'field' => $field,
            'values' => $values,
            'operator' => strtoupper($operator),
        ];
    }

    public function addJoin(
        string $model,
        string $terms,
        array|string|null $params = null,
        string $type = 'LEFT',
        string $alias = 'j'
    ): void {
        $this->joins[] = [
            'model' => $model,
            'terms' => $terms,
            'params' => $params,
            'type' => strtoupper($type),
            'alias' => $alias,
        ];
    }

    public function addOrder(string $columns, bool $sanitize = true): void
    {
        $this->orders[] = [
            'columns' => $columns,
            'sanitize' => $sanitize,
        ];
    }

    public function addScope(string $method, mixed $value = true): void
    {
        $this->scopes[] = [
            'method' => $method,
            'value' => $value,
        ];
    }

    public function hasWhere(): bool
    {
        return $this->where !== [];
    }

    public function hasParams(): bool
    {
        return $this->params !== [];
    }

    public function hasWhereIn(): bool
    {
        return $this->whereIn !== [];
    }

    public function hasJoins(): bool
    {
        return $this->joins !== [];
    }

    public function hasOrders(): bool
    {
        return $this->orders !== [];
    }

    public function hasScopes(): bool
    {
        return $this->scopes !== [];
    }
}
