<?php

namespace BetoCampoy\Champs\Filter\Bridge\Legacy;

use App\LegacySrc\Core\Model;

final class LegacyModelBridge
{
    public function apply(Model $model, LegacyCriteria $criteria): Model
    {
        $this->applyJoins($model, $criteria);
        $this->applyWheres($model, $criteria);
        $this->applyWhereIns($model, $criteria);
        $this->applyOrders($model, $criteria);
        $this->applyScopes($model, $criteria);

        return $model;
    }

    private function applyJoins(Model $model, LegacyCriteria $criteria): void
    {
        foreach ($criteria->joins as $join) {
            $model->join(
                $join['model'],
                $join['terms'],
                $join['params'] ?? null,
                $join['type'] ?? 'LEFT',
                $join['alias'] ?? 'j',
            );
        }
    }

    private function applyWheres(Model $model, LegacyCriteria $criteria): void
    {
        foreach ($criteria->where as $where) {
            $params = $where['params'] ?? null;

            if ($params === null) {
                $params = $this->extractClauseParams(
                    $where['terms'],
                    $criteria->params
                );
            }

            $model->where(
                $where['terms'],
                $params,
                $where['operator'] ?? 'AND',
            );
        }
    }

    private function applyWhereIns(Model $model, LegacyCriteria $criteria): void
    {
        foreach ($criteria->whereIn as $whereIn) {
            $model->whereIn(
                $whereIn['field'],
                $whereIn['values'] ?? [],
                $whereIn['operator'] ?? 'AND',
            );
        }
    }

    private function applyOrders(Model $model, LegacyCriteria $criteria): void
    {
        foreach ($criteria->orders as $order) {
            $model->order(
                $order['columns'],
                $order['sanitize'] ?? true,
            );
        }
    }

    private function applyScopes(Model $model, LegacyCriteria $criteria): void
    {
        foreach ($criteria->scopes as $scope) {
            $method = $scope['method'] ?? null;

            if (!$method || !method_exists($model, $method)) {
                continue;
            }

            $value = $scope['value'] ?? true;

            if ($value === true) {
                $model->{$method}();
                continue;
            }

            $model->{$method}($value);
        }
    }

    private function extractClauseParams(string $terms, array $allParams): array
    {
        preg_match_all('/\:([a-zA-Z0-9_]+)/', $terms, $matches);

        $params = [];

        foreach ($matches[1] ?? [] as $paramName) {
            if (array_key_exists($paramName, $allParams)) {
                $params[$paramName] = $allParams[$paramName];
            }
        }

        return $params;
    }
}
