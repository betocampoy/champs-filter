<?php

namespace BetoCampoy\Champs\Filter\Integration\Symfony;

use BetoCampoy\Champs\Filter\Bridge\Doctrine\DoctrineApplier;
use BetoCampoy\Champs\Filter\Core\FilterParser;
use BetoCampoy\Champs\Filter\Core\FilterPayload;
use BetoCampoy\Champs\Filter\Core\FormStateBuilder;
use BetoCampoy\Champs\Filter\Core\QueryStringBuilder;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSymfonyFilterService
{
    protected FilterParser $parser;
    protected DoctrineApplier $applier;
    protected FormStateBuilder $formStateBuilder;
    protected QueryStringBuilder $queryStringBuilder;

    public function __construct(
        FilterParser $parser,
        DoctrineApplier $applier,
        FormStateBuilder $formStateBuilder,
        QueryStringBuilder $queryStringBuilder,
    ) {
        $this->parser = $parser;
        $this->applier = $applier;
        $this->formStateBuilder = $formStateBuilder;
        $this->queryStringBuilder = $queryStringBuilder;
    }

    public function handle(Request $request, array $options = []): array
    {
        $input = $this->extractInput($request);

        return $this->handleInput($input, $request, $options);
    }

    public function filterById(int|string $id, array $options = []): ?array
    {
        $filterFieldName = "champs_filter_field_{$this->getIdFieldName()}";

        $result = $this->handleInput(
            [$filterFieldName => $id],
            null,
            $options
        );

        /** @var QueryBuilder $qb */
        $qb = $result['qb'];
        $qb->setMaxResults(1);

        $items = $qb->getQuery()->getArrayResult();

        return $items[0] ?? null;
    }

    /**
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    protected function handleInput(array $input, ?Request $request = null, array $options = []): array
    {
        $payload = $this->parser->parse($input);

        $qb = $this->createBaseQueryBuilder();

        $this->applier->apply(
            $qb,
            $payload,
            $this->getFieldMap(),
            $this->getScopeHandler()
        );

        $this->afterApply($qb, $payload, $request, $options);

        $formState = $this->formStateBuilder->build($payload);
        $formState = $this->hydrateFormState($formState);

        $queryString = $this->queryStringBuilder->build($payload);

        if ($request) {
            $this->applyDefaultOrder($qb, $request, $options);
        } else {
            $this->applyDefaultOrderWithoutRequest($qb, $options);
        }

        return [
            'qb' => $qb,
            'payload' => $payload,
            'formState' => $formState,
            'queryString' => $queryString,
        ];
    }

    abstract protected function createBaseQueryBuilder(): QueryBuilder;

    abstract protected function getFieldMap(): array;

    abstract protected function getIdFieldName(): string;

    protected function getScopeHandler(): ?object
    {
        return null;
    }

    protected function hydrateFormState(array $formState): array
    {
        return $formState;
    }

    protected function afterApply(
        QueryBuilder $qb,
        FilterPayload $payload,
        ?Request $request,
        array $options
    ): void {
    }

    protected function getDefaultOrder(): ?array
    {
        return null;
    }

    protected function applyDefaultOrder(QueryBuilder $qb, Request $request, array $options): void
    {
        $order = $this->getDefaultOrder();

        if (!$order) {
            return;
        }

        $qb->addOrderBy(
            $order['field'],
            $order['direction'] ?? 'ASC'
        );
    }

    protected function applyDefaultOrderWithoutRequest(QueryBuilder $qb, array $options): void
    {
        $order = $this->getDefaultOrder();

        if (!$order) {
            return;
        }

        $qb->addOrderBy(
            $order['field'],
            $order['direction'] ?? 'ASC'
        );
    }

    protected function extractInput(Request $request): array
    {
        return array_merge(
            $request->query->all(),
            $request->request->all()
        );
    }
}