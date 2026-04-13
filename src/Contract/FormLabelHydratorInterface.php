<?php

namespace BetoCampoy\Champs\Filter\Contract;

interface FormLabelHydratorInterface
{
    public function hydrate(array $formState, array $config = []): array;
}
