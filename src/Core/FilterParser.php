<?php

namespace BetoCampoy\Champs\Filter\Core;

final class FilterParser
{
    public function parse(array $input): FilterPayload
    {
        $rules = $this->parseRules($input);
        $scopes = $this->parseScopes($input);

        return new FilterPayload(
            rules: $rules,
            scopes: $scopes,
            raw: $input
        );
    }

    private function parseRules(array $input): array
    {
        $rules = [];

        foreach ($input as $key => $value) {

            if (!str_starts_with($key, 'champs_filter_field_')) {
                continue;
            }

            $field = substr($key, strlen('champs_filter_field_'));

            if ($value === null || $value === '') {
                continue;
            }

            $operator = strtoupper(
                $input["champs_filter_opr_{$field}"] ?? 'EQ'
            );

            $value2 = $input["champs_filter_2field_{$field}"] ?? null;

            $rules[] = new FilterRule(
                field: $field,
                operator: $operator,
                value: $value,
                value2: $value2
            );
        }

        return $rules;
    }

    private function parseScopes(array $data): array
    {
        $scopes = [];

        foreach ($data as $key => $value) {

            /**
             * -------------------------------------------------
             * 1. Scope simples: champs_filter_scope[]
             * -------------------------------------------------
             */
            if ($key === 'champs_filter_scope') {

                $values = is_array($value) ? $value : [$value];

                foreach ($values as $scopeName) {
                    if (!$scopeName) {
                        continue;
                    }

                    $scopes[] = new FilterScope(
                        $this->normalizeScopeName($scopeName),
                        true
                    );
                }

                continue;
            }

            /**
             * -------------------------------------------------
             * 2. Scope com parâmetro: champs_filter_scope_*
             * -------------------------------------------------
             */
            if (str_starts_with($key, 'champs_filter_scope_')) {

                $rawName = substr($key, strlen('champs_filter_scope_'));

                if (!$rawName) {
                    continue;
                }

                if ($value === null || $value === '') {
                    continue;
                }

                $scopes[] = new FilterScope(
                    $this->normalizeScopeName($rawName),
                    $value
                );
            }
        }

        return $scopes;
    }

    private function normalizeScopeName(string $name): string
    {
        if (str_contains($name, '_')) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
        }

        return $name;
    }
}
