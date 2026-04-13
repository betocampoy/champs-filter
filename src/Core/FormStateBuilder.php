<?php

namespace BetoCampoy\Champs\Filter\Core;

final class FormStateBuilder
{
    public function build(
        FilterPayload $payload,
        string $prefix = 'champs_filter_'
    ): array {
        $state = [];

        foreach ($payload->rules as $rule) {
            $state[$prefix . 'field_' . $rule->field] = $rule->value;
            $state[$prefix . 'opr_' . $rule->field] = $rule->operator;

            if ($rule->value2 !== null && $rule->value2 !== '') {
                $state[$prefix . '2field_' . $rule->field] = $rule->value2;
            }

            if ($rule->alias !== null && $rule->alias !== '') {
                $state[$prefix . 'alias_' . $rule->field] = $rule->alias;
            }
        }

        $simpleScopes = [];

        foreach ($payload->scopes as $scope) {
            if ($scope->value === true) {
                $simpleScopes[] = $scope->name;
                continue;
            }

            $state[$prefix . 'scope_' . $this->camelToSnake($scope->name)] = $scope->value;
        }

        if ($simpleScopes !== []) {
            $state[$prefix . 'scope'] = $simpleScopes;
        }

        return $state;
    }

    private function camelToSnake(string $value): string
    {
        $snake = preg_replace('/(?<!^)[A-Z]/', '_$0', $value);

        return strtolower($snake ?? $value);
    }
}
