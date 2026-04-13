# BetoCampoy Champs Filter

Componente reutilizável para construção, parsing e aplicação de filtros em aplicações PHP, com suporte a núcleo desacoplado e bridges para Doctrine e Legacy.

## Pacote

- **Composer:** `betocampoy/champs-filter`
- **Namespace base:** `BetoCampoy\Champs\Filter`

## Objetivo

O `betocampoy/champs-filter` foi criado para padronizar a forma como filtros são:

- interpretados
- estruturados
- aplicados
- reconstruídos para a interface
- serializados em query string

A proposta é permitir reutilização entre múltiplos projetos, mantendo o núcleo desacoplado e concentrando integrações específicas em bridges próprias.

## Estrutura

```text
betocampoy/
  champs/
    filter/
      composer.json
      README.md
      src/
        Core/                # Núcleo independente (Parser, Payload, Rule, Scope, builders)
        Contract/            # Interfaces e contratos auxiliares
        Bridge/
          Doctrine/          # Aplicação em QueryBuilder (Doctrine)
          Legacy/            # Aplicação em Model legado
        Integration/
          Symfony/           # Serviços base para uso com Symfony
          Legacy/            # Serviços base para uso no sistema legado
        Result/              # Objetos de retorno padronizados
```

## Instalação

### Via path repository

No `composer.json` do projeto consumidor:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../betocampoy/champs/filter"
    }
  ]
}
```

Depois:

```bash
composer require betocampoy/champs-filter:*
```

## Autoload

```json
"autoload": {
  "psr-4": {
    "BetoCampoy\\Champs\\Filter\\": "src/"
  }
}
```

## Exemplo de namespace

```php
namespace BetoCampoy\Champs\Filter\Core;
```

## Uso básico com Symfony

```php
use BetoCampoy\Champs\Filter\Integration\Symfony\AbstractSymfonyFilterService;

final class UserFilterService extends AbstractSymfonyFilterService
{
    protected function getFieldMap(): array
    {
        return [
            'name' => 'u.name',
            'email' => 'u.email',
        ];
    }
}
```

## Observações

- A bridge Legacy continua dependendo de `App\LegacySrc\Core\Model`
- O Core não depende de Symfony ou Doctrine
- Este pacote já está pronto para teste inicial, mas pode evoluir com mais contratos, testes e padronização de operadores

## Próximos passos sugeridos

- adicionar testes automatizados
- padronizar operadores e aliases
- criar configuração Symfony mais plugável
- reduzir ainda mais o acoplamento da bridge Legacy

## Autor

Beto Campoy
