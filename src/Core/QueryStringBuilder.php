<?php

namespace BetoCampoy\Champs\Filter\Core;

final class QueryStringBuilder
{
    public function __construct(
        private readonly FormStateBuilder $formStateBuilder = new FormStateBuilder()
    ) {
    }

    public function build(
        FilterPayload $payload,
        string $prefix = 'champs_filter_'
    ): string {
        $state = $this->formStateBuilder->build($payload, $prefix);

        return http_build_query($state, '', '&', PHP_QUERY_RFC3986);
    }

    public function buildWithExtraParams(
        FilterPayload $payload,
        array $extraParams = [],
        string $prefix = 'champs_filter_'
    ): string {
        $state = $this->formStateBuilder->build($payload, $prefix);
        $data = array_merge($state, $extraParams);

        return http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    }

    public function buildFromState(array $state): string
    {
        return http_build_query($state, '', '&', PHP_QUERY_RFC3986);
    }
}
