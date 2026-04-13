<?php

namespace BetoCampoy\Champs\Filter\Bridge\Doctrine;

use BetoCampoy\Champs\Filter\Core\FilterPayload;
use BetoCampoy\Champs\Filter\Core\FilterRule;
use BetoCampoy\Champs\Filter\Core\FilterScope;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

final class DoctrineApplier
{
    public function apply(
        QueryBuilder $qb,
        FilterPayload $payload,
        array $fieldMap = [],
        object $scopeHandler = null,
    ): QueryBuilder {
        $paramIndex = 0;

        foreach ($payload->rules as $rule) {
            if (!$rule->hasValue() && !$this->isNullOperator($rule->operator)) {
                continue;
            }

            $mapping = $this->resolveFieldMapping($rule->field, $rule->alias, $fieldMap);
            $dqlField = $mapping['alias'] . '.' . $mapping['field'];

            $this->applyRule($qb, $rule, $dqlField, $paramIndex);
        }

        foreach ($payload->scopes as $scope) {
            $this->applyScope($qb, $scope, $scopeHandler);
        }

        return $qb;
    }

    private function applyRule(
        QueryBuilder $qb,
        FilterRule $rule,
        string $dqlField,
        int &$paramIndex
    ): void {
        $operator = strtoupper($rule->operator);

        switch ($operator) {
            case 'EQ':
                $param = $this->nextParam($paramIndex, $rule->field);
                $qb->andWhere(sprintf('%s = :%s', $dqlField, $param))
                    ->setParameter($param, $rule->value);
                return;

            case 'NE':
                $param = $this->nextParam($paramIndex, $rule->field);
                $qb->andWhere(sprintf('%s <> :%s', $dqlField, $param))
                    ->setParameter($param, $rule->value);
                return;

            case 'GT':
                $param = $this->nextParam($paramIndex, $rule->field);
                $qb->andWhere(sprintf('%s > :%s', $dqlField, $param))
                    ->setParameter($param, $rule->value);
                return;

            case 'LT':
                $param = $this->nextParam($paramIndex, $rule->field);
                $qb->andWhere(sprintf('%s < :%s', $dqlField, $param))
                    ->setParameter($param, $rule->value);
                return;

            case 'GTE':
                $param = $this->nextParam($paramIndex, $rule->field);
                $qb->andWhere(sprintf('%s >= :%s', $dqlField, $param))
                    ->setParameter($param, $rule->value);
                return;

            case 'LTE':
                $param = $this->nextParam($paramIndex, $rule->field);
                $qb->andWhere(sprintf('%s <= :%s', $dqlField, $param))
                    ->setParameter($param, $rule->value);
                return;

            case 'LK':
            case 'LIKE':
                $param = $this->nextParam($paramIndex, $rule->field);
                $qb->andWhere(sprintf('%s LIKE :%s', $dqlField, $param))
                    ->setParameter($param, '%' . trim((string) $rule->value) . '%');
                return;

            case 'IN':
                $values = $this->normalizeInValues($rule->value);
                if ($values === []) {
                    return;
                }

                $param = $this->nextParam($paramIndex, $rule->field);
                $qb->andWhere(sprintf('%s IN (:%s)', $dqlField, $param))
                    ->setParameter($param, $values);
                return;

            case 'BT':
            case 'BETWEEN':
                if ($rule->value === null || $rule->value === '' || $rule->value2 === null || $rule->value2 === '') {
                    return;
                }

                $paramA = $this->nextParam($paramIndex, $rule->field . '_from');
                $paramB = $this->nextParam($paramIndex, $rule->field . '_to');

                $qb->andWhere(sprintf('%s BETWEEN :%s AND :%s', $dqlField, $paramA, $paramB))
                    ->setParameter($paramA, $rule->value)
                    ->setParameter($paramB, $rule->value2);
                return;

            case 'ISNULL':
            case 'NULL':
                $qb->andWhere(sprintf('%s IS NULL', $dqlField));
                return;

            case 'NOTNULL':
            case 'NOT_NULL':
                $qb->andWhere(sprintf('%s IS NOT NULL', $dqlField));
                return;

            default:
                throw new InvalidArgumentException(sprintf('Operador não suportado no DoctrineApplier: %s', $operator));
        }
    }

    private function applyScope(QueryBuilder $qb, FilterScope $scope, ?object $scopeHandler): void
    {
        if (!$scopeHandler) {
            return;
        }

        $method = 'scope' . $this->studly($scope->name);

        if (!method_exists($scopeHandler, $method)) {
            return;
        }

        if ($scope->value === true) {
            $scopeHandler->{$method}($qb);
            return;
        }

        $scopeHandler->{$method}($qb, $scope->value);
    }

    private function resolveFieldMapping(string $field, ?string $fallbackAlias, array $fieldMap): array
    {
        if (isset($fieldMap[$field])) {
            $mapping = $fieldMap[$field];

            return [
                'alias' => $mapping['alias'],
                'field' => $mapping['field'],
            ];
        }

        if ($fallbackAlias) {
            return [
                'alias' => $fallbackAlias,
                'field' => $field,
            ];
        }

        return [
            'alias' => 'm',
            'field' => $field,
        ];
    }

    private function normalizeInValues(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, static fn ($item) => $item !== null && $item !== ''));
        }

        $string = trim((string) $value);
        if ($string === '') {
            return [];
        }

        $parts = preg_split('/[\s,;]+/', $string) ?: [];

        return array_values(array_filter($parts, static fn ($item) => $item !== ''));
    }

    private function nextParam(int &$paramIndex, string $field): string
    {
        $paramIndex++;

        $safeField = preg_replace('/[^a-zA-Z0-9_]/', '_', $field) ?: 'field';

        return sprintf('cf_%s_%d', $safeField, $paramIndex);
    }

    private function isNullOperator(string $operator): bool
    {
        return in_array(strtoupper($operator), ['ISNULL', 'NULL', 'NOTNULL', 'NOT_NULL'], true);
    }

    private function studly(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', strtolower($value));

        return str_replace(' ', '', ucwords($value));
    }
}
