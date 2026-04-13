<?php

namespace BetoCampoy\Champs\Filter\Integration\Legacy;

use App\LegacySrc\Core\Model;
use BetoCampoy\Champs\Filter\Bridge\Legacy\LegacyApplier;
use BetoCampoy\Champs\Filter\Bridge\Legacy\LegacyCriteria;
use BetoCampoy\Champs\Filter\Bridge\Legacy\LegacyModelBridge;
use BetoCampoy\Champs\Filter\Core\FilterParser;
use BetoCampoy\Champs\Filter\Core\FilterPayload;
use BetoCampoy\Champs\Filter\Core\FormStateBuilder;
use BetoCampoy\Champs\Filter\Core\QueryStringBuilder;
use BetoCampoy\Champs\Filter\Result\FilterResult;

abstract class AbstractLegacyFilterService
{
    protected FilterParser $parser;
    protected LegacyApplier $applier;
    protected LegacyModelBridge $bridge;
    protected QueryStringBuilder $queryStringBuilder;
    protected FormStateBuilder $formStateBuilder;

    public function __construct()
    {
        $this->parser = new FilterParser();
        $this->applier = new LegacyApplier();
        $this->bridge = new LegacyModelBridge();
        $this->queryStringBuilder = new QueryStringBuilder();
        $this->formStateBuilder = new FormStateBuilder();
    }

    public function handle(array $context = [], array $options = []): FilterResult
    {
        $context = $this->mergeDefaultFilterValues($context);

        $payload = $this->parser->parse($context);
        $criteria = $this->applier->apply($payload, $this->getFieldMap());

        $model = $this->createBaseQuery();
        $model = $this->bridge->apply($model, $criteria);

        $this->afterApply($model, $payload, $criteria, $context, $options);

        $formState = $this->formStateBuilder->build($payload);
        $formState = $this->hydrateFormState($formState);

        $queryString = $this->queryStringBuilder->build($payload);

        $page = $this->resolvePage($context, $options);
        $perPage = $this->resolvePerPage($context, $options);
        $total = $this->resolveTotal($model, $context, $options);

        $this->applyDefaultOrder($model, $context, $options);

        if ($this->shouldPaginate($options)) {
            $this->applyPagination($model, $page, $perPage, $context, $options);
        }

        $items = $this->shouldFetchItems($options)
            ? ($model->fetch(true) ?? [])
            : [];

        return new FilterResult(
            target: $model,
            payload: $payload,
            criteria: $criteria,
            formState: $formState,
            queryString: $queryString,
            total: $total,
            page: $page,
            perPage: $perPage,
            items: $items,
        );
    }

    abstract protected function createBaseQuery(): Model;

    abstract protected function getFieldMap(): array;

    protected function hydrateFormState(array $formState): array
    {
        return $formState;
    }

    protected function afterApply(
        Model $model,
        FilterPayload $payload,
        LegacyCriteria $criteria,
        array $context,
        array $options
    ): void {
    }

    protected function getDefaultOrder(): ?array
    {
        return null;
    }

    protected function getDefaultFilterValues(): array
    {
        return [];
    }

    protected function mergeDefaultFilterValues(array $context): array
    {
        $defaults = $this->getDefaultFilterValues();

        if (!$defaults) {
            return $context;
        }

        foreach ($defaults as $key => $value) {
            if (
                !array_key_exists($key, $context) ||
                $context[$key] === null ||
                $context[$key] === ''
            ) {
                $context[$key] = $value;
            }
        }

        return $context;
    }

    protected function applyDefaultOrder(Model $model, array $context, array $options): void
    {
        $order = $this->getDefaultOrder();

        if (!$order) {
            return;
        }

        $model->order(
            $order['columns'],
            $order['sanitize'] ?? true
        );
    }

    protected function resolveTotal(Model $model, array $context, array $options): int
    {
        return (int) $model->count();
    }

    protected function resolvePage(array $context, array $options): int
    {
        $page = (int) ($options['page'] ?? $context['pagina'] ?? 1);

        return $page > 0 ? $page : 1;
    }

    protected function resolvePerPage(array $context, array $options): int
    {
        $perPage = (int) ($options['perPage'] ?? $this->getDefaultPerPage());

        return $perPage > 0 ? $perPage : $this->getDefaultPerPage();
    }

    protected function getDefaultPerPage(): int
    {
        return 20;
    }

    protected function shouldPaginate(array $options): bool
    {
        return $options['paginate'] ?? true;
    }

    protected function shouldFetchItems(array $options): bool
    {
        return $options['fetchItems'] ?? true;
    }

    protected function applyPagination(
        Model $model,
        int $page,
        int $perPage,
        array $context,
        array $options
    ): void {
        $offset = ($page - 1) * $perPage;

        $model
            ->limit($perPage)
            ->offset($offset);
    }
}