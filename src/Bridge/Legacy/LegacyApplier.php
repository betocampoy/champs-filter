<?php

namespace BetoCampoy\Champs\Filter\Bridge\Legacy;

use BetoCampoy\Champs\Filter\Core\FilterPayload;
use BetoCampoy\Champs\Filter\Core\FilterRule;
use BetoCampoy\Champs\Filter\Core\FilterScope;
use InvalidArgumentException;

final class LegacyApplier
{
    public function apply(
        FilterPayload $payload,
        array $fieldMap = []
    ): LegacyCriteria {
        $criteria = new LegacyCriteria();
        $paramIndex = 0;

        foreach ($payload->rules as $rule) {
            $mapping = $this->resolveFieldMapping($rule->field, $rule->alias, $fieldMap);
            $sqlField = $mapping['alias'] . '.' . $mapping['field'];

            if (!empty($mapping['join'])) {
                $join = $mapping['join'];

                $criteria->addJoin(
                    $join['model'],
                    $join['terms'],
                    $join['params'] ?? null,
                    $join['type'] ?? 'LEFT',
                    $join['alias'] ?? 'j'
                );
            }

            $this->applyRule($criteria, $rule, $sqlField, $paramIndex);
        }

        foreach ($payload->scopes as $scope) {
            $this->applyScope($criteria, $scope);
        }

        return $criteria;
    }

    private function applyRule(
        LegacyCriteria $criteria,
        FilterRule $rule,
        string $sqlField,
        int &$paramIndex
    ): void {
        $operator = strtoupper($rule->operator);

        switch ($operator) {
            case 'EQ':
                $param = $this->nextParam($paramIndex, $rule->field);
                $criteria->addWhere(
                    sprintf('%s = :%s', $sqlField, $param),
                    [$param => $rule->value]
                );
                return;

            case 'NE':
                $param = $this->nextParam($paramIndex, $rule->field);
                $criteria->addWhere(
                    sprintf('%s <> :%s', $sqlField, $param),
                    [$param => $rule->value]
                );
                return;

            case 'GT':
                $param = $this->nextParam($paramIndex, $rule->field);
                $criteria->addWhere(
                    sprintf('%s > :%s', $sqlField, $param),
                    [$param => $rule->value]
                );
                return;

            case 'LT':
                $param = $this->nextParam($paramIndex, $rule->field);
                $criteria->addWhere(
                    sprintf('%s < :%s', $sqlField, $param),
                    [$param => $rule->value]
                );
                return;

            case 'GTE':
                $param = $this->nextParam($paramIndex, $rule->field);
                $criteria->addWhere(
                    sprintf('%s >= :%s', $sqlField, $param),
                    [$param => $rule->value]
                );
                return;

            case 'LTE':
                $param = $this->nextParam($paramIndex, $rule->field);
                $criteria->addWhere(
                    sprintf('%s <= :%s', $sqlField, $param),
                    [$param => $rule->value]
                );
                return;

            case 'LK':
            case 'LIKE':
                $param = $this->nextParam($paramIndex, $rule->field);
                $criteria->addWhere(
                    sprintf('%s LIKE :%s', $sqlField, $param),
                    [$param => '%' . trim((string) $rule->value) . '%']
                );
                return;

            case 'IN':
                $values = $this->normalizeInValues($rule->value);

                if ($values === []) {
                    return;
                }

                $criteria->addWhereIn($sqlField, $values);
                return;

            case 'BT':
            case 'BETWEEN':
                if ($rule->value === null || $rule->value === '' || $rule->value2 === null || $rule->value2 === '') {
                    return;
                }

                $paramA = $this->nextParam($paramIndex, $rule->field . '_from');
                $paramB = $this->nextParam($paramIndex, $rule->field . '_to');

                $criteria->addWhere(
                    sprintf('%s BETWEEN :%s AND :%s', $sqlField, $paramA, $paramB),
                    [
                        $paramA => $rule->value,
                        $paramB => $rule->value2,
                    ]
                );
                return;

            case 'ISNULL':
            case 'NULL':
                $criteria->addWhere(sprintf('%s IS NULL', $sqlField));
                return;

            case 'NOTNULL':
            case 'NOT_NULL':
                $criteria->addWhere(sprintf('%s IS NOT NULL', $sqlField));
                return;

            default:
                throw new InvalidArgumentException(sprintf(
                    'Operador não suportado no LegacyApplier: %s',
                    $operator
                ));
        }
    }

    private function applyScope(
        LegacyCriteria $criteria,
        FilterScope $scope
    ): void {
        $criteria->addScope(
            $this->resolveScopeMethodName($scope->name),
            $scope->value
        );
    }

    private function resolveFieldMapping(string $field, ?string $fallbackAlias, array $fieldMap): array
    {
        if (isset($fieldMap[$field])) {
            $mapping = $fieldMap[$field];

            return [
                'alias' => $mapping['alias'] ?? 'm',
                'field' => $mapping['field'] ?? $field,
                'join' => $mapping['join'] ?? null,
            ];
        }

        return [
            'alias' => $fallbackAlias ?: 'm',
            'field' => $field,
            'join' => null,
        ];
    }

    private function resolveScopeMethodName(string $scopeName): string
    {
        if (str_contains($scopeName, '_')) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $scopeName))));
        }

        return $scopeName;
    }

    private function normalizeInValues(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(
                $value,
                static fn ($item) => $item !== null && $item !== ''
            ));
        }

        $string = trim((string) $value);

        if ($string === '') {
            return [];
        }

        $parts = preg_split('/[\s,;]+/', $string) ?: [];

        return array_values(array_filter(
            $parts,
            static fn ($item) => $item !== ''
        ));
    }

    private function nextParam(int &$paramIndex, string $field): string
    {
        $paramIndex++;

        $safeField = preg_replace('/[^a-zA-Z0-9_]/', '_', $field) ?: 'field';

        return sprintf('cf_%s_%d', $safeField, $paramIndex);
    }
}
